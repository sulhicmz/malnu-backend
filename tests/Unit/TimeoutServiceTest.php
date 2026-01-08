<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\TimeoutService;
use Exception;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TimeoutServiceTest extends TestCase
{
    private TimeoutService $timeoutService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->timeoutService = new TimeoutService(defaultTimeout: 30);
    }

    public function testSuccessfulCallExecutesWithoutTimeout()
    {
        $callback = function () {
            return 'success';
        };

        $result = $this->timeoutService->call($callback);

        $this->assertEquals('success', $result);
    }

    public function testFastOperationCompletesSuccessfully()
    {
        $callback = function () {
            usleep(10000);
            return 'fast_result';
        };

        $result = $this->timeoutService->call($callback, timeoutMs: 100);

        $this->assertEquals('fast_result', $result);
    }

    public function testOperationEndingAfterTimeoutThresholdThrowsException()
    {
        $callback = function () {
            usleep(50000);
            return 'should_not_return';
        };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Operation timed out');

        $this->timeoutService->call($callback, timeoutMs: 10);
    }

    public function testCustomTimeoutMsOverridesDefault()
    {
        $callback = function () {
            return 'quick';
        };

        $result = $this->timeoutService->call($callback, timeoutMs: 1000);

        $this->assertEquals('quick', $result);
    }

    public function testExceptionInCallbackIsPropagated()
    {
        $callback = function () {
            throw new Exception('Callback error');
        };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Callback error');

        $this->timeoutService->call($callback);
    }

    public function testOnTimeoutCallbackIsInvokedOnTimeout()
    {
        $timeoutInvoked = false;
        $elapsedTime = 0;

        $callback = function () {
            throw new Exception('Timeout occurred');
        };

        $onTimeout = function ($elapsed, $exception) use (&$timeoutInvoked, &$elapsedTime) {
            $timeoutInvoked = true;
            $elapsedTime = $elapsed;
            return 'fallback_value';
        };

        $result = $this->timeoutService->call($callback, timeoutMs: 10, onTimeout: $onTimeout);

        $this->assertTrue($timeoutInvoked);
        $this->assertEquals('fallback_value', $result);
        $this->assertGreaterThanOrEqual(0, $elapsedTime);
    }

    public function testOnTimeoutExceptionPreventsFallbackThrow()
    {
        $callback = function () {
            throw new Exception('Timeout error');
        };

        $onTimeout = function ($elapsed, $exception) {
            throw new Exception('Custom timeout exception');
        };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Custom timeout exception');

        $this->timeoutService->call($callback, timeoutMs: 10, onTimeout: $onTimeout);
    }

    public function testOperationNearingTimeoutLogsWarning()
    {
        $callback = function () {
            return 'success';
        };

        $result = $this->timeoutService->call($callback, timeoutMs: 100);

        $this->assertEquals('success', $result);
    }

    public function testDefaultTimeoutFromConstructor()
    {
        $callback = function () {
            return 'success';
        };

        $result = $this->timeoutService->call($callback);

        $this->assertEquals('success', $result);
    }

    public function testSetTimeoutChangesDefaultTimeout()
    {
        $this->timeoutService->setTimeout(60);

        $callback = function () {
            return 'success';
        };

        $result = $this->timeoutService->call($callback);

        $this->assertEquals('success', $result);
    }

    public function testCallWithFallbackUsesFallbackOnException()
    {
        $callback = function () {
            throw new Exception('Service error');
        };

        $fallback = function ($exception) {
            return 'cached_response';
        };

        $result = $this->timeoutService->callWithFallback($callback, $fallback);

        $this->assertEquals('cached_response', $result);
    }

    public function testCallWithFallbackUsesFallbackOnTimeout()
    {
        $callback = function () {
            throw new Exception('Operation timed out after 15ms');
        };

        $fallback = function ($exception) {
            return 'timeout_fallback';
        };

        $result = $this->timeoutService->callWithFallback($callback, $fallback, timeoutMs: 10);

        $this->assertEquals('timeout_fallback', $result);
    }

    public function testCallWithFallbackIgnoresFallbackOnSuccess()
    {
        $callback = function () {
            return 'live_response';
        };

        $fallback = function () {
            return 'cached_response';
        };

        $result = $this->timeoutService->callWithFallback($callback, $fallback);

        $this->assertEquals('live_response', $result);
    }

    public function testElapsedTimeIsTrackedAccurately()
    {
        $elapsedCaptured = false;
        $elapsedTime = 0;

        $callback = function () {
            usleep(20000);
            return 'measured';
        };

        $onTimeout = function ($elapsed, $exception) use (&$elapsedCaptured, &$elapsedTime) {
            $elapsedCaptured = true;
            $elapsedTime = $elapsed;
        };

        $result = $this->timeoutService->call($callback, timeoutMs: 50, onTimeout: $onTimeout);

        $this->assertEquals('measured', $result);
    }

    public function testMultipleCallsAreIndependent()
    {
        $results = [];

        for ($i = 0; $i < 3; ++$i) {
            $results[] = $this->timeoutService->call(function () use ($i) {
                return "result_{$i}";
            });
        }

        $this->assertEquals(['result_0', 'result_1', 'result_2'], $results);
    }

    public function testZeroTimeoutAllowsInstantCompletion()
    {
        $callback = function () {
            return 'instant';
        };

        $result = $this->timeoutService->call($callback, timeoutMs: 0);

        $this->assertEquals('instant', $result);
    }

    public function testNegativeTimeoutAllowsInstantCompletion()
    {
        $callback = function () {
            return 'instant';
        };

        $result = $this->timeoutService->call($callback, timeoutMs: -1);

        $this->assertEquals('instant', $result);
    }

    public function testExceptionMessageIncludesElapsedTime()
    {
        $callback = function () {
            throw new Exception('Operation timed out after 50ms');
        };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Operation timed out');

        $this->timeoutService->call($callback, timeoutMs: 10);
    }

    public function testFallbackReceivesException()
    {
        $exceptionReceived = null;

        $callback = function () {
            throw new Exception('Service error');
        };

        $fallback = function ($exception) use (&$exceptionReceived) {
            $exceptionReceived = $exception;
            return 'fallback';
        };

        $this->timeoutService->callWithFallback($callback, $fallback);

        $this->assertInstanceOf(Exception::class, $exceptionReceived);
        $this->assertEquals('Service error', $exceptionReceived->getMessage());
    }

    public function testFallbackExceptionIsNotCaught()
    {
        $callback = function () {
            throw new Exception('Callback error');
        };

        $fallback = function ($exception) {
            throw new Exception('Fallback error');
        };

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Fallback error');

        $this->timeoutService->callWithFallback($callback, $fallback);
    }

    public function testSuccessfulFastOperationReturnsImmediately()
    {
        $startTime = microtime(true);

        $callback = function () {
            return 'fast';
        };

        $result = $this->timeoutService->call($callback, timeoutMs: 10000);

        $elapsed = (microtime(true) - $startTime) * 1000;

        $this->assertEquals('fast', $result);
        $this->assertLessThan(1000, $elapsed);
    }
}
