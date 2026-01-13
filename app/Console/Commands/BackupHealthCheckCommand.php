<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputOption;

class BackupHealthCheckCommand extends Command
{
    protected ?string $signature = 'backup:health {--alert-on-fail}';
    protected string $description = 'Check backup system health and send alerts on failures';

    protected ConfigInterface $config;

    protected LoggerInterface $logger;

    public function __construct(ConfigInterface $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
        parent::__construct();
    }

    public function handle()
    {
        try {
            $this->output->writeln('<info>Starting backup health check...</info>');

            $healthStatus = $this->performHealthCheck();

            $this->displayHealthStatus($healthStatus);

            if (!$healthStatus['healthy']) {
                $alertOnFail = $this->input->getOption('alert-on-fail');

                if ($alertOnFail) {
                    $this->sendFailureAlert($healthStatus);
                }

                return 1;
            }

            $this->output->writeln('<info>Backup system is healthy</info>');
            $this->logger->info('Backup health check passed', $healthStatus);

            return 0;
        } catch (Exception $e) {
            $this->logger->error('Backup health check failed: ' . $e->getMessage());
            $this->output->writeln('<error>Backup health check failed: ' . $e->getMessage() . '</error>');

            $this->sendFailureAlert([
                'error' => $e->getMessage(),
                'healthy' => false,
            ]);

            return 1;
        }
    }

    protected function performHealthCheck(): array
    {
        $status = [
            'healthy' => true,
            'timestamp' => date('c'),
            'checks' => [],
            'errors' => [],
        ];

        $backupConfig = $this->config->get('backup', []);

        $status['checks'][] = $this->checkBackupDirectories($backupConfig);
        $status['checks'][] = $this->checkDiskSpace($backupConfig);
        $status['checks'][] = $this->checkRecentBackups($backupConfig);
        $status['checks'][] = $this->checkEncryptionKey($backupConfig);

        foreach ($status['checks'] as $check) {
            if (!$check['passed']) {
                $status['healthy'] = false;
                $status['errors'][] = $check['error'];
            }
        }

        return $status;
    }

    protected function checkBackupDirectories(array $backupConfig): array
    {
        $directories = $backupConfig['directories'] ?? [];
        $missingDirs = [];

        foreach ($directories as $name => $path) {
            if (!is_dir($path)) {
                $missingDirs[] = ['name' => $name, 'path' => $path];
            } elseif (!is_writable($path)) {
                $missingDirs[] = ['name' => $name, 'path' => $path, 'error' => 'Not writable'];
            }
        }

        if (empty($missingDirs)) {
            return [
                'check' => 'backup_directories',
                'passed' => true,
                'message' => 'All backup directories exist and are writable',
            ];
        }

        $errorMessages = [];
        foreach ($missingDirs as $dir) {
            $errorMessages[] = sprintf(
                'Directory %s (%s) %s',
                $dir['name'],
                $dir['path'],
                $dir['error'] ?? 'missing'
            );
        }

        return [
            'check' => 'backup_directories',
            'passed' => false,
            'error' => implode(', ', $errorMessages),
        ];
    }

    protected function checkDiskSpace(array $backupConfig): array
    {
        $backupPath = $backupConfig['directories']['comprehensive'] ?? storage_path('backups');

        if (!is_dir(dirname($backupPath))) {
            return [
                'check' => 'disk_space',
                'passed' => true,
                'message' => 'Cannot check disk space (backup directory parent not accessible)',
            ];
        }

        $diskFree = disk_free_space(dirname($backupPath));
        $diskTotal = disk_total_space(dirname($backupPath));
        $diskUsagePercent = ($diskTotal - $diskFree) / $diskTotal * 100;

        $requiredSpace = 5 * 1024 * 1024 * 1024;
        $hasEnoughSpace = $diskFree > $requiredSpace;

        $message = sprintf(
            'Disk space: %s free, %s total (%.1f%% used)',
            $this->formatBytes($diskFree),
            $this->formatBytes($diskTotal),
            $diskUsagePercent
        );

        return [
            'check' => 'disk_space',
            'passed' => $hasEnoughSpace,
            'message' => $message,
            'details' => [
                'free_bytes' => $diskFree,
                'total_bytes' => $diskTotal,
                'usage_percent' => round($diskUsagePercent, 2),
                'required_bytes' => $requiredSpace,
            ],
        ];
    }

    protected function checkRecentBackups(array $backupConfig): array
    {
        $backupPath = $backupConfig['directories']['comprehensive'] ?? storage_path('backups');

        if (!is_dir($backupPath)) {
            return [
                'check' => 'recent_backups',
                'passed' => false,
                'error' => 'Backup directory not accessible',
            ];
        }

        $files = glob($backupPath . '/full_backup_*.tar.gz');

        if (empty($files)) {
            return [
                'check' => 'recent_backups',
                'passed' => false,
                'error' => 'No backups found',
            ];
        }

        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $latestBackup = $files[0];
        $latestBackupAge = time() - filemtime($latestBackup);
        $maxAgeHours = 48;

        $hasRecentBackup = $latestBackupAge < ($maxAgeHours * 3600);

        $message = sprintf(
            'Latest backup is %d hours old (max: %d hours)',
            round($latestBackupAge / 3600),
            $maxAgeHours
        );

        return [
            'check' => 'recent_backups',
            'passed' => $hasRecentBackup,
            'message' => $message,
            'details' => [
                'latest_backup' => basename($latestBackup),
                'age_hours' => round($latestBackupAge / 3600),
                'max_age_hours' => $maxAgeHours,
            ],
        ];
    }

