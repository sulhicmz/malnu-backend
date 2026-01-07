<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hyperf\Contract\ConfigInterface;
use Hypervel\Console\Command;
use Hypervel\Support\Facades\Log;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

class BackupMonitorCommand extends Command
{
    protected ?string $signature = 'backup:monitor {--last-hours=} {--alert-email=} {--webhook-url=}';

    protected string $description = 'Monitor backup status and send alerts if needed';

    protected ContainerInterface $container;

    protected ConfigInterface $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
        parent::__construct();
    }

    public function handle()
    {
        $lastHours = (int) ($this->input->getOption('last-hours') ?: 24);
        $alertEmail = $this->input->getOption('alert-email') ?: $this->config->get('mail.from.address');
        $webhookUrl = $this->input->getOption('webhook-url');

        $this->output->writeln('<info>Starting backup monitoring...</info>');
        $this->output->writeln('<info>Checking backups from last ' . $lastHours . ' hours</info>');

        $results = $this->checkBackupStatus($lastHours);

        $this->output->writeln('<info>Backup Status Check Results:</info>');
        $this->output->writeln('  Database backups: ' . ($results['database_ok'] ? '<info>OK</info>' : '<error>FAILED</error>'));
        $this->output->writeln('  File system backups: ' . ($results['filesystem_ok'] ? '<info>OK</info>' : '<error>FAILED</error>'));
        $this->output->writeln('  Configuration backups: ' . ($results['config_ok'] ? '<info>OK</info>' : '<error>FAILED</error>'));
        $this->output->writeln('  Overall status: ' . ($results['overall_ok'] ? '<info>ALL OK</info>' : '<error>SOME FAILED</error>'));

        // Send alerts if needed
        if (! $results['overall_ok']) {
            $this->output->writeln('<comment>Sending alerts for failed backups...</comment>');
            $this->sendAlerts($results, $alertEmail, $webhookUrl);
        } else {
            $this->output->writeln('<info>All backups are up to date!</info>');
        }

        return $results['overall_ok'] ? 0 : 1;
    }

    protected function checkBackupStatus(int $lastHours): array
    {
        $backupPath = $this->getStoragePath('backups');

        $results = [
            'database_ok' => false,
            'filesystem_ok' => false,
            'config_ok' => false,
            'overall_ok' => false,
            'details' => [],
        ];

        // Calculate the time threshold
        $timeThreshold = time() - ($lastHours * 3600);

        // Check for recent database backups
        $dbBackups = $this->findRecentBackups($backupPath, 'db_backup_', $timeThreshold);
        $results['database_ok'] = ! empty($dbBackups);
        $results['details']['database'] = [
            'count' => count($dbBackups),
            'recent' => ! empty($dbBackups) ? date('Y-m-d H:i:s', max(array_keys($dbBackups))) : 'none',
        ];

        // Check for recent filesystem backups
        $fsBackups = $this->findRecentBackups($backupPath, 'filesystem_backup_', $timeThreshold);
        $results['filesystem_ok'] = ! empty($fsBackups);
        $results['details']['filesystem'] = [
            'count' => count($fsBackups),
            'recent' => ! empty($fsBackups) ? date('Y-m-d H:i:s', max(array_keys($fsBackups))) : 'none',
        ];

        // Check for recent configuration backups
        $configBackups = $this->findRecentBackups($backupPath, 'config_backup_', $timeThreshold);
        $results['config_ok'] = ! empty($configBackups);
        $results['details']['config'] = [
            'count' => count($configBackups),
            'recent' => ! empty($configBackups) ? date('Y-m-d H:i:s', max(array_keys($configBackups))) : 'none',
        ];

        // Check for recent comprehensive backups
        $comprehensiveBackups = $this->findRecentBackups($backupPath, 'full_backup_', $timeThreshold);
        if (! empty($comprehensiveBackups)) {
            // If comprehensive backup exists, consider individual components as OK
            $results['database_ok'] = true;
            $results['filesystem_ok'] = true;
            $results['config_ok'] = true;
        }

        $results['overall_ok'] = $results['database_ok'] && $results['filesystem_ok'] && $results['config_ok'];

        return $results;
    }

    protected function findRecentBackups(string $backupPath, string $pattern, int $timeThreshold): array
    {
        if (! is_dir($backupPath)) {
            return [];
        }

        $files = scandir($backupPath);
        $recentBackups = [];

        foreach ($files as $file) {
            if (strpos($file, $pattern) === 0) {
                $filePath = $backupPath . '/' . $file;
                $fileTime = filemtime($filePath);

                if ($fileTime >= $timeThreshold) {
                    $recentBackups[$fileTime] = $filePath;
                }
            }
        }

        return $recentBackups;
    }

    protected function sendAlerts(array $results, ?string $alertEmail, ?string $webhookUrl): void
    {
        $message = $this->generateAlertMessage($results);

        // Log the alert
        Log::error('Backup monitoring alert', [
            'results' => $results,
            'message' => $message,
        ]);

        // Send email alert if email is provided
        if ($alertEmail) {
            $this->sendEmailAlert($alertEmail, $message);
        }

        // Send webhook alert if URL is provided
        if ($webhookUrl) {
            $this->sendWebhookAlert($webhookUrl, $results);
        }
    }

    protected function generateAlertMessage(array $results): string
    {
        $message = "Backup Monitoring Alert\n\n";
        $message .= 'Time: ' . date('Y-m-d H:i:s') . "\n";
        $message .= 'Server: ' . gethostname() . "\n\n";

        $message .= 'Database backups: ' . ($results['database_ok'] ? 'OK' : 'FAILED') . "\n";
        $message .= 'File system backups: ' . ($results['filesystem_ok'] ? 'OK' : 'FAILED') . "\n";
        $message .= 'Configuration backups: ' . ($results['config_ok'] ? 'OK' : 'FAILED') . "\n\n";

        $message .= "Details:\n";
        $message .= "  Database: {$results['details']['database']['count']} backups, latest: {$results['details']['database']['recent']}\n";
        $message .= "  File system: {$results['details']['filesystem']['count']} backups, latest: {$results['details']['filesystem']['recent']}\n";
        $message .= "  Config: {$results['details']['config']['count']} backups, latest: {$results['details']['config']['recent']}\n\n";

        $message .= "Please check the backup system immediately.\n";

        return $message;
    }

    protected function sendEmailAlert(string $email, string $message): void
    {
        $this->output->write('Sending email alert to ' . $email . '... ');

        // In a real implementation, this would send an actual email
        // For now, we'll just log it
        Log::info('Backup alert email would be sent', [
            'to' => $email,
            'message' => $message,
        ]);

        $this->output->writeln('<info>OK (logged)</info>');
    }

    protected function sendWebhookAlert(string $webhookUrl, array $results): void
    {
        $this->output->write('Sending webhook alert to ' . $webhookUrl . '... ');

        // Prepare webhook payload
        $payload = [
            'timestamp' => time(),
            'server' => gethostname(),
            'status' => $results['overall_ok'] ? 'warning' : 'critical',
            'message' => 'Backup monitoring detected failed backups',
            'results' => $results,
        ];

        // In a real implementation, this would make an HTTP request
        // For now, we'll just log it
        Log::info('Backup alert webhook would be sent', [
            'url' => $webhookUrl,
            'payload' => $payload,
        ]);

        $this->output->writeln('<info>OK (logged)</info>');
    }

    protected function getStoragePath(string $path = ''): string
    {
        $storagePath = base_path('storage');
        if ($path) {
            $storagePath .= '/' . ltrim($path, '/');
        }
        return $storagePath;
    }

    protected function configure(): void
    {
        $this->addOption('last-hours', null, InputOption::VALUE_OPTIONAL, 'Check backups from last N hours (default: 24)', '24');
        $this->addOption('alert-email', null, InputOption::VALUE_OPTIONAL, 'Email address for alerts');
        $this->addOption('webhook-url', null, InputOption::VALUE_OPTIONAL, 'Webhook URL for alerts');
    }
}
