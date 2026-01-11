<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\ErrorLoggingService;
use Hyperf\Testing\Client;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ErrorHandlingTest extends TestCase
{
    private ErrorLoggingService $errorLogger;
    private LoggerInterface $mockLogger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockLogger = Mockery::mock(LoggerInterface::class);
        $this->errorLogger = new ErrorLoggingService($this->mockLogger);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testLogError()
    {
        $this->mockLogger
            ->shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Test error message'
                    && isset($context['timestamp'])
                    && $context['type'] === 'error'
                    && isset($context['additional_data']);
            });

        $this->errorLogger->logError('Test error message', ['additional_data' => 'value']);
    }

    public function testLogWarning()
    {
        $this->mockLogger
            ->shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Test warning message'
                    && isset($context['timestamp'])
                    && $context['type'] === 'warning'
                    && isset($context['warning_data']);
            });

        $this->errorLogger->logWarning('Test warning message', ['warning_data' => 'value']);
    }

    public function testLogInfo()
    {
        $this->mockLogger
            ->shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Test info message'
                    && isset($context['timestamp'])
                    && $context['type'] === 'info'
                    && isset($context['info_data']);
            });

        $this->errorLogger->logInfo('Test info message', ['info_data' => 'value']);
    }

    public function testLogDebug()
    {
        $this->mockLogger
            ->shouldReceive('debug')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Test debug message'
                    && isset($context['timestamp'])
                    && $context['type'] === 'debug'
                    && isset($context['debug_data']);
            });

        $this->errorLogger->logDebug('Test debug message', ['debug_data' => 'value']);
    }

    public function testLogSecurityEvent()
    {
        $this->mockLogger
            ->shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, '[SECURITY]')
                    && str_contains($message, 'LOGIN_ATTEMPT')
                    && isset($context['timestamp'])
                    && $context['type'] === 'security'
                    && $context['event'] === 'LOGIN_ATTEMPT'
                    && isset($context['ip_address']);
            });

        $this->errorLogger->logSecurityEvent('LOGIN_ATTEMPT', ['ip_address' => '127.0.0.1']);
    }

    public function testLogPerformanceUnderThreshold()
    {
        $this->mockLogger
            ->shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, '[PERFORMANCE]')
                    && isset($context['timestamp'])
                    && $context['type'] === 'performance'
                    && $context['endpoint'] === 'GET /api/test'
                    && $context['duration_ms'] === 1234.0
                    && $context['status_code'] === 200;
            });

        $this->errorLogger->logPerformance('GET /api/test', 1.234, 200, ['user_id' => 'test']);
    }

    public function testLogPerformanceOverThreshold()
    {
        $this->mockLogger
            ->shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'Slow request detected')
                    && isset($context['timestamp'])
                    && $context['type'] === 'performance'
                    && $context['endpoint'] === 'GET /api/slow'
                    && $context['duration_ms'] === 3456.0;
            });

        $this->errorLogger->logPerformance('GET /api/slow', 3.456, 200, ['user_id' => 'test']);
    }

    public function testLogAudit()
    {
        $this->mockLogger
            ->shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, '[AUDIT]')
                    && str_contains($message, 'USER_CREATED')
                    && isset($context['timestamp'])
                    && $context['type'] === 'audit'
                    && $context['action'] === 'USER_CREATED'
                    && isset($context['user_id']);
            });

        $this->errorLogger->logAudit('USER_CREATED', ['user_id' => 'user-123']);
    }

    public function testErrorContextManagement()
    {
        $this->errorLogger->setErrorContext(['request_id' => 'req-123']);

        $context = $this->errorLogger->getErrorContext();

        $this->assertArrayHasKey('request_id', $context);
        $this->assertEquals('req-123', $context['request_id']);

        $this->errorLogger->setErrorContext(['session_id' => 'session-456']);

        $context = $this->errorLogger->getErrorContext();

        $this->assertArrayHasKey('request_id', $context);
        $this->assertArrayHasKey('session_id', $context);

        $this->errorLogger->clearErrorContext();

        $context = $this->errorLogger->getErrorContext();

        $this->assertEmpty($context);
    }

    public function testAllLogMethodsIncludeTimestamp()
    {
        $methods = [
            'logError',
            'logWarning',
            'logInfo',
            'logDebug',
            'logSecurityEvent',
            'logPerformance',
            'logAudit',
        ];

        foreach ($methods as $method) {
            $this->mockLogger
                ->shouldReceive($method === 'logPerformance' || $method === 'logDebug' ? 'debug' : 'info')
                ->once()
                ->withArgs(function ($message, $context) {
                    return isset($context['timestamp']);
                });

            $this->errorLogger->$method('test message', ['test_data' => 'value']);

            Mockery::close();
            $this->mockLogger = Mockery::mock(LoggerInterface::class);
            $this->errorLogger = new ErrorLoggingService($this->mockLogger);
        }
    }
}
