<?php

namespace App\Http\Middleware;

use Closure;
use Throwable;

class ApiErrorHandlingMiddleware
{
    public function process($request, Closure $next)
    {
        try {
            $response = $next($request);
            
            // If the response is already in the correct format, return it
            return $response;
        } catch (Throwable $throwable) {
            // Log the error
            error_log('API Error: ' . $throwable->getMessage());
            
            // Create standardized error response
            $errorResponse = [
                'success' => false,
                'error' => [
                    'message' => 'An internal server error occurred',
                    'code' => 'SERVER_ERROR',
                    'details' => null,
                ],
                'timestamp' => date('c'),
            ];

            // Instead of using exit, return a response using output buffering
            // Capture the JSON response and return it properly
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json');
            }
            
            // For Hyperf/Swoole, we need to return a proper response object
            // Since we don't have direct access to the response classes, 
            // we'll return the JSON string which the framework will handle
            return json_encode($errorResponse);
        }
    }
}