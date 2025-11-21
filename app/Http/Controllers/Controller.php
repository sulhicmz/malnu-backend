<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

abstract class Controller
{
    protected RequestInterface $request;
    protected ResponseInterface $response;

    public function __construct(ContainerInterface $container)
    {
        $this->request = $container->get(RequestInterface::class);
        $this->response = $container->get(ResponseInterface::class);
    }

    protected function responseJson(array $data, int $status = 200)
    {
        return $this->response->json($data)->withStatus($status);
    }
}