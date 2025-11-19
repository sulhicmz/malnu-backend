<?php

declare(strict_types=1);

namespace Tests;

use Hypervel\Foundation\Testing\Concerns\RunTestsInCoroutine;
use Hypervel\Foundation\Testing\TestCase as BaseTestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RunTestsInCoroutine;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Refresh database to ensure clean state
        $this->refreshDatabase();
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
