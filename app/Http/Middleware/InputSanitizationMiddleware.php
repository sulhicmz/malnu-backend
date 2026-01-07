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
        $this->request = $container->get(Hypervel\HttpServer\Contract\RequestInterface::class);
        $this->response = $container->get(HttpResponse::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get the request body data
        $parsedBody = $request->getParsedBody();
        
        if (is_array($parsedBody) && !empty($parsedBody)) {
            // Sanitize the parsed body
            $sanitizedBody = $this->sanitizeInput($parsedBody);
            $request = $request->withParsedBody($sanitizedBody);
        }
        
        // Also sanitize query parameters if needed
        $queryParams = $request->getQueryParams();
        if (!empty($queryParams)) {
            $sanitizedQueryParams = $this->sanitizeInput($queryParams);
            $request = $request->withQueryParams($sanitizedQueryParams);
        }
        
        // Sanitize uploaded files metadata if present
        $uploadedFiles = $request->getUploadedFiles();
        if (!empty($uploadedFiles)) {
            // Process uploaded files for security validation
            foreach ($uploadedFiles as $key => $file) {
                if ($file->getError() === UPLOAD_ERR_OK) {
                    // Validate file type and size here if needed
                    $maxFileSize = 5 * 1024 * 1024; // 5MB
                    if ($file->getSize() > $maxFileSize) {
                        return $this->response->json([
                            'success' => false,
                            'error' => [
                                'message' => 'File size exceeds maximum allowed size',
                                'code' => 'FILE_SIZE_EXCEEDED'
                            ],
                            'timestamp' => date('c')
                        ])->withStatus(400);
                    }
                }
            }
        }

        return $handler->handle($request);
    }
}