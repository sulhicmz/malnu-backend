<?php

declare(strict_types=1);

namespace App\Contracts;

interface BackupServiceInterface
{
    public function createBackup(string $type = 'all', array $options = []): array;

    public function restoreBackup(string $backupPath, string $type = 'all', array $options = []): array;

    public function verifyBackup(string $backupPath, string $type = 'all'): array;

    public function getBackupStatus(): array;

    public function cleanOldBackups(string $type = 'all', int $keep = 5): array;
}
