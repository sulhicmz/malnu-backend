<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Hypervel\Http\Request;
use Hypervel\Routing\Middleware\ThrottleRequests;

class MobileApiRateLimiter extends ThrottleRequests
{
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        // Different rate limits for different user types
        $maxAttempts = $this->getRateLimitForUser($request);
        
        return parent::handle($request, $next, $maxAttempts, $decayMinutes, $prefix);
    }

    private function getRateLimitForUser(Request $request): int
    {
        // Get user from request if authenticated
        $user = $request->user();
        
        if (!$user) {
            // Unauthenticated requests get a lower rate limit
            return 30;
        }

        // Different rate limits based on user role
        if ($user->student) {
            return 100; // Students: 100 requests per minute
        } elseif ($user->parent) {
            return 80; // Parents: 80 requests per minute
        } elseif ($user->teacher) {
            return 150; // Teachers: 150 requests per minute
        } else {
            return 200; // Admins: 200 requests per minute
        }
    }
}