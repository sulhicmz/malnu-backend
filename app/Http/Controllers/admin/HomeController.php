<?php

declare (strict_types = 1);

namespace App\Http\Controllers\admin;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class HomeController
{
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        return $response->raw('Hello Hyperf!');
    }

    public function indexView()
    {
        return view('home');
    }
}
