<?php

namespace App\Http\Middleware;

use App\Services\AuthService;

class JwtMiddleware
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function handle($request, $next)
    {
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorizedResponse('Authorization token required');
        }

        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix

        $user = $this->authService->getUserFromToken($token);

        if (!$user) {
            return $this->unauthorizedResponse('Invalid or expired token');
        }

        // Add user to request for use in controllers
        $request = $request->withAttribute('user', $user);

        return $next($request);
    }
    
    private function unauthorizedResponse($message)
    {
        $response = new \Hyperf\HttpMessage\Server\Response();
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json')
            ->withBody(new \Hyperf\HttpMessage\Stream\SwooleStream(json_encode([
                'success' => false,
                'error' => [
                    'message' => $message,
                    'code' => 'UNAUTHORIZED'
                ],
                'timestamp' => date('c')
            ])));
    }
}