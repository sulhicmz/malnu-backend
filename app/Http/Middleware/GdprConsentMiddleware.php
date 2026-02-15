<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\UserConsent;
use Hypervel\Http\Request;
use Hypervel\Http\Response as HttpResponseInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * GdprConsentMiddleware
 *
 * Ensures users have provided required GDPR consent before accessing certain routes
 */
class GdprConsentMiddleware implements MiddlewareInterface
{
    private ContainerInterface $container;
    private RequestInterface $request;
    private HttpResponseInterface $response;

    /**
     * Routes that require specific consent types
     */
    private array $consentRequirements = [
        'data_processing' => [
            '/api/assessments',
            '/api/grades',
            '/api/school/students',
            '/api/behavioral',
            '/api/analytics',
        ],
        'analytics' => [
            '/api/analytics',
        ],
    ];

    public function __construct(
        ContainerInterface $container,
        RequestInterface $request,
        HttpResponseInterface $response
    ) {
        $this->container = $container;
        $this->request = $request;
        $this->response = $response;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Skip for non-authenticated routes
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return $handler->handle($request);
        }

        $path = $request->getUri()->getPath();

        // Check required consents for this path
        $requiredConsents = $this->getRequiredConsentsForPath($path);

        foreach ($requiredConsents as $consentType) {
            if (!UserConsent::hasActiveConsent($user->id, $consentType)) {
                return $this->response->json([
                    'success' => false,
                    'error' => [
                        'code' => 'GDPR_CONSENT_REQUIRED',
                        'message' => "User consent required: {$consentType}",
                        'consent_type' => $consentType,
                        'action_required' => 'Please review and accept the required privacy consents',
                    ],
                ])->withStatus(403);
            }
        }

        return $handler->handle($request);
    }

    /**
     * Get authenticated user from request
     */
    private function getAuthenticatedUser(ServerRequestInterface $request): ?\App\Models\User
    {
        // Get user from request attributes (set by JWT middleware)
        $user = $request->getAttribute('user');
        
        if ($user instanceof \App\Models\User) {
            return $user;
        }

        return null;
    }

    /**
     * Determine which consent types are required for a given path
     */
    private function getRequiredConsentsForPath(string $path): array
    {
        $required = [];

        foreach ($this->consentRequirements as $consentType => $routes) {
            foreach ($routes as $route) {
                if (str_starts_with($path, $route)) {
                    $required[] = $consentType;
                    break;
                }
            }
        }

        return array_unique($required);
    }

    /**
     * Check if user has given required consent
     */
    private function hasConsent(string $userId, string $consentType): bool
    {
        return UserConsent::hasActiveConsent($userId, $consentType);
    }

    /**
     * Add custom consent requirement for a route pattern
     */
    public function addConsentRequirement(string $consentType, string $routePattern): void
    {
        if (!isset($this->consentRequirements[$consentType])) {
            $this->consentRequirements[$consentType] = [];
        }
        $this->consentRequirements[$consentType][] = $routePattern;
    }
}
