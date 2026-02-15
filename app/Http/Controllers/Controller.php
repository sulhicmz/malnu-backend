<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Hypervel\Http\Request;
use Hypervel\Http\Response;

abstract class Controller
{
    protected RequestInterface $request;

    protected ResponseInterface $response;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Return JSON response.
     */
    protected function response()
    {
        return $this->response;
    }

    /**
     * Return JSON response.
     */
    protected function json(array $data, int $statusCode = 200)
    {
        return $this->response->json($data)->withStatus($statusCode);
    }
}
