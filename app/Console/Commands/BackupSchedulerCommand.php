<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputOption;

class BackupSchedulerCommand extends Command
{
    protected ?string $signature = 'backup:schedule {--type=} {--connection=} {--encrypt}';
    protected string $description = 'Schedule automated backup operations based on configuration';

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
            $type = $this->input->getOption('type') ?: 'scheduled';
            $connection = $this->input->getOption('connection');
            $encrypt = $this->input->getOption('encrypt');

            $this->logger->info('Starting scheduled backup', ['type' => $type]);

            $backupConfig = $this->config->get('backup.schedule', []);

            $time = date('H:i');

            $shouldRun = $this->shouldRunBackup($backupConfig, $time);

            if (!$shouldRun) {
                $this->output->writeln('<comment>Skipping scheduled backup (not scheduled for current time)</comment>');
                return 0;
            }

            $this->output->writeln('<info>Executing scheduled backup...</info>');

            if ($type === 'database' || $type === 'scheduled') {
                $this->runDatabaseBackup($connection, $encrypt);
            }

            if ($type === 'filesystem' || $type === 'scheduled') {
                $this->runFileSystemBackup($encrypt);
            }

            if ($type === 'config' || $type === 'scheduled') {
                $this->runConfigBackup($encrypt);
            }

            if ($type === 'comprehensive' || $type === 'scheduled') {
                $this->runComprehensiveBackup($connection, $encrypt);
            }

            $this->output->writeln('<info>Scheduled backup completed successfully</info>');
            $this->logger->info('Scheduled backup completed successfully', ['type' => $type]);

            return 0;
        } catch (Exception $e) {
            $this->logger->error('Scheduled backup failed: ' . $e->getMessage());
            $this->output->writeln('<error>Scheduled backup failed: ' . $e->getMessage() . '</error>');
            return 1;
        }
    }

    protected function shouldRunBackup(array $scheduleConfig, string $currentTime): bool
    {
        if (empty($scheduleConfig)) {
            return false;
        }

        foreach ($scheduleConfig as $name => $cronExpression) {
            if (!is_string($cronExpression)) {
                continue;
            }

            if ($this->isTimeMatch($cronExpression, $currentTime)) {
                $this->logger->info('Backup scheduled for current time', ['backup' => $name, 'cron' => $cronExpression]);
                return true;
            }
        }

        return false;
    }

    protected function isTimeMatch(string $cronExpression, string $currentTime): bool
    {
        $parts = explode(' ', trim($cronExpression));

        if (count($parts) < 5) {
            return false;
        }

        [$minute, $hour, $dayOfMonth, $month, $dayOfWeek] = $parts;

        [$currentHour, $currentMinute] = explode(':', $currentTime);
        $currentHour = (int) $currentHour;
        $currentMinute = (int) $currentMinute;

        $dayOfWeek = date('w');
        $currentDayOfMonth = date('j');
        $currentMonth = date('n');

        $minuteMatch = $this->matchCronPart($minute, $currentMinute);
        $hourMatch = $this->matchCronPart($hour, $currentHour);
        $monthMatch = $this->matchCronPart($month, $currentMonth);

        if (!$minuteMatch || !$hourMatch || !$monthMatch) {
            return false;
        }

        if ($dayOfMonth !== '*') {
            $dayMatch = $this->matchCronPart($dayOfMonth, $currentDayOfMonth);
            if (!$dayMatch) {
                return false;
            }
        }

        if ($dayOfWeek !== '*') {
            $weekMatch = $this->matchCronPart($dayOfWeek, (int) $dayOfWeek);
            if (!$weekMatch) {
                return false;
            }
        }

        return true;
    }

    protected function matchCronPart(string $part, int $currentValue): bool
    {
        if ($part === '*') {
            return true;
        }

        if (strpos($part, ',') !== false) {
            $values = explode(',', $part);
            return in_array($currentValue, array_map('intval', $values));
        }

        if (strpos($part, '/') !== false) {
            [$divisor, $remainder] = explode('/', $part);
            if ($currentValue % (int) $divisor === (int) $remainder) {
                return true;
            }
            return false;
        }

        return (int) $part === $currentValue;
    }

    protected function runDatabaseBackup(?string $connection, ?string $encrypt): void
    {
        $command = 'php ' . base_path('artisan') . ' backup:database';

        if ($connection) {
            $command .= ' --connection=' . $connection;
        }

        if ($encrypt) {
            $command .= ' --encrypt';
        }

        $this->executeCommand($command);
    }

    protected function runFileSystemBackup(?string $encrypt): void
    {
        $command = 'php ' . base_path('artisan') . ' backup:filesystem';

        if ($encrypt) {
            $command .= ' --encrypt';
        }

        $this->executeCommand($command);
    }

    protected function runConfigBackup(?string $encrypt): void
    {
        $command = 'php ' . base_path('artisan') . ' backup:config';

        if ($encrypt) {
            $command .= ' --encrypt';
        }

        $this->executeCommand($command);
    }

    protected function runComprehensiveBackup(?string $connection, ?string $encrypt): void
    {
        $command = 'php ' . base_path('artisan') . ' backup:all';

        if ($connection) {
            $command .= ' --connection=' . $connection;
        }

        if ($encrypt) {
            $command .= ' --encrypt';
        }

        $this->executeCommand($command);
    }

    protected function executeCommand(string $command): void
    {
        $this->output->write('Executing: ' . $command . '... ');

        $exitCode = 0;
        $output = [];
        exec($command, $output, $exitCode);

        if ($exitCode === 0) {
            $this->output->writeln('<info>OK</info>');
        } else {
            $this->output->writeln('<error>FAILED</error>');
        }
    }

    protected function configure(): void
    {
        $this->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Type of backup: database, filesystem, config, comprehensive, or scheduled');
        $this->addOption('connection', null, InputOption::VALUE_OPTIONAL, 'Database connection name');
        $this->addOption('encrypt', null, InputOption::VALUE_NONE, 'Encrypt backup file');
    }
}
