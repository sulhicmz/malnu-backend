<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MfaServiceInterface;
use App\Models\MfaBackupCode;
use App\Models\MfaVerificationAttempt;
use App\Models\UserMfaSetting;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

class MfaService implements MfaServiceInterface
{
    private Google2FA $google2fa;

    private const MAX_FAILED_ATTEMPTS = 5;

    private const LOCKOUT_MINUTES = 15;

    private const BACKUP_CODES_COUNT = 10;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a new MFA secret for a user.
     */
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Get QR code URL for MFA setup.
     */
    public function getQrCodeUrl(string $companyName, string $email, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl($companyName, $email, $secret);
    }

    /**
     * Generate SVG QR code for MFA setup.
     */
    public function generateQrCodeSvg(string $companyName, string $email, string $secret): string
    {
        $qrCodeUrl = $this->getQrCodeUrl($companyName, $email, $secret);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        return $writer->writeString($qrCodeUrl);
    }

    /**
     * Verify TOTP code.
     */
    public function verifyTotp(string $secret, string $code): bool
    {
        try {
            return $this->google2fa->verifyKey($secret, $code);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Setup MFA for a user.
     */
    public function setupMfa(string $userId, string $type = 'totp'): array
    {
        $secret = $this->generateSecret();

        UserMfaSetting::updateOrCreate(
            ['user_id' => $userId],
            [
                'mfa_secret' => $secret,
                'mfa_type' => $type,
                'mfa_enabled' => false,
            ]
        );

        return [
            'secret' => $secret,
            'qr_code_url' => null,
        ];
    }

    /**
     * Enable MFA for a user after verification.
     */
    public function enableMfa(string $userId, string $code): bool
    {
        $mfaSetting = UserMfaSetting::where('user_id', $userId)->first();

        if (! $mfaSetting || ! $mfaSetting->mfa_secret) {
            return false;
        }

        if (! $this->verifyTotp($mfaSetting->mfa_secret, $code)) {
            return false;
        }

        $this->generateBackupCodes($userId);

        $mfaSetting->markAsEnabled();
        $mfaSetting->markAsVerified();
        $mfaSetting->update(['backup_codes_count' => self::BACKUP_CODES_COUNT]);

        return true;
    }

    /**
     * Disable MFA for a user.
     */
    public function disableMfa(string $userId): bool
    {
        $mfaSetting = UserMfaSetting::where('user_id', $userId)->first();

        if (! $mfaSetting) {
            return false;
        }

        MfaBackupCode::deleteAllForUser($userId);
        $mfaSetting->markAsDisabled();

        return true;
    }

    /**
     * Verify MFA code during login.
     */
    public function verifyMfa(string $userId, string $code, ?string $ipAddress = null, ?string $userAgent = null): bool
    {
        if ($this->isLockedOut($userId, $ipAddress)) {
            return false;
        }

        $mfaSetting = UserMfaSetting::where('user_id', $userId)->first();

        if (! $mfaSetting || ! $mfaSetting->isMfaEnabled()) {
            return false;
        }

        if ($this->verifyTotp($mfaSetting->mfa_secret, $code)) {
            $this->logAttempt($userId, true, 'totp', $ipAddress, $userAgent);
            return true;
        }

        if ($this->verifyBackupCode($userId, $code)) {
            $this->logAttempt($userId, true, 'backup_code', $ipAddress, $userAgent);
            return true;
        }

        $this->logAttempt($userId, false, 'totp', $ipAddress, $userAgent);

        return false;
    }

    /**
     * Check if user has MFA enabled.
     */
    public function isMfaEnabled(string $userId): bool
    {
        $mfaSetting = UserMfaSetting::where('user_id', $userId)->first();

        return $mfaSetting && $mfaSetting->isMfaEnabled();
    }

    /**
     * Generate backup codes for a user.
     */
    public function generateBackupCodes(string $userId): array
    {
        MfaBackupCode::deleteAllForUser($userId);

        $codes = [];
        $plainCodes = [];

        for ($i = 0; $i < self::BACKUP_CODES_COUNT; $i++) {
            $plainCode = $this->generateBackupCode();
            $hashedCode = password_hash($plainCode, PASSWORD_DEFAULT);

            MfaBackupCode::create([
                'user_id' => $userId,
                'code_hash' => $hashedCode,
                'used' => false,
            ]);

            $plainCodes[] = $plainCode;
            $codes[] = [
                'plain' => $plainCode,
                'hashed' => $hashedCode,
            ];
        }

        $mfaSetting = UserMfaSetting::where('user_id', $userId)->first();
        if ($mfaSetting) {
            $mfaSetting->update(['backup_codes_count' => self::BACKUP_CODES_COUNT]);
        }

        return $plainCodes;
    }

    /**
     * Verify a backup code.
     */
    public function verifyBackupCode(string $userId, string $code): bool
    {
        $backupCodes = MfaBackupCode::getUnusedCodesForUser($userId);

        foreach ($backupCodes as $backupCode) {
            if (password_verify($code, $backupCode->code_hash)) {
                $backupCode->markAsUsed();

                $remainingCodes = MfaBackupCode::countUnusedCodesForUser($userId);
                UserMfaSetting::where('user_id', $userId)->update([
                    'backup_codes_count' => $remainingCodes,
                ]);

                return true;
            }
        }

        return false;
    }

    /**
     * Get remaining backup codes count.
     */
    public function getRemainingBackupCodesCount(string $userId): int
    {
        return MfaBackupCode::countUnusedCodesForUser($userId);
    }

    /**
     * Get MFA status for a user.
     */
    public function getMfaStatus(string $userId): array
    {
        $mfaSetting = UserMfaSetting::where('user_id', $userId)->first();

        if (! $mfaSetting) {
            return [
                'enabled' => false,
                'type' => null,
                'backup_codes_remaining' => 0,
            ];
        }

        return [
            'enabled' => $mfaSetting->isMfaEnabled(),
            'type' => $mfaSetting->mfa_type,
            'enabled_at' => $mfaSetting->mfa_enabled_at,
            'verified_at' => $mfaSetting->mfa_verified_at,
            'backup_codes_remaining' => $mfaSetting->backup_codes_count,
        ];
    }

    /**
     * Regenerate backup codes.
     */
    public function regenerateBackupCodes(string $userId): array
    {
        return $this->generateBackupCodes($userId);
    }

    /**
     * Check if user is locked out due to too many failed attempts.
     */
    public function isLockedOut(string $userId, ?string $ipAddress = null): bool
    {
        $failedAttempts = MfaVerificationAttempt::getRecentFailedAttempts($userId, self::LOCKOUT_MINUTES);

        if ($failedAttempts >= self::MAX_FAILED_ATTEMPTS) {
            return true;
        }

        if ($ipAddress) {
            $ipFailedAttempts = MfaVerificationAttempt::getRecentFailedAttemptsFromIp($ipAddress, self::LOCKOUT_MINUTES);

            if ($ipFailedAttempts >= self::MAX_FAILED_ATTEMPTS * 2) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get lockout time remaining in minutes.
     */
    public function getLockoutTimeRemaining(string $userId): int
    {
        $failedAttempts = MfaVerificationAttempt::getRecentFailedAttempts($userId, self::LOCKOUT_MINUTES);

        if ($failedAttempts < self::MAX_FAILED_ATTEMPTS) {
            return 0;
        }

        return self::LOCKOUT_MINUTES;
    }

    /**
     * Log MFA verification attempt.
     */
    private function logAttempt(
        string $userId,
        bool $success,
        string $method,
        ?string $ipAddress,
        ?string $userAgent
    ): void {
        MfaVerificationAttempt::logAttempt($userId, $success, $method, $ipAddress, $userAgent);
    }

    /**
     * Generate a random backup code.
     */
    private function generateBackupCode(): string
    {
        return bin2hex(random_bytes(4));
    }
}