    protected function checkEncryptionKey(array $backupConfig): array
    {
        $encryptionEnabled = $backupConfig['options']['encrypt'] ?? false;
        $encryptionKey = $backupConfig['options']['password'] ?? null;

        if (!$encryptionEnabled) {
            return [
                'check' => 'encryption_key',
                'passed' => true,
                'message' => 'Encryption not enabled',
            ];
        }

        if (empty($encryptionKey)) {
            return [
                'check' => 'encryption_key',
                'passed' => false,
                'error' => 'Encryption enabled but no key configured',
            ];
        }

        if (strlen($encryptionKey) < 32) {
            return [
                'check' => 'encryption_key',
                'passed' => false,
                'error' => 'Encryption key too short (minimum 32 characters)',
            ];
        }

        return [
            'check' => 'encryption_key',
            'passed' => true,
            'message' => 'Encryption key configured and valid',
        ];
    }

    protected function displayHealthStatus(array $healthStatus): void
    {
        $this->output->writeln('');
        $this->output->writeln('Backup System Health Status');
        $this->output->writeln(str_repeat('=', 50));

        foreach ($healthStatus['checks'] as $check) {
            $icon = $check['passed'] ? '<info>✓</info>' : '<error>✗</error>';
            $label = $this->formatCheckName($check['check']);

            $this->output->writeln(sprintf('  %s %s', $icon, $label));

            if (isset($check['message'])) {
                $this->output->writeln(sprintf('    %s', $check['message']));
            }

            if (isset($check['details'])) {
                foreach ($check['details'] as $key => $value) {
                    $this->output->writeln(sprintf('      %s: %s', $key, $value));
                }
            }
        }

        $this->output->writeln(str_repeat('=', 50));

        if ($healthStatus['healthy']) {
            $this->output->writeln('<info>Overall Status: HEALTHY</info>');
        } else {
            $this->output->writeln('<error>Overall Status: UNHEALTHY</error>');
            if (!empty($healthStatus['errors'])) {
                $this->output->writeln('<error>Errors: ' . implode('; ', $healthStatus['errors']) . '</error>');
            }
        }
    }

    protected function formatCheckName(string $check): string
    {
        return ucwords(str_replace('_', ' ', $check));
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    protected function sendFailureAlert(array $healthStatus): void
    {
        $alertConfig = $this->config->get('backup.alerts', []);

        if (empty($alertConfig['email']) && empty($alertConfig['webhook'])) {
            return;
        }

        $this->logger->error('Backup health check failed, sending alert', $healthStatus);

        if (!empty($alertConfig['email'])) {
            $this->sendEmailAlert($alertConfig['email'], $healthStatus);
        }

        if (!empty($alertConfig['webhook'])) {
            $this->sendWebhookAlert($alertConfig['webhook'], $healthStatus);
        }

        if (!empty($alertConfig['slack_webhook_url'])) {
            $this->sendSlackAlert($alertConfig['slack_webhook_url'], $healthStatus);
        }
    }

    protected function sendEmailAlert(string $email, array $healthStatus): void
    {
        $subject = '[Backup System Alert] Health Check Failed';

        $body = "Backup System Health Check Failed\n\n";
        $body .= "Timestamp: {$healthStatus['timestamp']}\n\n";
        $body .= "Failed Checks:\n";

        foreach ($healthStatus['checks'] as $check) {
            if (!$check['passed']) {
                $body .= sprintf("- %s: %s\n", $check['check'], $check['error'] ?? 'Failed');
            }
        }

        $body .= "\nPlease investigate immediately.\n";

        mail($email, $subject, $body);

        $this->logger->info('Email alert sent', ['email' => $email]);
    }

    protected function sendWebhookAlert(string $webhookUrl, array $healthStatus): void
    {
        $payload = [
            'event' => 'backup_health_check',
            'status' => 'failed',
            'timestamp' => $healthStatus['timestamp'],
            'checks' => $healthStatus['checks'],
            'errors' => $healthStatus['errors'],
        ];

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $this->logger->info('Webhook alert sent successfully', ['url' => $webhookUrl]);
        } else {
            $this->logger->error('Webhook alert failed', [
                'url' => $webhookUrl,
                'http_code' => $httpCode,
                'response' => $response,
            ]);
        }
    }

    protected function sendSlackAlert(string $slackWebhookUrl, array $healthStatus): void
    {
        $payload = [
            'text' => ':rotating_light: *Backup System Health Check Failed*',
            'attachments' => [
                [
                    'color' => 'danger',
                    'fields' => [
                        [
                            'title' => 'Timestamp',
                            'value' => $healthStatus['timestamp'],
                            'short' => false,
                        ],
                        [
                            'title' => 'Failed Checks',
                            'value' => implode("\n", array_map(function ($check) {
                                if (!$check['passed']) {
                                    return sprintf('• %s: %s', $check['check'], $check['error'] ?? 'Failed');
                                }
                                return null;
                            }, $healthStatus['checks'])),
                            'short' => false,
                        ],
                    ],
                ],
            ],
        ];

        $ch = curl_init($slackWebhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $this->logger->info('Slack alert sent successfully', ['url' => $slackWebhookUrl]);
        } else {
            $this->logger->error('Slack alert failed', [
                'url' => $slackWebhookUrl,
                'http_code' => $httpCode,
                'response' => $response,
            ]);
        }
    }

    protected function configure(): void
    {
        $this->addOption('alert-on-fail', null, InputOption::VALUE_NONE, 'Send alert if health check fails');
    }
}
