<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Psr\Http\Message\ResponseInterface;

class BaseController extends Controller
{
    /**
     * Standard success response format
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function successResponse($data = null, string $message = 'Operation successful', int $statusCode = 200)
    {
        $response = [
            'success' => true,
            'data' => $data,
            'message' => $message,
            'timestamp' => date('c'), // ISO 8601 format
        ];

        // Remove data field if null to maintain consistency
        if (is_null($data)) {
            unset($response['data']);
        }

        // This will be implemented by the actual HyperVel framework
        // The concrete implementation will handle the response formatting
        return $this->buildJsonResponse($response, $statusCode);
    }

    /**
     * Standard error response format
     *
     * @param string $message
     * @param string|null $errorCode
     * @param array|null $details
     * @param int $statusCode
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function errorResponse(string $message, string $errorCode = null, array $details = null, int $statusCode = 400)
    {
        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $errorCode ?? 'GENERAL_ERROR',
                'details' => $details,
            ],
            'timestamp' => date('c'), // ISO 8601 format
        ];

        // Remove details field if null to maintain consistency
        if (is_null($details)) {
            unset($response['error']['details']);
        }

        // Log the error - this will be implemented by the actual framework
        $this->logError([
            'message' => $message,
            'error_code' => $errorCode,
            'details' => $details,
            'status_code' => $statusCode,
        ]);

        return $this->buildJsonResponse($response, $statusCode);
    }

    /**
     * Validation error response format
     *
     * @param array $errors
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function validationErrorResponse(array $errors)
    {
        return $this->errorResponse(
            'Validation failed',
            'VALIDATION_ERROR',
            $errors,
            422
        );
    }

    /**
     * Not found error response
     *
     * @param string $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function notFoundResponse(string $message = 'Resource not found')
    {
        return $this->errorResponse($message, 'NOT_FOUND', null, 404);
    }

    /**
     * Unauthorized error response
     *
     * @param string $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized')
    {
        return $this->errorResponse($message, 'UNAUTHORIZED', null, 401);
    }

    /**
     * Forbidden error response
     *
     * @param string $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function forbiddenResponse(string $message = 'Forbidden')
    {
        return $this->errorResponse($message, 'FORBIDDEN', null, 403);
    }

    /**
     * Server error response
     *
     * @param string $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function serverErrorResponse(string $message = 'Internal server error')
    {
        return $this->errorResponse($message, 'SERVER_ERROR', null, 500);
    }

    /**
     * Build JSON response - to be implemented by concrete controllers
     *
     * @param array $data
     * @param int $statusCode
     * @return ResponseInterface
     */
    protected function buildJsonResponse(array $data, int $statusCode = 200)
    {
        // Use the base Controller's json method to return proper JSON response
        // This ensures consistent response formatting and proper HTTP status codes
        return $this->json($data, $statusCode);
    }

    /**
     * Log error - to be implemented by concrete controllers
     *
     * @param array $context
     * @return void
     */
    protected function logError(array $context): void
    {
        // Create a structured log entry with context
        $logEntry = [
            'message' => $context['message'] ?? 'API Error occurred',
            'error_code' => $context['error_code'] ?? null,
            'details' => $context['details'] ?? null,
            'status_code' => $context['status_code'] ?? null,
            'timestamp' => date('c'),
            'uri' => $this->request->getUri()->getPath() ?? null,
            'method' => $this->request->getMethod() ?? null,
            'request_id' => $this->getRequestId(), // Add correlation ID if available
        ];
        
        // Log to the application's standard logging system
        // Using a more robust logging approach
        $logMessage = '[API_ERROR] ' . json_encode($logEntry);
        error_log($logMessage);
        
        // In a production environment, you would use the proper logger service
        // This is a simplified implementation that can be enhanced later
    }
    
    /**
     * Get request ID for correlation tracking
     *
     * @return string|null
     */
    private function getRequestId(): ?string
    {
        // Try to get request ID from header or generate one if not available
        try {
            $requestId = $this->request->getHeaderLine('X-Request-ID');
            if (empty($requestId)) {
                $requestId = uniqid('req_', true);
            }
            return $requestId;
        } catch (\Throwable $e) {
            return uniqid('req_', true);
        }
    }
}