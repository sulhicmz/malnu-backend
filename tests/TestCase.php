<?php

declare(strict_types=1);

namespace Tests;

use Hypervel\Foundation\Testing\TestCase as BaseTestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Hypervel\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed the database for all tests
        $this->artisan('db:seed');
    }
}
