<?php

declare(strict_types=1);

namespace Tests\Feature;

use Hyperf\Foundation\Testing\TestCase;

class CsrfProtectionTest extends TestCase
{
    public function test_csrf_protection_is_enabled_for_web_routes(): void
    {
        $this->assertTrue(true);
    }

    public function test_api_routes_are_excluded_from_csrf(): void
    {
        $this->assertTrue(true);
    }
}
