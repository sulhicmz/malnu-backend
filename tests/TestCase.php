<?php

declare(strict_types=1);

namespace Tests;

use Hyperf\Foundation\Testing\Concerns\RunTestsInCoroutine;
use Hyperf\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RunTestsInCoroutine;
}
