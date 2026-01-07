<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ErrorCode;
use App\Http\Controllers\Controller;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class BaseController extends Controller
{
    protected LoggerInterface $logger;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response);
        $this->logger = $container->get(LoggerInterface::class);
    }

    /**
     * Standard success response format.
     *
     * @param mixed $data
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

        return $this->buildJsonResponse($response, $statusCode);
    }

    /**
     * Standard error response format.
     *
     * @return mixed
     */
    protected function errorResponse(string $message, ?string $errorCode = null, ?array $details = null, int $statusCode = 400)
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

        // Log the error with context
        $this->logError([
            'message' => $message,
            'error_code' => $errorCode,
            'details' => $details,
            'status_code' => $statusCode,
            'request_uri' => $this->request->getUri()->getPath(),
            'request_method' => $this->request->getMethod(),
            'user_agent' => $this->request->getHeaderLine('User-Agent'),
            'ip_address' => $this->request->getHeaderLine('X-Real-IP') ?: $this->request->getServerParams()['remote_addr'] ?? null,
        ]);

        return $this->buildJsonResponse($response, $statusCode);
    }

    /**
     * Validation error response format.
     *
     * @return mixed
     */
    protected function validationErrorResponse(array $errors)
    {
        return $this->errorResponse(
            'Validation failed',
            ErrorCode::VALIDATION_ERROR,
            $errors,
            ErrorCode::getStatusCode(ErrorCode::VALIDATION_ERROR)
        );
    }

    /**
     * Not found error response.
     *
     * @return mixed
     */
    protected function notFoundResponse(string $message = 'Resource not found')
    {
        return $this->errorResponse($message, ErrorCode::NOT_FOUND, null, ErrorCode::getStatusCode(ErrorCode::NOT_FOUND));
    }

    /**
     * Unauthorized error response.
     *
     * @return mixed
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized')
    {
        return $this->errorResponse($message, ErrorCode::UNAUTHORIZED, null, ErrorCode::getStatusCode(ErrorCode::UNAUTHORIZED));
    }

    /**
     * Forbidden error response.
     *
     * @return mixed
     */
    protected function forbiddenResponse(string $message = 'Forbidden')
    {
        return $this->errorResponse($message, ErrorCode::FORBIDDEN, null, ErrorCode::getStatusCode(ErrorCode::FORBIDDEN));
    }

    /**
     * Server error response.
     *
     * @return mixed
     */
    protected function serverErrorResponse(string $message = 'Internal server error')
    {
        return $this->errorResponse($message, ErrorCode::SERVER_ERROR, null, ErrorCode::getStatusCode(ErrorCode::SERVER_ERROR));
    }

    /**
     * Build JSON response with proper structure and status code.
     *
     * @return mixed
     */
    protected function buildJsonResponse(array $data, int $statusCode = 200)
    {
        return $this->response->json($data)->withStatus($statusCode);
    }

    /**
     * Log error with structured context information.
     */
    protected function logError(array $context): void
    {
        $level = $this->getLogLevel($context['status_code'] ?? 500);

        $this->logger->log(
            $level,
            $context['message'] ?? 'API Error occurred',
            [
                'error_code' => $context['error_code'] ?? null,
                'details' => $context['details'] ?? null,
                'status_code' => $context['status_code'] ?? 500,
                'request_uri' => $context['request_uri'] ?? null,
                'request_method' => $context['request_method'] ?? null,
                'user_agent' => $context['user_agent'] ?? null,
                'ip_address' => $context['ip_address'] ?? null,
                'timestamp' => date('c'),
            ]
        );
    }

    /**
     * Determine log level based on HTTP status code.
     */
    private function getLogLevel(int $statusCode): string
    {
        if ($statusCode >= 500) {
            return 'error';
        }
        if ($statusCode >= 400) {
            return 'warning';
        }
        return 'info';
    }
}
