<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CircuitBreaker;
use Exception;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CircuitBreakerTest extends TestCase
{
    private string $serviceName = 'test_service';

    private CircuitBreaker $circuitBreaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->circuitBreaker = new CircuitBreaker(
            $this->serviceName,
            failureThreshold: 3,
            recoveryTimeout: 60,
            successThreshold: 2
        );
        $this->circuitBreaker->reset();
    }

    protected function tearDown(): void
    {
        $this->circuitBreaker->reset();
        parent::tearDown();
    }

    public function testInitialStateIsClosed()
    {
        $state = $this->circuitBreaker->getState();

        $this->assertEquals(CircuitBreaker::STATE_CLOSED, $state);
        $this->assertEquals(0, $this->circuitBreaker->getFailureCount());
        $this->assertEquals(0, $this->circuitBreaker->getSuccessCount());
    }

    public function testSuccessfulCallIncreasesSuccessCount()
    {
        $callback = fn () => 'success';

        $result = $this->circuitBreaker->call($callback);

        $this->assertEquals('success', $result);
        $this->assertEquals(1, $this->circuitBreaker->getSuccessCount());
        $this->assertEquals(0, $this->circuitBreaker->getFailureCount());
        $this->assertEquals(CircuitBreaker::STATE_CLOSED, $this->circuitBreaker->getState());
    }

    public function testFailedCallIncreasesFailureCountAndStaysClosedUntilThreshold()
    {
        $callback = fn () => throw new Exception('Service error');

        try {
            $this->circuitBreaker->call($callback);
        } catch (Exception $e) {
            $this->assertEquals('Service error', $e->getMessage());
        }

        $this->assertEquals(1, $this->circuitBreaker->getFailureCount());
        $this->assertEquals(0, $this->circuitBreaker->getSuccessCount());
        $this->assertEquals(CircuitBreaker::STATE_CLOSED, $this->circuitBreaker->getState());
    }

    public function testCircuitOpensAfterFailureThresholdReached()
    {
        $callback = fn () => throw new Exception('Service error');

        for ($i = 0; $i < 3; ++$i) {
            try {
                $this->circuitBreaker->call($callback);
            } catch (Exception) {
                // Expected to fail
            }
        }

        $this->assertEquals(3, $this->circuitBreaker->getFailureCount());
        $this->assertEquals(CircuitBreaker::STATE_OPEN, $this->circuitBreaker->getState());
    }

    public function testOpenCircuitRejectsRequests()
    {
        $callback = fn () => 'should not execute';

        $this->forceCircuitOpen();

        try {
            $this->circuitBreaker->call($callback);
            $this->fail('Expected exception for open circuit');
        } catch (Exception $e) {
            $this->assertStringContainsString('circuit breaker', strtolower($e->getMessage()));
        }

        $this->assertEquals(3, $this->circuitBreaker->getFailureCount());
    }

    public function testOpenCircuitReturnsFallbackWhenProvided()
    {
        $callback = fn () => 'should not execute';
        $fallback = fn () => 'fallback_value';

        $this->forceCircuitOpen();

        $result = $this->circuitBreaker->call($callback, $fallback);

        $this->assertEquals('fallback_value', $result);
        $this->assertEquals(CircuitBreaker::STATE_OPEN, $this->circuitBreaker->getState());
    }

    public function testFailedCallInOpenStateUsesFallback()
    {
        $callback = fn () => 'should not execute';
        $fallback = fn () => 'fallback_value';

        $this->forceCircuitOpen();

        $result = $this->circuitBreaker->call($callback, $fallback);

        $this->assertEquals('fallback_value', $result);
    }

    public function testCircuitTransitionsToHalfOpenAfterRecoveryTimeout()
    {
        $this->forceCircuitOpen();

        $circuitBreaker = new CircuitBreaker(
            $this->serviceName,
            failureThreshold: 3,
            recoveryTimeout: 0,
            successThreshold: 2
        );

        $this->assertEquals(CircuitBreaker::STATE_HALF_OPEN, $circuitBreaker->getState());
    }

    public function testHalfOpenAllowsOneRequestToTestService()
    {
        $this->forceCircuitOpen();

        $circuitBreaker = new CircuitBreaker(
            $this->serviceName,
            failureThreshold: 3,
            recoveryTimeout: 0,
            successThreshold: 2
        );

        $result = $circuitBreaker->call(fn () => 'success');

        $this->assertEquals('success', $result);
        $this->assertEquals(CircuitBreaker::STATE_HALF_OPEN, $circuitBreaker->getState());
        $this->assertEquals(1, $circuitBreaker->getSuccessCount());
    }

    public function testHalfOpenFailureReopensCircuit()
    {
        $this->forceCircuitOpen();

        $circuitBreaker = new CircuitBreaker(
            $this->serviceName,
            failureThreshold: 3,
            recoveryTimeout: 0,
            successThreshold: 2
        );

        try {
            $circuitBreaker->call(fn () => throw new Exception('Service error'));
        } catch (Exception) {
            // Expected
        }

        $this->assertEquals(CircuitBreaker::STATE_OPEN, $circuitBreaker->getState());
        $this->assertEquals(4, $circuitBreaker->getFailureCount());
    }

    public function testHalfOpensTransitionsToClosedAfterSuccessThreshold()
    {
        $this->forceCircuitOpen();

        $circuitBreaker = new CircuitBreaker(
            $this->serviceName,
            failureThreshold: 3,
            recoveryTimeout: 0,
            successThreshold: 2
        );

        $circuitBreaker->call(fn () => 'success');
        $circuitBreaker->call(fn () => 'success');

        $this->assertEquals(CircuitBreaker::STATE_CLOSED, $circuitBreaker->getState());
        $this->assertEquals(0, $circuitBreaker->getFailureCount());
        $this->assertEquals(2, $circuitBreaker->getSuccessCount());
    }

    public function testResetClearsStateAndCounts()
    {
        $this->forceCircuitOpen();

        $this->circuitBreaker->reset();

        $this->assertEquals(CircuitBreaker::STATE_CLOSED, $this->circuitBreaker->getState());
        $this->assertEquals(0, $this->circuitBreaker->getFailureCount());
        $this->assertEquals(0, $this->circuitBreaker->getSuccessCount());
        $this->assertEquals(0, $this->circuitBreaker->getLastFailureTime());
    }

    public function testGetFailureCountReturnsCurrentCount()
    {
        $callback = fn () => throw new Exception('Error');

        for ($i = 0; $i < 2; ++$i) {
            try {
                $this->circuitBreaker->call($callback);
            } catch (Exception) {
            }
        }

        $this->assertEquals(2, $this->circuitBreaker->getFailureCount());
    }

    public function testGetSuccessCountReturnsCurrentCount()
    {
        $this->circuitBreaker->call(fn () => 'success1');
        $this->circuitBreaker->call(fn () => 'success2');

        $this->assertEquals(2, $this->circuitBreaker->getSuccessCount());
    }

    public function testGetLastFailureTimeReturnsTimestamp()
    {
        $beforeFailure = time();

        try {
            $this->circuitBreaker->call(fn () => throw new Exception('Error'));
        } catch (Exception) {
        }

        $lastFailureTime = $this->circuitBreaker->getLastFailureTime();

        $this->assertGreaterThanOrEqual($beforeFailure, $lastFailureTime);
        $this->assertLessThanOrEqual(time(), $lastFailureTime);
    }

    public function testCustomFailureThresholdWorks()
    {
        $customCircuit = new CircuitBreaker(
            'custom_service',
            failureThreshold: 5,
            recoveryTimeout: 60,
            successThreshold: 2
        );

        $callback = fn () => throw new Exception('Error');

        for ($i = 0; $i < 4; ++$i) {
            try {
                $customCircuit->call($callback);
            } catch (Exception) {
            }
        }

        $this->assertEquals(CircuitBreaker::STATE_CLOSED, $customCircuit->getState());
        $this->assertEquals(4, $customCircuit->getFailureCount());

        try {
            $customCircuit->call($callback);
        } catch (Exception) {
        }

        $this->assertEquals(CircuitBreaker::STATE_OPEN, $customCircuit->getState());
    }

    public function testCustomSuccessThresholdWorks()
    {
        $customCircuit = new CircuitBreaker(
            'custom_service_2',
            failureThreshold: 3,
            recoveryTimeout: 0,
            successThreshold: 3
        );

        $this->forceCircuitOpen($customCircuit);

        $customCircuit->call(fn () => 'success1');
        $this->assertEquals(CircuitBreaker::STATE_HALF_OPEN, $customCircuit->getState());

        $customCircuit->call(fn () => 'success2');
        $this->assertEquals(CircuitBreaker::STATE_HALF_OPEN, $customCircuit->getState());

        $customCircuit->call(fn () => 'success3');
        $this->assertEquals(CircuitBreaker::STATE_CLOSED, $customCircuit->getState());
    }

    public function testExceptionInCallWithFallbackReturnsFallbackResult()
    {
        $callback = fn () => throw new Exception('Service error');
        $fallback = fn () => 'degraded_response';

        $result = $this->circuitBreaker->call($callback, $fallback);

        $this->assertEquals('degraded_response', $result);
    }

    public function testSuccessfulCallWithFallbackIgnoresFallback()
    {
        $callback = fn () => 'success';
        $fallback = fn () => 'fallback';

        $result = $this->circuitBreaker->call($callback, $fallback);

        $this->assertEquals('success', $result);
    }

    private function forceCircuitOpen(?CircuitBreaker $circuitBreaker = null): void
    {
        $cb = $circuitBreaker ?? $this->circuitBreaker;
        $callback = fn () => throw new Exception('Forced failure');

        for ($i = 0; $i < 3; ++$i) {
            try {
                $cb->call($callback);
            } catch (Exception) {
            }
        }

        $this->assertEquals(CircuitBreaker::STATE_OPEN, $cb->getState());
    }
}
