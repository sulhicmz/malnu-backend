<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\CircuitBreakerService;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
class CircuitBreakerServiceTest extends TestCase
{
    private CircuitBreakerService $circuitBreaker;

    protected function setUp(): void
    {
        parent::setUp();
        $loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->circuitBreaker = new CircuitBreakerService($loggerMock);
    }

    public function testCallSucceedsWhenCircuitIsClosed()
    {
        $result = $this->circuitBreaker->call('test-service', function () {
            return 'success';
        });

        $this->assertEquals('success', $result);
    }

    public function testCallReturnsFallbackWhenCircuitIsOpen()
    {
        for ($i = 0; $i < 10; ++$i) {
            try {
                $this->circuitBreaker->call('failing-service', function () {
                    throw new Exception('Service unavailable');
                });
            } catch (Exception $e) {
            }
        }

        $result = $this->circuitBreaker->call('failing-service', function () {
            throw new Exception('Should not be called');
        }, function () {
            return 'fallback_result';
        });

        $this->assertEquals('fallback_result', $result);
    }

    public function testCallOpensCircuitAfterFailureThreshold()
    {
        $failureThreshold = 5;

        for ($i = 0; $i < $failureThreshold; ++$i) {
            try {
                $this->circuitBreaker->call('threshold-test', function () {
                    throw new Exception('Failure');
                });
            } catch (Exception $e) {
            }
        }

        $status = $this->circuitBreaker->getStatus('threshold-test');
        $this->assertEquals('OPEN', $status);
    }

    public function testCallTransitionsToHalfOpenAfterRecoveryTimeout()
    {
        for ($i = 0; $i < 5; ++$i) {
            try {
                $this->circuitBreaker->call('recovery-test', function () {
                    throw new Exception('Failure');
                });
            } catch (Exception $e) {
            }
        }

        $reflection = new ReflectionClass($this->circuitBreaker);
        $statesProperty = $reflection->getProperty('states');
        $statesProperty->setAccessible(true);
        $states = $statesProperty->getValue($this->circuitBreaker);
        $states['recovery-test']['opens_at'] = time() - 120;
        $statesProperty->setValue($this->circuitBreaker, $states);

        $status = $this->circuitBreaker->getStatus('recovery-test');
        $this->assertEquals('HALF_OPEN', $status);
    }

    public function testCallTransitionsToClosedAfterSuccessfulHalfOpen()
    {
        for ($i = 0; $i < 5; ++$i) {
            try {
                $this->circuitBreaker->call('close-test', function () {
                    throw new Exception('Failure');
                });
            } catch (Exception $e) {
            }
        }

        $reflection = new ReflectionClass($this->circuitBreaker);
        $statesProperty = $reflection->getProperty('states');
        $statesProperty->setAccessible(true);
        $states = $statesProperty->getValue($this->circuitBreaker);
        $states['close-test']['opens_at'] = time() - 120;
        $statesProperty->setValue($this->circuitBreaker, $states);

        $this->circuitBreaker->getState('close-test');

        $result = $this->circuitBreaker->call('close-test', function () {
            return 'success';
        });

        $this->assertEquals('success', $result);
        $this->assertEquals('CLOSED', $this->circuitBreaker->getStatus('close-test'));
    }

    public function testCallTransitionsBackToOpenOnHalfOpenFailure()
    {
        for ($i = 0; $i < 5; ++$i) {
            try {
                $this->circuitBreaker->call('open-test', function () {
                    throw new Exception('Failure');
                });
            } catch (Exception $e) {
            }
        }

        $reflection = new ReflectionClass($this->circuitBreaker);
        $statesProperty = $reflection->getProperty('states');
        $statesProperty->setAccessible(true);
        $states = $statesProperty->getValue($this->circuitBreaker);
        $states['open-test']['opens_at'] = time() - 120;
        $statesProperty->setValue($this->circuitBreaker, $states);

        $this->circuitBreaker->getState('open-test');

        try {
            $this->circuitBreaker->call('open-test', function () {
                throw new Exception('Half-open failure');
            });
        } catch (Exception $e) {
        }

        $this->assertEquals('OPEN', $this->circuitBreaker->getStatus('open-test'));
    }

    public function testGetStateReturnsCorrectState()
    {
        $state = $this->circuitBreaker->getState('new-service');

        $this->assertEquals('CLOSED', $state['status']);
        $this->assertEquals(0, $state['failures']);
        $this->assertNull($state['opens_at']);
    }

    public function testResetClearsCircuitState()
    {
        for ($i = 0; $i < 5; ++$i) {
            try {
                $this->circuitBreaker->call('reset-test', function () {
                    throw new Exception('Failure');
                });
            } catch (Exception $e) {
            }
        }

        $this->assertEquals('OPEN', $this->circuitBreaker->getStatus('reset-test'));

        $this->circuitBreaker->reset('reset-test');

        $state = $this->circuitBreaker->getState('reset-test');
        $this->assertEquals('CLOSED', $state['status']);
        $this->assertEquals(0, $state['failures']);
    }

    public function testGetStatusReturnsStatusString()
    {
        $status = $this->circuitBreaker->getStatus('status-test');
        $this->assertEquals('CLOSED', $status);
    }

    public function testMultipleServicesIndependentStates()
    {
        $this->circuitBreaker->call('service-a', function () {
            return 'a';
        });

        for ($i = 0; $i < 10; ++$i) {
            try {
                $this->circuitBreaker->call('service-b', function () {
                    throw new Exception('Failure');
                });
            } catch (Exception $e) {
            }
        }

        $this->assertEquals('CLOSED', $this->circuitBreaker->getStatus('service-a'));
        $this->assertEquals('OPEN', $this->circuitBreaker->getStatus('service-b'));
    }
}
