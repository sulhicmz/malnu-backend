<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Support\Arr;
use Psr\Log\LogLevel;
use Hyperf\Di\Annotation\Inject;

class BaseController extends Controller
{
    /**
     * Standard success response format
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return mixed
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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
     */
    protected function notFoundResponse(string $message = 'Resource not found')
    {
        return $this->errorResponse($message, 'NOT_FOUND', null, 404);
    }

    /**
     * Unauthorized error response
     *
     * @param string $message
     * @return mixed
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized')
    {
        return $this->errorResponse($message, 'UNAUTHORIZED', null, 401);
    }

    /**
     * Forbidden error response
     *
     * @param string $message
     * @return mixed
     */
    protected function forbiddenResponse(string $message = 'Forbidden')
    {
        return $this->errorResponse($message, 'FORBIDDEN', null, 403);
    }

    /**
     * Server error response
     *
     * @param string $message
     * @return mixed
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
     * @return mixed
     */
    protected function buildJsonResponse(array $data, int $statusCode = 200)
    {
        // Use the response instance from the parent Controller
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
        // Use the Log facade for structured error logging
        \Hyperf\Support\Facades\Log::error('API Error', [
            'message' => $context['message'] ?? 'API Error',
            'error_code' => $context['error_code'] ?? 'GENERAL_ERROR',
            'status_code' => $context['status_code'] ?? 500,
            'details' => $context['details'] ?? null,
            'timestamp' => date('c'),
            'request_uri' => $this->request->getUri()->getPath() ?? null,
            'request_method' => $this->request->getMethod() ?? null,
            'user_agent' => $this->request->getHeaderLine('User-Agent') ?? null,
        ]);
    }
}