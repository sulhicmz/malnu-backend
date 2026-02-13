<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Services\LoggingService;
use Hyperf\Foundation\Exceptions\Handler as ExceptionHandler;
use Hyperf\Http\Request;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use Throwable;

/**
 * Centralized Exception Handler for the application.
 *
 * Provides:
 * - Structured error logging via LoggingService
 * - Consistent API error response format
 * - Exception-to-HTTP status code mapping
 * - Request context enrichment for debugging
 */
class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected array $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * A list of exception types that have custom status code methods.
     *
     * @var array<class-string<Throwable>>
     */
    protected array $customExceptions = [
        ValidationException::class,
        NotFoundException::class,
        AuthenticationException::class,
        BusinessLogicException::class,
        DatabaseException::class,
    ];

    private LoggingService $loggingService;

    private ResponseInterface $response;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->loggingService = $container->get(LoggingService::class);
        $this->response = $container->get(ResponseInterface::class);
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // return json when path start with `api`
        $this->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return str_starts_with($path = $request->path(), 'api')
                && (strlen($path) === 3 || $path[3] === '/');
        });

        $this->reportable(function (Throwable $e) {
            $this->logException($e);
        });
    }

    /**
     * Handle the exception and return a response.
     *
     * @param Request $request
     * @param Throwable $e
     * @return mixed
     */
    public function render($request, Throwable $e)
    {
        // Check if this is an API request
        if ($this->isApiRequest($request)) {
            return $this->renderApiError($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Check if the request is an API request.
     */
    private function isApiRequest(Request $request): bool
    {
        $path = $request->path();

        return str_starts_with($path, 'api')
            && (strlen($path) === 3 || $path[3] === '/');
    }

    /**
     * Render an API error response.
     */
    private function renderApiError(Request $request, Throwable $e): mixed
    {
        $statusCode = $this->getStatusCode($e);
        $errorCode = $this->getErrorCode($e);
        $message = $this->getErrorMessage($e, $statusCode);

        $errorResponse = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $errorCode,
                'details' => $this->getErrorDetails($e),
            ],
            'timestamp' => date('c'),
        ];

        // Add debug information in non-production environments
        if ($this->shouldIncludeDebugInfo()) {
            $errorResponse['debug'] = [
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ];
        }

        return $this->response->json($errorResponse)->withStatus($statusCode);
    }

    /**
     * Get HTTP status code for the exception.
     */
    private function getStatusCode(Throwable $e): int
    {
        if (method_exists($e, 'getStatusCode')) {
            return $e->getStatusCode();
        }

        return 500;
    }

    /**
     * Get error code for the exception.
     */
    private function getErrorCode(Throwable $e): string
    {
        if (method_exists($e, 'getErrorCode')) {
            return $e->getErrorCode();
        }

        return 'SERVER_ERROR';
    }

    /**
     * Get error message for the exception.
     */
    private function getErrorMessage(Throwable $e, int $statusCode): string
    {
        // For server errors, don't expose internal details
        if ($statusCode >= 500) {
            return 'An internal server error occurred';
        }

        return $e->getMessage() ?: 'An error occurred';
    }

    /**
     * Get error details from the exception.
     */
    private function getErrorDetails(Throwable $e): ?array
    {
        if (method_exists($e, 'getErrors')) {
            return $e->getErrors();
        }

        return null;
    }

    /**
     * Log the exception with structured context.
     */
    private function logException(Throwable $e): void
    {
        $context = [
            'exception_class' => get_class($e),
            'exception_message' => $e->getMessage(),
            'exception_file' => $e->getFile(),
            'exception_line' => $e->getLine(),
            'status_code' => $this->getStatusCode($e),
            'error_code' => $this->getErrorCode($e),
        ];

        if ($e->getPrevious()) {
            $context['previous_exception'] = [
                'class' => get_class($e->getPrevious()),
                'message' => $e->getPrevious()->getMessage(),
            ];
        }

        $this->loggingService->exception($e, $context);
    }

    /**
     * Check if debug information should be included in error responses.
     */
    private function shouldIncludeDebugInfo(): bool
    {
        return \env('APP_DEBUG', false) && \env('APP_ENV') !== 'production';
    }
}
