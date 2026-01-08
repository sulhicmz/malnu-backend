<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\RetryService;
use Exception;
use InvalidArgumentException;
use PDOException;
use RuntimeException;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RetryServiceTest extends TestCase
{
    private RetryService $retryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->retryService = new RetryService(
            maxAttempts: 3,
            baseDelay: 10,
            exponentialFactor: 2.0,
            maxDelay: 100
        );
    }

    public function testSuccessfulCallExecutesOnceWithoutRetry()
    {
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            return 'success';
        };

        $result = $this->retryService->call($callback);

        $this->assertEquals('success', $result);
        $this->assertEquals(1, $attempts);
    }

    public function testFailedRetryableCallRetriesUntilMaxAttempts()
    {
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 3) {
                throw new RuntimeException('Transient error');
            }
            return 'success';
        };

        $result = $this->retryService->call($callback);

        $this->assertEquals('success', $result);
        $this->assertEquals(3, $attempts);
    }

    public function testNonRetryableExceptionFailsImmediately()
    {
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            throw new Exception('Non-retryable error');
        };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Non-retryable error');

        try {
            $this->retryService->call($callback);
        } catch (Exception $e) {
            $this->assertEquals(1, $attempts);
            throw $e;
        }
    }

    public function testRuntimeExceptionIsRetryable()
    {
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 2) {
                throw new RuntimeException('Runtime error');
            }
            return 'success';
        };

        $result = $this->retryService->call($callback);

        $this->assertEquals('success', $result);
        $this->assertEquals(2, $attempts);
    }

    public function testPdoExceptionIsRetryable()
    {
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 2) {
                throw new PDOException('Database connection lost');
            }
            return 'db_success';
        };

        $result = $this->retryService->call($callback);

        $this->assertEquals('db_success', $result);
        $this->assertEquals(2, $attempts);
    }

    public function testConnectionRefusedErrorIsRetryable()
    {
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 2) {
                throw new Exception('Connection refused');
            }
            return 'connected';
        };

        $result = $this->retryService->call($callback);

        $this->assertEquals('connected', $result);
        $this->assertEquals(2, $attempts);
    }

    public function testConnectionTimeoutErrorIsRetryable()
    {
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 2) {
                throw new Exception('Connection timed out');
            }
            return 'connected';
        };

        $result = $this->retryService->call($callback);

        $this->assertEquals('connected', $result);
        $this->assertEquals(2, $attempts);
    }

    public function testConnectionResetErrorIsRetryable()
    {
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 2) {
                throw new Exception('Connection reset');
            }
            return 'connected';
        };

        $result = $this->retryService->call($callback);

        $this->assertEquals('connected', $result);
        $this->assertEquals(2, $attempts);
    }

    public function testHostDownErrorIsRetryable()
    {
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 2) {
                throw new Exception('Host is down');
            }
            return 'connected';
        };

        $result = $this->retryService->call($callback);

        $this->assertEquals('connected', $result);
        $this->assertEquals(2, $attempts);
    }

    public function testDeadlockErrorIsRetryable()
    {
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 2) {
                throw new Exception('Deadlock found when trying to get lock');
            }
            return 'lock_acquired';
        };

        $result = $this->retryService->call($callback);

        $this->assertEquals('lock_acquired', $result);
        $this->assertEquals(2, $attempts);
    }

    public function testSqlstateHy000ErrorIsRetryable()
    {
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 2) {
                throw new Exception('SQLSTATE[HY000]: General error');
            }
            return 'query_success';
        };

        $result = $this->retryService->call($callback);

        $this->assertEquals('query_success', $result);
        $this->assertEquals(2, $attempts);
    }

    public function testMaxAttemptsExceededThrowsException()
    {
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            throw new RuntimeException('Persistent error');
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Persistent error');

        try {
            $this->retryService->call($callback);
        } catch (Exception $e) {
            $this->assertEquals(3, $attempts);
            throw $e;
        }
    }

    public function testOnRetryCallbackIsCalledOnEachRetry()
    {
        $attempts = 0;
        $retryAttempts = [];
        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 3) {
                throw new RuntimeException('Error');
            }
            return 'success';
        };

        $onRetry = function ($attempt, $exception, $delay) use (&$retryAttempts) {
            $retryAttempts[] = [
                'attempt' => $attempt,
                'exception' => $exception->getMessage(),
                'delay' => $delay,
            ];
        };

        $result = $this->retryService->call($callback, $onRetry);

        $this->assertEquals('success', $result);
        $this->assertCount(2, $retryAttempts);
        $this->assertEquals(1, $retryAttempts[0]['attempt']);
        $this->assertEquals('Error', $retryAttempts[0]['exception']);
        $this->assertEquals(2, $retryAttempts[1]['attempt']);
        $this->assertGreaterThan(0, $retryAttempts[0]['delay']);
        $this->assertGreaterThan(0, $retryAttempts[1]['delay']);
    }

    public function testExponentialBackoffIncreasesDelay()
    {
        $delays = [];
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 3) {
                throw new RuntimeException('Error');
            }
            return 'success';
        };

        $onRetry = function ($attempt, $exception, $delay) use (&$delays) {
            $delays[$attempt] = $delay;
        };

        $result = $this->retryService->call($callback, $onRetry);

        $this->assertEquals('success', $result);
        $this->assertArrayHasKey(1, $delays);
        $this->assertArrayHasKey(2, $delays);
        $this->assertGreaterThan($delays[1], $delays[2]);
    }

    public function testMaxDelayLimitsExponentialBackoff()
    {
        $delays = [];
        $attempts = 0;

        $retryService = new RetryService(
            maxAttempts: 5,
            baseDelay: 100,
            exponentialFactor: 10.0,
            maxDelay: 150
        );

        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 5) {
                throw new RuntimeException('Error');
            }
            return 'success';
        };

        $onRetry = function ($attempt, $exception, $delay) use (&$delays) {
            $delays[$attempt] = $delay;
        };

        $result = $retryService->call($callback, $onRetry);

        $this->assertEquals('success', $result);
        foreach ($delays as $delay) {
            $this->assertLessThanOrEqual(150, $delay);
        }
    }

    public function testJitterIsAddedToDelay()
    {
        $delays = [];
        $attempts = 0;

        $retryService = new RetryService(
            maxAttempts: 3,
            baseDelay: 100,
            exponentialFactor: 1.0,
            maxDelay: 200
        );

        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 3) {
                throw new RuntimeException('Error');
            }
            return 'success';
        };

        $onRetry = function ($attempt, $exception, $delay) use (&$delays) {
            $delays[] = $delay;
        };

        $result = $retryService->call($callback, $onRetry);

        $this->assertEquals('success', $result);

        $firstDelay = $delays[0];
        $secondDelay = $delays[1];

        $this->assertGreaterThanOrEqual(90, $firstDelay);
        $this->assertLessThanOrEqual(110, $firstDelay);
        $this->assertGreaterThanOrEqual(90, $secondDelay);
        $this->assertLessThanOrEqual(110, $secondDelay);
    }

    public function testCallWithFallbackReturnsFallbackOnFailure()
    {
        $callback = fn () => throw new Exception('Service unavailable');
        $fallback = fn () => 'cached_response';

        $result = $this->retryService->callWithFallback($callback, $fallback);

        $this->assertEquals('cached_response', $result);
    }

    public function testCallWithFallbackIgnoresFallbackOnSuccess()
    {
        $callback = fn () => 'live_response';
        $fallback = fn () => 'cached_response';

        $result = $this->retryService->callWithFallback($callback, $fallback);

        $this->assertEquals('live_response', $result);
    }

    public function testAddRetryableExceptionAddsCustomException()
    {
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 2) {
                throw new InvalidArgumentException('Custom retryable');
            }
            return 'success';
        };

        $this->retryService->addRetryableException(InvalidArgumentException::class);

        $result = $this->retryService->call($callback);

        $this->assertEquals('success', $result);
        $this->assertEquals(2, $attempts);
    }

    public function testSetMaxAttemptsChangesMaxAttempts()
    {
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            throw new RuntimeException('Error');
        };

        $this->retryService->setMaxAttempts(5);

        $this->expectException(RuntimeException::class);

        try {
            $this->retryService->call($callback);
        } catch (Exception $e) {
            $this->assertEquals(5, $attempts);
            throw $e;
        }
    }

    public function testSetBaseDelayChangesBaseDelay()
    {
        $delays = [];
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 2) {
                throw new RuntimeException('Error');
            }
            return 'success';
        };

        $onRetry = function ($attempt, $exception, $delay) use (&$delays) {
            $delays[] = $delay;
        };

        $this->retryService->setBaseDelay(50);

        $result = $this->retryService->call($callback, $onRetry);

        $this->assertEquals('success', $result);
        $this->assertGreaterThanOrEqual(45, $delays[0]);
        $this->assertLessThanOrEqual(55, $delays[0]);
    }

    public function testSetExponentialFactorChangesBackoffFactor()
    {
        $delays = [];
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 3) {
                throw new RuntimeException('Error');
            }
            return 'success';
        };

        $onRetry = function ($attempt, $exception, $delay) use (&$delays) {
            $delays[] = $delay;
        };

        $this->retryService->setExponentialFactor(3.0);

        $result = $this->retryService->call($callback, $onRetry);

        $this->assertEquals('success', $result);
        $expectedRatio = 3.0;
        $actualRatio = $delays[1] / $delays[0];
        $this->assertEqualsWithDelta($expectedRatio, $actualRatio, 0.3);
    }

    public function testNetworkUnreachableErrorIsRetryable()
    {
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 2) {
                throw new Exception('Network is unreachable');
            }
            return 'connected';
        };

        $result = $this->retryService->call($callback);

        $this->assertEquals('connected', $result);
        $this->assertEquals(2, $attempts);
    }

    public function testTimeoutExpiredErrorIsRetryable()
    {
        $attempts = 0;
        $callback = function () use (&$attempts) {
            ++$attempts;
            if ($attempts < 2) {
                throw new Exception('Timeout expired');
            }
            return 'completed';
        };

        $result = $this->retryService->call($callback);

        $this->assertEquals('completed', $result);
        $this->assertEquals(2, $attempts);
    }
}
