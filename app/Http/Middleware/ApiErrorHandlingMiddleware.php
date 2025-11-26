<?php

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ApiErrorHandlingMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
            
            // If the response is already in the correct format, return it
            return $response;
        } catch (Throwable $throwable) {
            // Log the error
            error_log('API Error: ' . $throwable->getMessage());
            
            // Create standardized error response
            $errorResponse = json_encode([
                'success' => false,
                'error' => [
                    'message' => 'An internal server error occurred',
                    'code' => 'SERVER_ERROR',
                    'details' => null,
                ],
                'timestamp' => date('c'),
            ]);
            
            // Create response with 500 status for server errors
            $response = new \Hyperf\HttpMessage\Server\Response();
            return $response->withStatus(500)
                           ->withHeader('Content-Type', 'application/json')
                           ->withBody(\Swoole\Http\Response::createBody($errorResponse));
        }
    }
}