<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Utils\Str;

class InputSanitizer implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get the request data
        $parsedBody = $request->getParsedBody();
        $queryParams = $request->getQueryParams();

        // Sanitize the parsed body if it's an array
        if (is_array($parsedBody)) {
            $parsedBody = $this->sanitizeArray($parsedBody);
        }

        // Sanitize query parameters if it's an array
        if (is_array($queryParams)) {
            $queryParams = $this->sanitizeArray($queryParams);
        }

        // Update the request with sanitized data
        $request = $request->withParsedBody($parsedBody);
        $request = $request->withQueryParams($queryParams);

        return $handler->handle($request);
    }

    /**
     * Recursively sanitize an array of input data
     */
    private function sanitizeArray(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Recursively sanitize nested arrays
                $sanitized[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                // Sanitize string values
                $sanitized[$key] = $this->sanitizeString($value);
            } else {
                // Keep non-string, non-array values as they are
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize a string value
     */
    private function sanitizeString(string $value): string
    {
        // Remove null bytes
        $value = str_replace("\0", '', $value);
        
        // Remove escape characters that could be used for injection
        $value = str_replace("\\", '', $value);
        
        // Basic XSS prevention - remove common script tags
        $value = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $value);
        $value = preg_replace('/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi', '', $value);
        
        // Remove javascript: and vbscript: protocols
        $value = preg_replace('/javascript:/i', 'javascript&#58;', $value);
        $value = preg_replace('/vbscript:/i', 'vbscript&#58;', $value);
        
        // Remove data: and file: protocols (potential XSS vectors)
        $value = preg_replace('/(data|file):/i', '\1&#58;', $value);
        
        // Strip tags but allow some safe ones if needed (customize based on requirements)
        $value = strip_tags($value);
        
        // Trim whitespace
        $value = trim($value);
        
        return $value;
    }
}