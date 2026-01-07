<?php

declare(strict_types=1);

namespace App\Exceptions;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Swow\Psr7\Message\ResponsePlusInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Handle the exception, and return the specified result.
     */
    public function handle(Throwable $throwable, ResponsePlusInterface $response)
    {
        return $response->withStatus(500)
            ->withBody(new \Hyperf\HttpMessage\Stream\SwooleStream(json_encode([
                'success' => false,
                'error' => [
                    'message' => $throwable->getMessage(),
                    'code' => 'INTERNAL_SERVER_ERROR',
                ],
                'timestamp' => date('c'),
            ])))
            ->withAddedHeader('content-type', 'application/json');
    }

    /**
     * Determine if the current exception handler should handle the exception.
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
