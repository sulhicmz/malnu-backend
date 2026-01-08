<?php

declare(strict_types=1);

namespace App\Exceptions;

use Hyperf\Foundation\Exceptions\Handler as ExceptionHandler;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    protected array $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    protected array $dontReport = [
    ];

    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            $this->logException($e);
        });
    }

    protected function logException(Throwable $e): void
    {
        $context = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];

        $request = \Hyperf\Context\Context::get(RequestInterface::class);
        if ($request) {
            $context['request'] = [
                'method' => $request->getMethod(),
                'uri' => $request->getUri()->getPath(),
                'ip' => $request->getServerParams()['remote_addr'] ?? 'unknown',
            ];
        }

        $user = \Hyperf\Context\Context::get('user.id');
        if ($user) {
            $context['user_id'] = $user;
        }

        $level = $this->getLogLevel($e);
        $this->logger->log($level, $e->getMessage(), $context);
    }

    protected function getLogLevel(Throwable $e): string
    {
        if (method_exists($e, 'getStatusCode')) {
            $statusCode = $e->getStatusCode();
            if ($statusCode >= 500) {
                return 'error';
            }
            return 'warning';
        }

        return 'error';
    }
}
