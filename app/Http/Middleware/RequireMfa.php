<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\MfaServiceInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequireMfa implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected RequestInterface $request;

    protected MfaServiceInterface $mfaService;

    public function __construct(
        ContainerInterface $container,
        RequestInterface $request,
        MfaServiceInterface $mfaService
    ) {
        $this->container = $container;
        $this->request = $request;
        $this->mfaService = $mfaService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute('user');

        if (! $user) {
            return $this->createErrorResponse('Unauthorized', 401);
        }

        if ($this->mfaService->isMfaEnabled($user['id'])) {
            $mfaVerified = $request->getAttribute('mfa_verified', false);

            if (! $mfaVerified) {
                return $this->createErrorResponse('MFA verification required', 403);
            }
        }

        return $handler->handle($request);
    }

    private function createErrorResponse(string $message, int $statusCode): ResponseInterface
    {
        $response = $this->container->get(\Psr\Http\Message\ResponseInterface::class);
        $response = $response->withStatus($statusCode);
        $response = $response->withHeader('Content-Type', 'application/json');

        $body = json_encode([
            'success' => false,
            'message' => $message,
        ]);

        $response->getBody()->write($body);

        return $response;
    }
}
