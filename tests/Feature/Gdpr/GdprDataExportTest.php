<?php

declare(strict_types=1);

namespace Tests\Feature\Gdpr;

use App\Models\User;
use Tests\TestCase;

class GdprDataExportTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function testUserCanExportDataAsJson(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/api/gdpr/export?format=json');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json')
            ->assertHeader('Content-Disposition');
    }

    public function testUserCanExportDataAsCsv(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/api/gdpr/export?format=csv');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv')
            ->assertHeader('Content-Disposition');
    }

    public function testExportRequiresAuthentication(): void
    {
        $response = $this->get('/api/gdpr/export');

        $response->assertStatus(401);
    }

    public function testExportValidatesFormat(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/api/gdpr/export?format=invalid');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_FORMAT',
                ],
            ]);
    }
}
