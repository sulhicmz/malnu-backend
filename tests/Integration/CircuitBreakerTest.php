<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Patterns\CircuitBreaker;
use App\Patterns\CircuitBreakerOpenException;
use App\Patterns\RetryWithBackoff;
use Exception;
use Hyperf\Cache\Cache;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 * @coversNothing
 */
class CircuitBreakerTest extends TestCase
{
    private Cache $cache;

    private CircuitBreaker $circuitBreaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = new Cache();
        $this->circuitBreaker = new CircuitBreaker(
            $this->cache,
            'test_service',
            3,
            5
        );
    }

    protected function tearDown(): void
    {
        $this->circuitBreaker->reset();
        parent::tearDown();
    }

    public function testCircuitBreakerClosedOnSuccess()
    {
        $result = $this->circuitBreaker->call(function () {
            return 'success';
        });

        $this->assertEquals('success', $result);
        $this->assertEquals('closed', $this->circuitBreaker->getMetrics()['state']);
    }

    public function testCircuitBreakerOpensAfterFailureThreshold()
    {
        $this->expectException(CircuitBreakerOpenException::class);

        for ($i = 0; $i < 3; ++$i) {
            try {
                $this->circuitBreaker->call(function () {
                    throw new Exception('Service failed');
                });
            } catch (Exception $e) {
                if ($i < 2) {
                    continue;
                }
                throw $e;
            }
        }

        $metrics = $this->circuitBreaker->getMetrics();
        $this->assertEquals('open', $metrics['state']);
    }

    public function testCircuitBreakerPreventsCallsWhenOpen()
    {
        $this->expectException(CircuitBreakerOpenException::class);

        $callCount = 0;

        for ($i = 0; $i < 3; ++$i) {
            try {
                $this->circuitBreaker->call(function () use (&$callCount) {
                    ++$callCount;
                    throw new Exception('Service failed');
                });
            } catch (Exception $e) {
            }
        }

        $this->expectException(CircuitBreakerOpenException::class);
        $this->circuitBreaker->call(function () use (&$callCount) {
            ++$callCount;
            return 'should not execute';
        });

        $this->assertEquals(3, $callCount);
    }

    public function testCircuitBreakerResetsOnSuccess()
    {
        for ($i = 0; $i < 3; ++$i) {
            try {
                $this->circuitBreaker->call(function () {
                    throw new Exception('Service failed');
                });
            } catch (Exception $e) {
            }
        }

        $this->assertEquals('open', $this->circuitBreaker->getMetrics()['state']);

        $this->circuitBreaker->reset();

        $result = $this->circuitBreaker->call(function () {
            return 'success after reset';
        });

        $this->assertEquals('success after reset', $result);
        $this->assertEquals('closed', $this->circuitBreaker->getMetrics()['state']);
    }

    public function testCircuitBreakerMetrics()
    {
        $this->circuitBreaker->call(function () {
            return 'success';
        });

        $metrics = $this->circuitBreaker->getMetrics();

        $this->assertEquals('test_service', $metrics['service']);
        $this->assertEquals('closed', $metrics['state']);
        $this->assertEquals(0, $metrics['failures']);
        $this->assertEquals(3, $metrics['failure_threshold']);
        $this->assertEquals(5, $metrics['timeout_seconds']);
    }
}

/**
 * @internal
 * @coversNothing
 */
class RetryWithBackoffTest extends TestCase
{
    private RetryWithBackoff $retry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->retry = new RetryWithBackoff(3, 10, 2.0, 100);
    }

    public function testRetrySucceedsOnFirstAttempt()
    {
        $callCount = 0;

        $result = $this->retry->execute(function () use (&$callCount) {
            ++$callCount;
            return 'success';
        });

        $this->assertEquals('success', $result);
        $this->assertEquals(1, $callCount);
    }

    public function testRetrySucceedsAfterFailures()
    {
        $callCount = 0;

        $result = $this->retry->execute(function () use (&$callCount) {
            ++$callCount;
            if ($callCount < 2) {
                throw new Exception('Temporary failure');
            }
            return 'success after retry';
        });

        $this->assertEquals('success after retry', $result);
        $this->assertEquals(2, $callCount);
    }

    public function testRetryFailsAfterMaxAttempts()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Final failure');

        $callCount = 0;

        $this->retry->execute(function () use (&$callCount) {
            ++$callCount;
            throw new Exception('Final failure');
        });

        $this->assertEquals(3, $callCount);
    }

    public function testRetryRespectsRetryableExceptions()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Non-retryable error');

        $callCount = 0;

        $this->retry->execute(
            function () use (&$callCount) {
                ++$callCount;
                throw new Exception('Non-retryable error');
            },
            [RuntimeException::class]
        );

        $this->assertEquals(1, $callCount);
    }

    public function testRetryWithRetryableException()
    {
        $callCount = 0;

        $result = $this->retry->execute(
            function () use (&$callCount) {
                ++$callCount;
                if ($callCount < 2) {
                    throw new RuntimeException('Temporary runtime error');
                }
                return 'success';
            },
            [RuntimeException::class]
        );

        $this->assertEquals('success', $result);
        $this->assertEquals(2, $callCount);
    }
}
