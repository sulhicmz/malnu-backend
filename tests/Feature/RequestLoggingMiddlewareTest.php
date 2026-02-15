<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\RequestLoggingMiddleware;
use Hypervel\Contracts\Config\Repository;
use Hypervel\Foundation\Application;
use Hypervel\Contracts\Config\Repository;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @internal
 * @coversNothing
 */
class RequestLoggingMiddlewareTest extends TestCase
{
    private TestHandler $logHandler;

    private Logger $logger;

    private RequestLoggingMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logHandler = new TestHandler();
        $this->logger = new Logger('test', [$this->logHandler]);

        $container = $this->createMock(ContainerInterface::class);

        $config = new Config([
            'logging' => [
                'request' => [
                    'enabled' => true,
                    'level' => 'info',
                    'include_body' => false,
                    'exclude_paths' => ['/health', '/metrics'],
                ],
            ],
        ]);

        $container->method('get')
            ->willReturnCallback(function ($id) use ($config) {
                if ($id === ConfigInterface::class) {
                    return $config;
                }

                if ($id === 'Psr\Log\LoggerInterface' || $id === LoggerInterface::class) {
                    return $this->logger;
                }

                return null;
            });

        ApplicationContext::setContainer($container);

        $this->middleware = new RequestLoggingMiddleware($container);
    }

    public function testLogsSuccessfulRequest(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($this->createUri('/api/test'));
        $request->method('getQueryParams')->willReturn(['param1' => 'value1']);
        $request->method('getHeaderLine')->with('User-Agent')->willReturn('TestAgent/1.0');
        $request->method('getHeaderLine')->with('Content-Type')->willReturn('application/json');
        $request->method('getParsedBody')->willReturn(null);
        $request->method('getServerParams')->willReturn(['REMOTE_ADDR' => '127.0.0.1']);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $this->middleware->process($request, $handler);

        $this->assertTrue($this->logHandler->hasInfoRecords());
        $records = $this->logHandler->getRecords();
        $this->assertEquals('HTTP request completed successfully', $records[0]['message']);
        $this->assertEquals('GET', $records[0]['context']['method']);
        $this->assertEquals('/api/test', $records[0]['context']['path']);
        $this->assertEquals(200, $records[0]['context']['status_code']);
        $this->assertArrayHasKey('duration_ms', $records[0]['context']);
    }

    public function testLogsRequestWithClientError(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getUri')->willReturn($this->createUri('/api/error'));
        $request->method('getQueryParams')->willReturn([]);
        $request->method('getHeaderLine')->willReturn('');
        $request->method('getParsedBody')->willReturn(['data' => 'test']);
        $request->method('getServerParams')->willReturn(['REMOTE_ADDR' => '192.168.1.1']);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(400);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $this->middleware->process($request, $handler);

        $this->assertTrue($this->logHandler->hasWarningRecords());
        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('client error', $records[0]['message']);
        $this->assertEquals(400, $records[0]['context']['status_code']);
    }

    public function testLogsRequestWithServerError(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($this->createUri('/api/server-error'));
        $request->method('getQueryParams')->willReturn([]);
        $request->method('getHeaderLine')->willReturn('');
        $request->method('getParsedBody')->willReturn(null);
        $request->method('getServerParams')->willReturn(['REMOTE_ADDR' => '10.0.0.1']);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(500);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $this->middleware->process($request, $handler);

        $this->assertTrue($this->logHandler->hasErrorRecords());
        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('server error', $records[0]['message']);
        $this->assertEquals(500, $records[0]['context']['status_code']);
    }

    public function testDoesNotLogExcludedPaths(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($this->createUri('/health'));
        $request->method('getQueryParams')->willReturn([]);
        $request->method('getHeaderLine')->willReturn('');
        $request->method('getParsedBody')->willReturn(null);
        $request->method('getServerParams')->willReturn(['REMOTE_ADDR' => '127.0.0.1']);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $this->middleware->process($request, $handler);

        $this->assertFalse($this->logHandler->hasRecords());
    }

    public function testRedactsSensitiveDataInQueryParams(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($this->createUri('/api/test'));
        $request->method('getQueryParams')->willReturn([
            'username' => 'test',
            'password' => 'secret123',
            'token' => 'abc123',
            'api_key' => 'xyz789',
        ]);
        $request->method('getHeaderLine')->willReturn('');
        $request->method('getParsedBody')->willReturn(null);
        $request->method('getServerParams')->willReturn(['REMOTE_ADDR' => '127.0.0.1']);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $this->middleware->process($request, $handler);

        $records = $this->logHandler->getRecords();
        $this->assertEquals('test', $records[0]['context']['query']['username']);
        $this->assertEquals('[REDACTED]', $records[0]['context']['query']['password']);
        $this->assertEquals('[REDACTED]', $records[0]['context']['query']['token']);
        $this->assertEquals('[REDACTED]', $records[0]['context']['query']['api_key']);
    }

    public function testDoesNotLogRequestBodyByDefault(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getUri')->willReturn($this->createUri('/api/create'));
        $request->method('getQueryParams')->willReturn([]);
        $request->method('getHeaderLine')->willReturn('');
        $request->method('getParsedBody')->willReturn(['sensitive' => 'data']);
        $request->method('getServerParams')->willReturn(['REMOTE_ADDR' => '127.0.0.1']);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(201);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $this->middleware->process($request, $handler);

        $records = $this->logHandler->getRecords();
        $this->assertArrayNotHasKey('body', $records[0]['context']);
    }

    public function testLogsRequestDuration(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($this->createUri('/api/timing'));
        $request->method('getQueryParams')->willReturn([]);
        $request->method('getHeaderLine')->willReturn('');
        $request->method('getParsedBody')->willReturn(null);
        $request->method('getServerParams')->willReturn(['REMOTE_ADDR' => '127.0.0.1']);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $this->middleware->process($request, $handler);

        $records = $this->logHandler->getRecords();
        $this->assertArrayHasKey('duration_ms', $records[0]['context']);
        $this->assertIsFloat($records[0]['context']['duration_ms']);
        $this->assertGreaterThan(0, $records[0]['context']['duration_ms']);
    }

    public function testLogsClientIpFromHeaders(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($this->createUri('/api/test'));
        $request->method('getQueryParams')->willReturn([]);
        $request->method('getHeaderLine')
            ->willReturnMap([
                ['X-Forwarded-For', '203.0.113.1'],
                ['User-Agent', ''],
                ['Content-Type', ''],
            ]);
        $request->method('getParsedBody')->willReturn(null);
        $request->method('getServerParams')->willReturn([]);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $this->middleware->process($request, $handler);

        $records = $this->logHandler->getRecords();
        $this->assertEquals('203.0.113.1', $records[0]['context']['ip']);
    }

    public function testLogsUserAgent(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getUri')->willReturn($this->createUri('/api/test'));
        $request->method('getQueryParams')->willReturn([]);
        $request->method('getHeaderLine')
            ->willReturnMap([
                ['User-Agent', 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36'],
                ['Content-Type', ''],
            ]);
        $request->method('getParsedBody')->willReturn(null);
        $request->method('getServerParams')->willReturn(['REMOTE_ADDR' => '127.0.0.1']);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($response);

        $this->middleware->process($request, $handler);

        $records = $this->logHandler->getRecords();
        $this->assertEquals('Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36', $records[0]['context']['user_agent']);
    }

    private function createUri(string $path)
    {
        $uri = $this->createMock(\Psr\Http\Message\UriInterface::class);
        $uri->method('getPath')->willReturn($path);

        return $uri;
    }
}
