<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;

class WebRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}