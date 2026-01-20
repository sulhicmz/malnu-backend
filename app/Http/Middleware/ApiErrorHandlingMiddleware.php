<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ApiErrorHandlingMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;
    protected ResponseInterface $response;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->response = $container->get(ResponseInterface::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): PsrResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $throwable) {
            error_log('API Error: ' . $throwable->getMessage());

            $errorResponse = [
                'success' => false,
                'error' => [
                    'message' => 'An internal server error occurred',
                    'code' => 'SERVER_ERROR',
                    'details' => null,
                ],
                'timestamp' => date('c'),
            ];

            return $this->response->json($errorResponse)->withStatus(500);
        }
    }
}