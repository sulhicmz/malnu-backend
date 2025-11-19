<?php

declare(strict_types=1);

namespace Tests;

use Hypervel\Foundation\Testing\Concerns\RunTestsInCoroutine;
use Hypervel\Foundation\Testing\TestCase as BaseTestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Hypervel\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use RunTestsInCoroutine;
    use RefreshDatabase;
    use WithFaker;
}
