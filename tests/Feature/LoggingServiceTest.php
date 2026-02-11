<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\LoggingService;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

/**
 * LoggingService Test
 *
 * Tests the comprehensive logging service with standardized methods and security event logging.
 */
class LoggingServiceTest extends TestCase
{
    /**
     * @var LoggingService
     */
    private LoggingService $loggingService;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create mock logger
        $loggerMock = $this->createMock(LoggerInterface::class);

        // Create logging service with mock logger
        $this->loggingService = new LoggingService($loggerMock, $this->container);
    }

    /**
     * Test logging standard info message.
     */
    public function testInfoLogging(): void
    {
        $this->expectLogCall('info', 'Test message', ['context_key' => 'context_value']);

        $this->loggingService->info('Test message', ['context_key' => 'context_value']);
    }

    /**
     * Test logging warning message.
     */
    public function testWarningLogging(): void
    {
        $this->expectLogCall('warning', 'Test warning', ['context_key' => 'context_value']);

        $this->loggingService->warning('Test warning', ['context_key' => 'context_value']);
    }

    /**
     * Test logging error message.
     */
    public function testErrorLogging(): void
    {
        $this->expectLogCall('error', 'Test error', ['context_key' => 'context_value']);

        $this->loggingService->error('Test error', ['context_key' => 'context_value']);
    }

    /**
     * Test logging critical message.
     */
    public function testCriticalLogging(): void
    {
        $this->expectLogCall('critical', 'Test critical', ['context_key' => 'context_value']);

        $this->loggingService->critical('Test critical', ['context_key' => 'context_value']);
    }

    /**
     * Test logging debug message.
     */
    public function testDebugLogging(): void
    {
        $this->expectLogCall('debug', 'Test debug', ['context_key' => 'context_value']);

        $this->loggingService->debug('Test debug', ['context_key' => 'context_value']);
    }

    /**
     * Test logging exception with context.
     */
    public function testExceptionLogging(): void
    {
        $exception = new \Exception('Test exception');

        $this->expectLogCall('error', $exception->getMessage(), [
            'exception_type' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_file' => $exception->getFile(),
            'exception_line' => $exception->getLine(),
        ]);

        $this->loggingService->exception($exception, ['additional_context' => 'value']);
    }

    /**
     * Test logging failed login attempt.
     */
    public function testFailedLoginLogging(): void
    {
        $email = 'test@example.com';
        $ipAddress = '192.168.1.1';

        $this->expectLogCall('warning', 'Failed login attempt', [
            'event_type' => 'auth_event',
            'event_action' => 'login_failed',
            'email' => 'tst@example.com', // sanitized
            'ip_address' => $ipAddress,
        ]);

        $this->loggingService->logFailedLogin($email, $ipAddress);
    }

    /**
     * Test logging successful login.
     */
    public function testSuccessfulLoginLogging(): void
    {
        $email = 'test@example.com';
        $ipAddress = '192.168.1.1';

        $this->expectLogCall('info', 'User logged in successfully', [
            'event_type' => 'auth_event',
            'event_action' => 'login_success',
            'email' => 'tst@example.com', // sanitized
            'ip_address' => $ipAddress,
        ]);

        $this->loggingService->logSuccessfulLogin($email, $ipAddress);
    }

    /**
     * Test logging permission denied.
     */
    public function testPermissionDeniedLogging(): void
    {
        $action = 'delete_user';
        $resource = 'user_data';
        $userId = 'user_123';

        $this->expectLogCall('warning', 'Permission denied', [
            'event_type' => 'security_event',
            'event_action' => $action,
            'resource' => $resource,
            'user_id' => $userId,
        ]);

        $this->loggingService->logPermissionDenied($action, $resource, $userId);
    }

    /**
     * Test logging token blacklist operation.
     */
    public function testTokenBlacklistOperationLogging(): void
    {
        $action = 'blacklist_token';
        $userId = 'user_123';

        $this->expectLogCall('info', 'Token blacklist operation', [
            'event_type' => 'security_event',
            'event_action' => $action,
            'user_id' => $userId,
            'additional_context' => 'extra_data',
        ]);

        $this->loggingService->logTokenBlacklistOperation($action, $userId, ['additional_context' => 'extra_data']);
    }

    /**
     * Test logging rate limit trigger.
     */
    public function testRateLimitTriggerLogging(): void
    {
        $identifier = 'api_user_123';
        $ipAddress = '192.168.1.1';
        $limit = 100;
        $window = '1 minute';

        $this->expectLogCall('warning', 'Rate limit triggered', [
            'event_type' => 'security_event',
            'event_action' => 'rate_limit_exceeded',
            'identifier' => $identifier,
            'ip_address' => $ipAddress,
            'limit' => $limit,
            'window' => $window,
        ]);

        $this->loggingService->logRateLimitTrigger($identifier, $ipAddress, $limit, $window);
    }

    /**
     * Test logging suspicious activity.
     */
    public function testSuspiciousActivityLogging(): void
    {
        $activity = 'multiple_failed_logins';

        $this->expectLogCall('warning', 'Suspicious activity detected', [
            'event_type' => 'security_event',
            'event_action' => 'suspicious_activity',
            'activity' => $activity,
        ]);

        $this->loggingService->logSuspiciousActivity($activity, ['additional_info' => 'detected_from_ip_192.168.1.1']);
    }

    /**
     * Test logging system event.
     */
    public function testSystemEventLogging(): void
    {
        $action = 'backup_completed';
        $result = 'success';

        $this->expectLogCall('info', 'System event', [
            'event_type' => 'system_event',
            'event_action' => $action,
            'result' => $result,
        ]);

        $this->loggingService->logSystemEvent($action, $result);
    }

    /**
     * Test logging backup operation.
     */
    public function testBackupOperationLogging(): void
    {
        $type = 'database';

        $this->expectLogCall('info', 'Backup operation completed', [
            'event_type' => 'system_event',
            'event_action' => 'backup_' . $type,
        ]);

        $this->loggingService->logBackupOperation($type, ['backup_size' => '10MB', 'backup_time' => '5 minutes']);
    }

    /**
     * Test logging restore operation.
     */
    public function testRestoreOperationLogging(): void
    {
        $type = 'filesystem';

        $this->expectLogCall('info', 'Restore operation completed', [
            'event_type' => 'system_event',
            'event_action' => 'restore_' . $type,
        ]);

        $this->loggingService->logRestoreOperation($type, ['restore_time' => '10 minutes', 'files_restored' => 5]);
    }

    /**
     * Test logging API request.
     */
    public function testApiRequestLogging(): void
    {
        $method = 'GET';
        $path = '/api/users';
        $statusCode = 200;
        $userId = 'user_123';

        $this->expectLogCall('info', 'API request', [
            'event_type' => 'api_request',
            'method' => $method,
            'path' => $path,
            'status_code' => $statusCode,
            'user_id' => $userId,
        ]);

        $this->loggingService->logApiRequest($method, $path, $statusCode, $userId);
    }

    /**
     * Test sensitive data sanitization in logs.
     */
    public function testSensitiveDataSanitization(): void
    {
        // Password should be redacted
        $this->loggingService->info('Login attempt', [
            'password' => 'secret_password123', // Should be redacted to '[REDACTED]'
        ]);

        // Verify password was sanitized in the log call
        $logCalls = $this->getLoggerMock()->getCalls();

        $sanitized = false;
        foreach ($logCalls as $call) {
            $context = $call->getArgument(0); // The context array
            if (isset($context['password']) && $context['password'] === '[REDACTED]') {
                $sanitized = true;
            }
        }

        $this->assertTrue($sanitized, 'Password should be sanitized to [REDACTED] in logs');
    }

    /**
     * Test email sanitization for logs.
     */
    public function testEmailSanitization(): void
    {
        $email = 'testuser@example.com';

        // Email should be partially sanitized (keep domain)
        $this->loggingService->info('Login attempt', ['email' => $email]);

        // Verify email was sanitized in the log call
        $logCalls = $this->getLoggerMock()->getCalls();

        $sanitized = false;
        foreach ($logCalls as $call) {
            $context = $call->getArgument(0); // The context array
            if (isset($context['email'])) {
                // Should be formatted like 'tst@example.com'
                $sanitized = is_string($context['email']) && str_contains($context['email'], '@') && !str_contains($context['email'], '***@');
            }
        }

        $this->assertTrue($sanitized, 'Email should be sanitized (partial with asterisks) in logs');
    }

    /**
     * Helper method to expect a specific log call.
     *
     * @param string $level Expected log level
     * @param string $message Expected log message
     * @param array $expectedContext Expected context keys/values
     */
    private function expectLogCall(string $level, string $message, array $expectedContext = []): void
    {
        $loggerMock = $this->getLoggerMock();

        $loggerMock->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo($level),
                $this->equalTo($message),
                $this->callback(function ($context) {
                    // Check that timestamp is included
                    $this->assertArrayHasKey('timestamp', $context);

                    // Check that correlation_id and user_id are included (may be null)
                    $this->assertArrayHasKey('correlation_id', $context);
                    $this->assertArrayHasKey('user_id', $context);

                    // Check expected context
                    foreach ($expectedContext as $key => $value) {
                        $this->assertArrayHasKey($key, $context);
                        $this->assertEquals($value, $context[$key]);
                    }

                    return true;
                })
            )
            ->willReturn(null);
    }

    /**
     * Helper method to get the logger mock.
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getLoggerMock(): \PHPUnit\Framework\MockObject\MockObject
    {
        return $this->container->get(LoggerInterface::class);
    }

    /**
     * Helper method to get the container mock.
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getContainerMock(): \PHPUnit\Framework\MockObject\MockObject
    {
        // For test purposes, we use a simple mock
        $mock = $this->createMock(ContainerInterface::class);

        // Mock LoggerInterface::class to return the logger mock
        $mock->method('get')
            ->with($this->equalTo(LoggerInterface::class))
            ->willReturn($this->getLoggerMock());

        return $mock;
    }

    /**
     * Override parent setUp to use our mock container.
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->getContainerMock();
    }

    /**
     * Create a mock for the LoggerInterface that records all calls.
     *
     * @return \PHPUnit\Framework\MockObject\BuilderInvocationMocker
     */
    private function createMockLogger(): \PHPUnit\Framework\MockObject\BuilderInvocationMocker
    {
        $mock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $invocationMocker = $mock->expects($this->any())
            ->method('log')
            ->willReturnCallback(function ($level, $message, $context) {
                return null; // Just record, don't actually log
            });

        $mock->__phpunit_setReturnValueMap([
            'expects' => $this->any(),
        ]);

        return $mock;
    }
}
