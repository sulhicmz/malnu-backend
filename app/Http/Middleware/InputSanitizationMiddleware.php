<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Traits\InputValidationTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class InputSanitizationMiddleware implements MiddlewareInterface
{
    use InputValidationTrait;

    protected ContainerInterface $container;
    protected RequestInterface $request;
    protected HttpResponse $response;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $container->get(RequestInterface::class);
        $this->response = $container->get(HttpResponse::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();

        if (is_array($parsedBody) && !empty($parsedBody)) {
            if ($this->containsMaliciousPatterns($parsedBody)) {
                return $this->response->json([
                    'success' => false,
                    'error' => [
                        'message' => 'Invalid input detected',
                        'code' => 'MALICIOUS_INPUT'
                    ],
                    'timestamp' => date('c')
                ])->withStatus(400);
            }

            $sanitizedBody = $this->sanitizeInput($parsedBody);
            $request = $request->withParsedBody($sanitizedBody);
        }

        $queryParams = $request->getQueryParams();
        if (!empty($queryParams)) {
            if ($this->containsMaliciousPatterns($queryParams)) {
                return $this->response->json([
                    'success' => false,
                    'error' => [
                        'message' => 'Invalid input detected',
                        'code' => 'MALICIOUS_INPUT'
                    ],
                    'timestamp' => date('c')
                ])->withStatus(400);
            }

            $sanitizedQueryParams = $this->sanitizeInput($queryParams);
            $request = $request->withQueryParams($sanitizedQueryParams);
        }

        $uploadedFiles = $request->getUploadedFiles();
        if (!empty($uploadedFiles)) {
            foreach ($uploadedFiles as $key => $file) {
                if ($file->getError() === UPLOAD_ERR_OK) {
                    $fileValidation = $this->validateUploadedFile($file);
                    if (!empty($fileValidation)) {
                        return $this->response->json([
                            'success' => false,
                            'error' => [
                                'message' => 'File validation failed',
                                'code' => 'FILE_VALIDATION_FAILED',
                                'details' => $fileValidation
                            ],
                            'timestamp' => date('c')
                        ])->withStatus(400);
                    }
                }
            }
        }

        return $handler->handle($request);
    }

    protected function containsMaliciousPatterns(array $input): bool
    {
        foreach ($input as $value) {
            if (is_string($value)) {
                if ($this->detectSqlInjection($value) || $this->detectXss($value)) {
                    return true;
                }
            } elseif (is_array($value)) {
                if ($this->containsMaliciousPatterns($value)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function detectSqlInjection(string $value): bool
    {
        $patterns = [
            '/(\s|^)(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|TRUNCATE)(\s|$)/i',
            '/(\s|^)(UNION|JOIN|WHERE|OR|AND)(\s|$)/i',
            '/[\'";\\]/',
            '/--/',
            '/\/\*/',
            '/\*\//',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    protected function detectXss(string $value): bool
    {
        $patterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/<iframe\b[^>]*>(.*?)<\/iframe>/is',
            '/<object\b[^>]*>(.*?)<\/object>/is',
            '/<embed\b[^>]*>(.*?)<\/embed>/is',
            '/on\w+\s*=\s*["\']?[^"\'\s>]+/i',
            '/javascript:/i',
            '/vbscript:/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    protected function validateUploadedFile($file): array
    {
        $errors = [];

        $maxFileSize = 5 * 1024 * 1024;
        if ($file->getSize() > $maxFileSize) {
            $errors[] = 'File size exceeds maximum allowed size of 5MB';
        }

        $clientFilename = $file->getClientFilename();
        if ($clientFilename) {
            $extension = strtolower(pathinfo($clientFilename, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv'];

            if (!in_array($extension, $allowedExtensions)) {
                $errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $allowedExtensions);
            }
        }

        $clientMediaType = $file->getClientMediaType();
        if ($clientMediaType) {
            $allowedMimeTypes = [
                'image/jpeg', 'image/png', 'image/gif',
                'application/pdf',
                'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain', 'text/csv'
            ];

            if (!in_array($clientMediaType, $allowedMimeTypes)) {
                $errors[] = 'File MIME type not allowed';
            }
        }

        return $errors;
    }
}
