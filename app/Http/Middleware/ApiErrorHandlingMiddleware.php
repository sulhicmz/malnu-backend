<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\AuthenticationException;
use App\Exceptions\BusinessLogicException;
use App\Exceptions\DatabaseException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use Hypervel\Http\Response;
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
        } catch (AuthenticationException $e) {
            error_log('Authentication Error: ' . $e->getMessage());

            $errorResponse = [
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'UNAUTHORIZED',
                    'details' => null,
                ],
                'timestamp' => date('c'),
            ];

            return $this->response->json($errorResponse)->withStatus(401);
        } catch (ValidationException $e) {
            error_log('Validation Error: ' . $e->getMessage());

            $errorResponse = [
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'VALIDATION_ERROR',
                    'details' => null,
                ],
                'timestamp' => date('c'),
            ];

            return $this->response->json($errorResponse)->withStatus(422);
        } catch (NotFoundException $e) {
            error_log('Not Found Error: ' . $e->getMessage());

            $errorResponse = [
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'NOT_FOUND',
                    'details' => null,
                ],
                'timestamp' => date('c'),
            ];

            return $this->response->json($errorResponse)->withStatus(404);
        } catch (BusinessLogicException $e) {
            error_log('Business Logic Error: ' . $e->getMessage());

            $errorResponse = [
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => 'BUSINESS_LOGIC_ERROR',
                    'details' => null,
                ],
                'timestamp' => date('c'),
            ];

            return $this->response->json($errorResponse)->withStatus(400);
        } catch (DatabaseException $e) {
            error_log('Database Error: ' . $e->getMessage());

            $errorResponse = [
                'success' => false,
                'error' => [
                    'message' => 'A database error occurred',
                    'code' => 'DATABASE_ERROR',
                    'details' => null,
                ],
                'timestamp' => date('c'),
            ];

            return $this->response->json($errorResponse)->withStatus(500);
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
