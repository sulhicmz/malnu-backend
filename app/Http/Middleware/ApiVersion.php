<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Hypervel\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiVersion
{
    /**
     * Handle an incoming request for API versioning.
     *
     * @param  \Hypervel\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Add version information to the request
        $request->headers->set('X-API-Version', 'v1');
        
        $response = $next($request);
        
        // Add version header to response
        $response->headers->set('X-API-Version', 'v1');
        
        return $response;
    }
}