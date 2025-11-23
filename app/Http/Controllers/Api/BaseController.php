<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Log\LoggerInterface;
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
        return $this->response->json($data)->withStatus($statusCode);
    }

    /**
     * Log error - to be implemented by concrete controllers
     *
     * @param array $context
     * @return void
     */
    protected function logError(array $context): void
    {
        // Extract error details for logging
        $message = $context['message'] ?? 'API Error';
        $errorCode = $context['error_code'] ?? 'GENERAL_ERROR';
        $details = $context['details'] ?? null;
        $statusCode = $context['status_code'] ?? 500;
        
        // Create a structured log entry with context
        $logContext = [
            'error_code' => $errorCode,
            'status_code' => $statusCode,
            'details' => $details,
            'request_id' => $this->getRequestId(), // Add correlation ID if available
            'timestamp' => date('c')
        ];
        
        // For now, use the response logger - in a real implementation, 
        // we would inject a proper logger service
        $logMessage = sprintf(
            '[API_ERROR] %s - Code: %s, Status: %d, Context: %s',
            $message,
            $errorCode,
            $statusCode,
            json_encode($logContext)
        );
        
        // Use error_log as fallback but with structured format
        error_log($logMessage);
        
        // In a complete implementation, we would use a proper logger service
        // $this->logger->error($message, $logContext);
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