<?php

declare(strict_types=1);

namespace Tests\Feature\Gdpr;

use App\Models\User;
use App\Models\UserConsent;
use Tests\TestCase;

class GdprConsentTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function testUserCanGetConsentStatus(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/api/gdpr/consents');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function testUserCanRecordConsent(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/api/gdpr/consents', [
                'consent_type' => UserConsent::CONSENT_PRIVACY_POLICY,
                'consent_given' => true,
                'version' => '1.0',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Consent recorded successfully',
            ]);

        $this->assertDatabaseHas('user_consents', [
            'user_id' => $this->user->id,
            'consent_type' => UserConsent::CONSENT_PRIVACY_POLICY,
            'consent_given' => true,
        ]);
    }

    public function testUserCannotRecordInvalidConsentType(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/api/gdpr/consents', [
                'consent_type' => 'invalid_type',
                'consent_given' => true,
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CONSENT_TYPE',
                ],
            ]);
    }

    public function testUserCanWithdrawConsent(): void
    {
        UserConsent::record(
            $this->user->id,
            UserConsent::CONSENT_MARKETING,
            true,
            '1.0'
        );

        $response = $this->actingAs($this->user)
            ->post('/api/gdpr/consents/withdraw', [
                'consent_type' => UserConsent::CONSENT_MARKETING,
                'reason' => 'No longer interested',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Consent withdrawn successfully',
            ]);

        $consent = UserConsent::where('user_id', $this->user->id)
            ->where('consent_type', UserConsent::CONSENT_MARKETING)
            ->latest()
            ->first();

        $this->assertNotNull($consent->withdrawn_at);
    }

    public function testConsentStatusRequiresAuthentication(): void
    {
        $response = $this->get('/api/gdpr/consents');

        $response->assertStatus(401);
    }
}
