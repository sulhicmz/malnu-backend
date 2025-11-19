<?php

declare(strict_types=1);

namespace Tests;

use Hypervel\Foundation\Testing\Concerns\RunTestsInCoroutine;
use Hypervel\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RunTestsInCoroutine;
    
    protected function setUp(): void
    {
        parent::setUp();
        // Add any common setup here
    }
}
