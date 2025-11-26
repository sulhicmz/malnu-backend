<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hyperf\Console\Command;

class BackupCommand extends Command
{
    protected ?string $signature = 'backup:run {--type=full : Type of backup: full, database, files}';
    
    protected string $description = 'Create a backup of the system';

    public function handle()
    {
        $type = $this->input->getOption('type');
        
        $this->output->writeln('<info>Starting backup process...</info>');
        $this->output->writeln("<info>Backup type: {$type}</info>");

        try {
            $backupService = new \App\Services\BackupService();
            
            switch ($type) {
                case 'database':
                    $path = $backupService->createDatabaseBackup();
                    $this->output->writeln("<info>Database backup created: {$path}</info>");
                    break;
                case 'files':
                    $path = $backupService->createFileBackup();
                    $this->output->writeln("<info>File backup created: {$path}</info>");
                    break;
                case 'full':
                default:
                    $results = $backupService->createFullBackup();
                    $this->output->writeln("<info>Full backup created:</info>");
                    $this->output->writeln("  - Database: {$results['database']}");
                    $this->output->writeln("  - Files: {$results['files']}");
                    break;
            }
            
            $this->output->writeln('<info>Backup process completed successfully!</info>');
        } catch (\Exception $e) {
            $this->output->writeln("<error>Backup failed: " . $e->getMessage() . "</error>");
            return 1;
        }

        return 0;
    }
}