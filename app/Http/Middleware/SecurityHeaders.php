<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SecurityHeaders implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected ConfigInterface $config;

    protected HttpResponse $response;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
        $this->response = $container->get(HttpResponse::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        // Only add security headers if they're enabled in config
        if ($this->config->get('security.enabled', true)) {
            $response = $this->addSecurityHeaders($response);
        }

        return $response;
    }

    private function addSecurityHeaders(ResponseInterface $response): ResponseInterface
    {
        // Content Security Policy
        if ($this->config->get('security.csp.enabled', true)) {
            $csp = $this->buildCspHeader();

            $headerName = $this->config->get('security.csp.report_only') ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy';
            $response = $response->withHeader($headerName, $csp);
        }

        // HTTP Strict Transport Security
        if ($this->config->get('security.hsts.enabled', true)) {
            $hstsDirectives = [
                'max-age=' . $this->config->get('security.hsts.max_age', 31536000),
            ];

            if ($this->config->get('security.hsts.include_sub_domains', true)) {
                $hstsDirectives[] = 'includeSubDomains';
            }

            if ($this->config->get('security.hsts.preload', true)) {
                $hstsDirectives[] = 'preload';
            }

            $response = $response->withHeader('Strict-Transport-Security', implode('; ', $hstsDirectives));
        }

        // X-Frame-Options to prevent clickjacking
        $response = $response->withHeader('X-Frame-Options', $this->config->get('security.x_frame_options', 'DENY'));

        // X-Content-Type-Options to prevent MIME type sniffing
        $response = $response->withHeader('X-Content-Type-Options', $this->config->get('security.x_content_type_options', 'nosniff'));

        // Referrer-Policy to control referrer information
        $response = $response->withHeader('Referrer-Policy', $this->config->get('security.referrer_policy', 'strict-origin-when-cross-origin'));

        // Permissions-Policy to control browser features
        $response = $response->withHeader('Permissions-Policy', $this->config->get('security.permissions_policy', 'geolocation=(), microphone=(), camera=()'));

        // X-XSS-Protection (for older browsers that don't support CSP)
        return $response->withHeader('X-XSS-Protection', $this->config->get('security.x_xss_protection', '1; mode=block'));
    }

    private function buildCspHeader(): string
    {
        $policies = $this->config->get('security.csp.policies', []);
        $csp = '';

        foreach ($policies as $directive => $value) {
            if (! empty($value)) {
                $csp .= ($csp ? '; ' : '') . $directive . '-src ' . $value;
            }
        }

        // Add report URI if configured
        $reportUri = $this->config->get('security.csp.report_uri');
        if (! empty($reportUri)) {
            $csp .= ($csp ? '; ' : '') . 'report-uri ' . $reportUri;
        }

        return $csp;
    }
}
