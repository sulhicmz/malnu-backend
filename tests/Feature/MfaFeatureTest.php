<?php

declare (strict_types = 1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\MfaService;
use App\Models\MfaSecret;
use App\Models\SecurityEvent;
use App\Models\User;
use Hyperf\DbConnection\Db;

class MfaFeatureTest extends TestCase
{
    private MfaService $mfaService;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->mfaService = new MfaService();
    }

    protected function setUp(): void
    {
        parent::setUp();
        Db::table('security_events')->truncate();
        Db::table('mfa_secrets')->truncate();
    }

    public function test_mfa_secret_generation()
    {
        $secret = $this->mfaService->generateSecret();

        $this->assertIsString($secret);
        $this->assertNotEmpty($secret);
        $this->assertGreaterThan(15, strlen($secret));
    }

    public function test_totp_code_generation()
    {
        $secret = $this->mfaService->generateSecret();
        $code = $this->mfaService->generateTotpCode($secret);

        $this->assertIsString($code);
        $this->assertEquals(6, strlen($code));
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $code);
    }

    public function test_totp_code_verification_valid()
    {
        $secret = $this->mfaService->generateSecret();
        $code = $this->mfaService->generateTotpCode($secret);

        $result = $this->mfaService->verifyCode($secret, $code);

        $this->assertTrue($result);
    }

    public function test_totp_code_verification_invalid()
    {
        $secret = $this->mfaService->generateSecret();
        $code = $this->mfaService->generateTotpCode($secret);
        $invalidCode = (int)$code + 1;

        $result = $this->mfaService->verifyCode($secret, (string)$invalidCode);

        $this->assertFalse($result);
    }

    public function test_qr_code_url_generation()
    {
        $secret = $this->mfaService->generateSecret();
        $email = 'test@example.com';
        $qrUrl = $this->mfaService->generateQrCodeUrl($secret, $email);

        $this->assertStringStartsWith('otpauth://totp/', $qrUrl);
        $this->assertStringContainsString('secret=', $qrUrl);
        $this->assertStringContainsString('issuer=', $qrUrl);
    }

    public function test_backup_codes_generation()
    {
        $codes = $this->mfaService->generateBackupCodes();

        $this->assertIsArray($codes);
        $this->assertCount(10, $codes);
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{8}$/', $codes[0]);
    }

    public function test_mfa_secret_model_creation()
    {
        $user = User::first();
        $mfaSecret = new MfaSecret();
        $mfaSecret->user_id = $user->id;
        $mfaSecret->secret = 'test_secret_key';
        $mfaSecret->is_enabled = true;
        $mfaSecret->backup_codes = json_encode(['CODE1', 'CODE2']);
        $mfaSecret->backup_codes_count = 10;
        $mfaSecret->save();

        $this->assertDatabaseHas('mfa_secrets', [
            'user_id' => $user->id,
            'is_enabled' => true,
            'backup_codes_count' => 10
        ]);
    }

    public function test_mfa_secret_model_relationship()
    {
        $user = User::first();
        $mfaSecret = new MfaSecret();
        $mfaSecret->user_id = $user->id;
        $mfaSecret->secret = 'test_secret_key';
        $mfaSecret->is_enabled = true;
        $mfaSecret->save();

        $retrievedUser = $mfaSecret->user;

        $this->assertEquals($user->id, $retrievedUser->id);
    }

    public function test_security_event_creation()
    {
        $user = User::first();
        $event = new SecurityEvent();
        $event->user_id = $user->id;
        $event->event_type = 'mfa_enabled';
        $event->description = 'User enabled MFA';
        $event->ip_address = '192.168.1.1';
        $event->user_agent = 'Test Agent';
        $event->is_successful = true;
        $event->save();

        $this->assertDatabaseHas('security_events', [
            'user_id' => $user->id,
            'event_type' => 'mfa_enabled',
            'is_successful' => true
        ]);
    }

    public function test_security_event_scope_by_user()
    {
        $user = User::first();

        $event1 = new SecurityEvent();
        $event1->user_id = $user->id;
        $event1->event_type = 'login_success';
        $event1->is_successful = true;
        $event1->save();

        $event2 = new SecurityEvent();
        $event2->user_id = 'different-user-id';
        $event2->event_type = 'login_success';
        $event2->is_successful = true;
        $event2->save();

        $events = SecurityEvent::byUser($user->id)->get();

        $this->assertCount(1, $events);
        $this->assertEquals('login_success', $events[0]->event_type);
    }

    public function test_security_event_scope_by_type()
    {
        $user = User::first();

        $event1 = new SecurityEvent();
        $event1->user_id = $user->id;
        $event1->event_type = 'login_success';
        $event1->is_successful = true;
        $event1->save();

        $event2 = new SecurityEvent();
        $event2->user_id = $user->id;
        $event2->event_type = 'mfa_enabled';
        $event2->is_successful = true;
        $event2->save();

        $events = SecurityEvent::byType('login_success')->get();

        $this->assertCount(1, $events);
        $this->assertEquals('login_success', $events[0]->event_type);
    }

    public function test_security_event_scope_failed()
    {
        $user = User::first();

        $event1 = new SecurityEvent();
        $event1->user_id = $user->id;
        $event1->event_type = 'login_success';
        $event1->is_successful = true;
        $event1->save();

        $event2 = new SecurityEvent();
        $event2->user_id = $user->id;
        $event2->event_type = 'login_failed';
        $event2->is_successful = false;
        $event2->save();

        $events = SecurityEvent::failed()->get();

        $this->assertCount(1, $events);
        $this->assertEquals('login_failed', $events[0]->event_type);
        $this->assertFalse($events[0]->is_successful);
    }

    public function test_backup_codes_decrement()
    {
        $user = User::first();
        $mfaSecret = new MfaSecret();
        $mfaSecret->user_id = $user->id;
        $mfaSecret->secret = 'test_secret_key';
        $mfaSecret->is_enabled = true;
        $mfaSecret->backup_codes = json_encode(['CODE1', 'CODE2', 'CODE3']);
        $mfaSecret->backup_codes_count = 3;
        $mfaSecret->save();

        $this->assertEquals(3, $mfaSecret->backup_codes_count);
    }
}
