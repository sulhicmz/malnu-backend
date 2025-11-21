<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AbstractController;

abstract class ApiController extends AbstractController
{
    /**
     * Success response
     */
    protected function success($data = null, string $message = 'Success', int $code = 200): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'code' => $code
        ];
    }

    /**
     * Error response
     */
    protected function error(string $message = 'Error', int $code = 400, $errors = null): array
    {
        return [
            'success' => false,
            'message' => $message,
            'data' => null,
            'code' => $code,
            'errors' => $errors
        ];
    }
}