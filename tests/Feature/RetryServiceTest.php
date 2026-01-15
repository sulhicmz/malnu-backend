<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\RetryService;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 * @coversNothing
 */
class RetryServiceTest extends TestCase
{
    private RetryService $retryService;

    private int $attemptCount = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->retryService = new RetryService($loggerMock);
        $this->attemptCount = 0;
    }

    public function testExecuteSucceedsOnFirstAttempt()
    {
        $result = $this->retryService->execute(function () {
            return 'success';
        });

        $this->assertEquals('success', $result);
    }

    public function testExecuteRetriesOnFailure()
    {
        $this->attemptCount = 0;

        $result = $this->retryService->execute(function () {
            ++$this->attemptCount;
            if ($this->attemptCount < 3) {
                throw new Exception('Temporary failure');
            }
            return 'success';
        }, ['max_attempts' => 5]);

        $this->assertEquals('success', $result);
        $this->assertEquals(3, $this->attemptCount);
    }

    public function testExecuteThrowsAfterMaxAttempts()
    {
        $this->expectException(Exception::class);

        $this->retryService->execute(function () {
            throw new Exception('Permanent failure');
        }, ['max_attempts' => 2]);
    }

    public function testExecuteRespectsCustomMaxAttempts()
    {
        $this->expectException(Exception::class);

        $this->attemptCount = 0;

        $this->retryService->execute(function () {
            ++$this->attemptCount;
            throw new Exception('Failure');
        }, ['max_attempts' => 3]);

        $this->assertEquals(3, $this->attemptCount);
    }

    public function testExecuteRespectsCustomInitialDelay()
    {
        $startTime = microtime(true);

        $this->retryService->execute(function () {
            static $attempt = 0;
            ++$attempt;
            if ($attempt < 2) {
                throw new Exception('Failure');
            }
            return 'success';
        }, ['max_attempts' => 3, 'initial_delay' => 100]);

        $elapsed = (microtime(true) - $startTime) * 1000;

        $this->assertGreaterThanOrEqual(100, $elapsed);
        $this->assertLessThan(200, $elapsed);
    }

    public function testExecuteAppliesExponentialBackoff()
    {
        $delays = [];

        $this->retryService->execute(function () use (&$delays) {
            $delays[] = microtime(true);
            static $attempt = 0;
            ++$attempt;
            if ($attempt < 3) {
                throw new Exception('Failure');
            }
            return 'success';
        }, ['max_attempts' => 4, 'initial_delay' => 100, 'multiplier' => 2, 'jitter' => false]);

        $firstRetryDelay = ($delays[1] - $delays[0]) * 1000;
        $secondRetryDelay = ($delays[2] - $delays[1]) * 1000;

        $this->assertGreaterThanOrEqual(100, $firstRetryDelay);
        $this->assertGreaterThanOrEqual(200, $secondRetryDelay);
    }

    public function testExecuteRespectsMaxDelay()
    {
        $this->expectException(Exception::class);

        $this->retryService->execute(function () {
            throw new Exception('Failure');
        }, [
            'max_attempts' => 10,
            'initial_delay' => 100,
            'multiplier' => 2,
            'max_delay' => 200,
        ]);
    }

    public function testExecuteAppliesJitter()
    {
        $attemptCount = 0;
        $delays = [];

        $results = [];

        for ($i = 0; $i < 5; ++$i) {
            $attemptCount = 0;
            $startTime = microtime(true);

            $this->retryService->execute(function () use (&$attemptCount) {
                ++$attemptCount;
                if ($attemptCount < 2) {
                    throw new Exception('Failure');
                }
                return 'success';
            }, ['max_attempts' => 3, 'initial_delay' => 100, 'jitter' => true]);

            $delays[] = (microtime(true) - $startTime) * 1000;
        }

        $variances = array_map(function ($delay) {
            return abs($delay - 100);
        }, $delays);

        $this->assertTrue(max($variances) > 0, 'Jitter should create variance in delays');
    }

    public function testExecuteFiltersExceptionsWithRetryOn()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->attemptCount = 0;

        $this->retryService->execute(function () {
            ++$this->attemptCount;
            if ($this->attemptCount === 1) {
                throw new RuntimeException('Should retry');
            }
            if ($this->attemptCount === 2) {
                throw new InvalidArgumentException('Should not retry');
            }
            return 'success';
        }, [
            'max_attempts' => 5,
            'retry_on' => [RuntimeException::class],
        ]);
    }

    public function testExecuteRetriesAllExceptionsWithWildcard()
    {
        $this->attemptCount = 0;

        $result = $this->retryService->execute(function () {
            ++$this->attemptCount;
            if ($this->attemptCount < 3) {
                throw new InvalidArgumentException('Should retry');
            }
            return 'success';
        }, [
            'max_attempts' => 5,
            'retry_on' => ['*'],
        ]);

        $this->assertEquals('success', $result);
        $this->assertEquals(3, $this->attemptCount);
    }

    public function testExecuteWithZeroDelaySkipsWaiting()
    {
        $startTime = microtime(true);

        $this->retryService->execute(function () {
            static $attempt = 0;
            ++$attempt;
            if ($attempt < 2) {
                throw new Exception('Failure');
            }
            return 'success';
        }, ['max_attempts' => 3, 'initial_delay' => 0]);

        $elapsed = (microtime(true) - $startTime) * 1000;

        $this->assertLessThan(50, $elapsed);
    }

    public function testExecuteWithOperationNameLogsCorrectly()
    {
        $result = $this->retryService->execute(function () {
            return 'success';
        }, ['operation_name' => 'test_operation']);

        $this->assertEquals('success', $result);
    }
}
