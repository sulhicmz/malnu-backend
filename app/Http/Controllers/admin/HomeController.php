<?php

declare(strict_types=1);

namespace App\Http\Controllers\admin;

use Hypervel\Http\Request;
use Hypervel\Http\Response;

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
