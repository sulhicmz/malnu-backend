<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserConsent;
use Tests\TestCase;

class UserConsentTest extends TestCase
{
    public function testCanRecordConsent(): void
    {
        $user = User::factory()->create();

        $consent = UserConsent::record(
            $user->id,
            UserConsent::CONSENT_TERMS_OF_SERVICE,
            true,
            '1.0'
        );

        $this->assertInstanceOf(UserConsent::class, $consent);
        $this->assertEquals($user->id, $consent->user_id);
        $this->assertEquals(UserConsent::CONSENT_TERMS_OF_SERVICE, $consent->consent_type);
        $this->assertTrue($consent->consent_given);
    }

    public function testCanCheckActiveConsent(): void
    {
        $user = User::factory()->create();

        UserConsent::record(
            $user->id,
            UserConsent::CONSENT_PRIVACY_POLICY,
            true,
            '1.0'
        );

        $this->assertTrue(
            UserConsent::hasActiveConsent($user->id, UserConsent::CONSENT_PRIVACY_POLICY)
        );
    }

    public function testInactiveConsentReturnsFalse(): void
    {
        $user = User::factory()->create();

        $consent = UserConsent::record(
            $user->id,
            UserConsent::CONSENT_MARKETING,
            true,
            '1.0'
        );

        $consent->withdraw('User request');

        $this->assertFalse(
            UserConsent::hasActiveConsent($user->id, UserConsent::CONSENT_MARKETING)
        );
    }

    public function testCanWithdrawConsent(): void
    {
        $user = User::factory()->create();

        $consent = UserConsent::record(
            $user->id,
            UserConsent::CONSENT_ANALYTICS,
            true,
            '1.0'
        );

        $consent->withdraw('No longer want analytics');

        $this->assertNotNull($consent->withdrawn_at);
        $this->assertEquals('No longer want analytics', $consent->withdrawn_reason);
    }

    public function testCanWithdrawAllConsents(): void
    {
        $user = User::factory()->create();

        UserConsent::record($user->id, UserConsent::CONSENT_TERMS_OF_SERVICE, true, '1.0');
        UserConsent::record($user->id, UserConsent::CONSENT_PRIVACY_POLICY, true, '1.0');
        UserConsent::record($user->id, UserConsent::CONSENT_MARKETING, true, '1.0');

        $count = UserConsent::withdrawAllForUser($user->id, 'Account deletion');

        $this->assertEquals(3, $count);
        $this->assertEquals(0, UserConsent::active()->where('user_id', $user->id)->count());
    }

    public function testGetActiveConsentsForUser(): void
    {
        $user = User::factory()->create();

        UserConsent::record($user->id, UserConsent::CONSENT_TERMS_OF_SERVICE, true, '1.0');
        UserConsent::record($user->id, UserConsent::CONSENT_PRIVACY_POLICY, true, '1.0');

        $activeConsents = UserConsent::getActiveConsentsForUser($user->id);

        $this->assertCount(2, $activeConsents);
        $this->assertContains(UserConsent::CONSENT_TERMS_OF_SERVICE, $activeConsents);
        $this->assertContains(UserConsent::CONSENT_PRIVACY_POLICY, $activeConsents);
    }

    public function testIsActiveReturnsCorrectStatus(): void
    {
        $user = User::factory()->create();

        $activeConsent = UserConsent::record($user->id, UserConsent::CONSENT_COOKIES, true, '1.0');
        $this->assertTrue($activeConsent->isActive());

        $inactiveConsent = UserConsent::record($user->id, UserConsent::CONSENT_THIRD_PARTY, false, '1.0');
        $this->assertFalse($inactiveConsent->isActive());

        $withdrawnConsent = UserConsent::record($user->id, UserConsent::CONSENT_DATA_PROCESSING, true, '1.0');
        $withdrawnConsent->withdraw();
        $this->assertFalse($withdrawnConsent->isActive());
    }
}
