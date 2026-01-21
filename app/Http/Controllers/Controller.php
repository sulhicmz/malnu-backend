<?php

namespace App\Http\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

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
     * Return JSON response
     */
    protected function response()
    {
        return $this->response;
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $statusCode = 200)
    {
        return $this->response->json($data)->withStatus($statusCode);
    }
}