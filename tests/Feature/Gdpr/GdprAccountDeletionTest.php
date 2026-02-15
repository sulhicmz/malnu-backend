<?php

declare(strict_types=1);

namespace Tests\Feature\Gdpr;

use App\Models\User;
use Tests\TestCase;

class GdprAccountDeletionTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function testUserCanValidateDeletion(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/api/gdpr/deletion/validate');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'can_delete' => true,
                ],
            ]);
    }

    public function testUserCanScheduleDeletion(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/api/gdpr/deletion/request', [
                'delay_days' => 30,
                'reason' => 'Moving to another school',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Account deletion scheduled',
            ]);

        $this->user->refresh();
        $this->assertEquals('scheduled_deletion', $this->user->key_status);
    }

    public function testUserCanCancelScheduledDeletion(): void
    {
        $this->user->update(['key_status' => 'scheduled_deletion']);

        $response = $this->actingAs($this->user)
            ->post('/api/gdpr/deletion/cancel');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Scheduled deletion cancelled successfully',
            ]);

        $this->user->refresh();
        $this->assertNull($this->user->key_status);
    }

    public function testCancelDeletionFailsIfNotScheduled(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/api/gdpr/deletion/cancel');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'NO_SCHEDULED_DELETION',
                ],
            ]);
    }

    public function testDeletionRequiresAuthentication(): void
    {
        $response = $this->get('/api/gdpr/deletion/validate');

        $response->assertStatus(401);
    }
}
