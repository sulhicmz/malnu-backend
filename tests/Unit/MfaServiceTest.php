<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\MfaBackupCode;
use App\Models\MfaVerificationAttempt;
use App\Models\User;
use App\Models\UserMfaSetting;
use App\Services\MfaService;
use PHPUnit\Framework\TestCase;

class MfaServiceTest extends TestCase
{
    private MfaService $mfaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mfaService = new MfaService();
    }

    public function test_generate_secret_returns_valid_base32_string(): void
    {
        $secret = $this->mfaService->generateSecret();

        $this->assertIsString($secret);
        $this->assertGreaterThanOrEqual(16, strlen($secret));
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
    }

    public function test_generate_secret_returns_different_values(): void
    {
        $secret1 = $this->mfaService->generateSecret();
        $secret2 = $this->mfaService->generateSecret();

        $this->assertNotEquals($secret1, $secret2);
    }

    public function test_get_qr_code_url_returns_valid_url(): void
    {
        $secret = $this->mfaService->generateSecret();
        $url = $this->mfaService->getQrCodeUrl('Test App', 'test@example.com', $secret);

        $this->assertIsString($url);
        $this->assertStringStartsWith('otpauth://totp/', $url);
        $this->assertStringContains('Test%20App', $url);
        $this->assertStringContains('test@example.com', $url);
        $this->assertStringContains($secret, $url);
    }

    public function test_generate_qr_code_svg_returns_svg_string(): void
    {
        $secret = $this->mfaService->generateSecret();
        $svg = $this->mfaService->generateQrCodeSvg('Test App', 'test@example.com', $secret);

        $this->assertIsString($svg);
        $this->assertStringStartsWith('<?xml', $svg);
        $this->assertStringContains('<svg', $svg);
    }

    public function test_verify_totp_returns_true_for_valid_code(): void
    {
        $secret = $this->mfaService->generateSecret();
        
        $reflection = new \ReflectionClass($this->mfaService);
        $google2fa = $reflection->getProperty('google2fa');
        $google2fa->setAccessible(true);
        $google2faInstance = $google2fa->getValue($this->mfaService);
        
        $validCode = $google2faInstance->getCurrentOtp($secret);
        
        $result = $this->mfaService->verifyTotp($secret, $validCode);
        
        $this->assertTrue($result);
    }

    public function test_verify_totp_returns_false_for_invalid_code(): void
    {
        $secret = $this->mfaService->generateSecret();
        
        $result = $this->mfaService->verifyTotp($secret, '000000');
        
        $this->assertFalse($result);
    }

    public function test_verify_totp_returns_false_for_malformed_code(): void
    {
        $secret = $this->mfaService->generateSecret();
        
        $result = $this->mfaService->verifyTotp($secret, 'invalid');
        
        $this->assertFalse($result);
    }

    public function test_generate_backup_codes_returns_correct_count(): void
    {
        $userId = 'test-user-id';
        
        MfaBackupCode::where('user_id', $userId)->delete();
        
        $codes = $this->mfaService->generateBackupCodes($userId);
        
        $this->assertIsArray($codes);
        $this->assertCount(10, $codes);
        
        foreach ($codes as $code) {
            $this->assertIsString($code);
            $this->assertEquals(16, strlen($code));
            $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $code);
        }
    }

    public function test_generate_backup_codes_creates_database_records(): void
    {
        $userId = 'test-user-id';
        
        MfaBackupCode::where('user_id', $userId)->delete();
        
        $this->mfaService->generateBackupCodes($userId);
        
        $count = MfaBackupCode::where('user_id', $userId)->count();
        $this->assertEquals(10, $count);
    }

    public function test_generate_backup_codes_deletes_existing_codes(): void
    {
        $userId = 'test-user-id';
        
        MfaBackupCode::create([
            'user_id' => $userId,
            'code_hash' => password_hash('oldcode', PASSWORD_DEFAULT),
            'used' => false,
        ]);
        
        $this->mfaService->generateBackupCodes($userId);
        
        $oldCodeExists = MfaBackupCode::where('user_id', $userId)
            ->where('code_hash', password_hash('oldcode', PASSWORD_DEFAULT))
            ->exists();
        
        $this->assertFalse($oldCodeExists);
    }

    public function test_is_locked_out_returns_false_for_fresh_user(): void
    {
        $userId = 'test-user-id';
        
        MfaVerificationAttempt::where('user_id', $userId)->delete();
        
        $result = $this->mfaService->isLockedOut($userId);
        
        $this->assertFalse($result);
    }

    public function test_is_locked_out_returns_true_after_max_failed_attempts(): void
    {
        $userId = 'test-user-id';
        
        MfaVerificationAttempt::where('user_id', $userId)->delete();
        
        for ($i = 0; $i < 5; $i++) {
            MfaVerificationAttempt::create([
                'user_id' => $userId,
                'success' => false,
                'method' => 'totp',
                'attempted_at' => now(),
            ]);
        }
        
        $result = $this->mfaService->isLockedOut($userId);
        
        $this->assertTrue($result);
    }

    public function test_get_lockout_time_remaining_returns_zero_for_unlocked_user(): void
    {
        $userId = 'test-user-id';
        
        MfaVerificationAttempt::where('user_id', $userId)->delete();
        
        $result = $this->mfaService->getLockoutTimeRemaining($userId);
        
        $this->assertEquals(0, $result);
    }

    public function test_get_lockout_time_remaining_returns_minutes_for_locked_user(): void
    {
        $userId = 'test-user-id';
        
        MfaVerificationAttempt::where('user_id', $userId)->delete();
        
        for ($i = 0; $i < 5; $i++) {
            MfaVerificationAttempt::create([
                'user_id' => $userId,
                'success' => false,
                'method' => 'totp',
                'attempted_at' => now(),
            ]);
        }
        
        $result = $this->mfaService->getLockoutTimeRemaining($userId);
        
        $this->assertEquals(15, $result);
    }
}
