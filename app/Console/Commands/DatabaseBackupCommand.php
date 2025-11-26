<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hyperf\Console\Command;
use App\Services\BackupService;

class DatabaseBackupCommand extends Command
{
    protected ?string $signature = 'backup:database';
    
    protected string $description = 'Create a backup of the database';

    public function handle()
    {
        $backupService = new BackupService();
        
        $this->output->writeln('<info>Starting database backup...</info>');

        try {
            $backupPath = $backupService->createDatabaseBackup();
            $this->output->writeln("<info>Database backup created successfully: {$backupPath}</info>");
        } catch (\Exception $e) {
            $this->output->writeln("<error>Database backup failed: " . $e->getMessage() . "</error>");
            return 1;
        }

        return 0;
    }
}