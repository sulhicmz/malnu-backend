<?php

declare(strict_types=1);

namespace App\Contracts;

interface MfaServiceInterface
{
    public function generateSecret(): string;

    public function getQrCodeUrl(string $companyName, string $email, string $secret): string;

    public function generateQrCodeSvg(string $companyName, string $email, string $secret): string;

    public function verifyTotp(string $secret, string $code): bool;

    public function setupMfa(string $userId, string $type = 'totp'): array;

    public function enableMfa(string $userId, string $code): bool;

    public function disableMfa(string $userId): bool;

    public function verifyMfa(string $userId, string $code, ?string $ipAddress = null, ?string $userAgent = null): bool;

    public function isMfaEnabled(string $userId): bool;

    public function generateBackupCodes(string $userId): array;

    public function verifyBackupCode(string $userId, string $code): bool;

    public function getRemainingBackupCodesCount(string $userId): int;

    public function getMfaStatus(string $userId): array;

    public function regenerateBackupCodes(string $userId): array;

    public function isLockedOut(string $userId, ?string $ipAddress = null): bool;

    public function getLockoutTimeRemaining(string $userId): int;
}
