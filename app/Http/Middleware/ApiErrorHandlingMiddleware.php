<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
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

            // Return JSON response with 500 status for server errors
            return new JsonResponse($errorResponse, 500);
        }
    }
}