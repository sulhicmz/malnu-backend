<?php

declare (strict_types = 1);

namespace App\Contracts;

interface MfaServiceInterface
{
    public function generateSecret(): string;

    public function generateTotpCode(string $secret): string;

    public function verifyCode(string $secret, string $code, int $window = 1): bool;

    public function generateQrCodeUrl(string $secret, string $email): string;

    public function generateBackupCodes(int $count = 10): array;
}
