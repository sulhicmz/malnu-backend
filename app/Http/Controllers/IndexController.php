<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;

class IndexController extends Controller
{
    public function index(RequestInterface $request): array
    {
        $user = $request->input('user', 'Hyperf') ?? 'Hyperf';
        $method = $request->getMethod();

        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }
}
