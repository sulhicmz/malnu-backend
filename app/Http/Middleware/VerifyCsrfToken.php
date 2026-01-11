<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Hypervel\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * API routes are excluded because this is a stateless JWT API.
     * CSRF attacks require session cookies to exploit, which are not used here.
     *
     * @var array<int, string>
     */
    protected array $except = [
        'api/*',
        'csp-report',
    ];
}
