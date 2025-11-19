<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\AbstractController;
use Hypervel\Http\Request;
use Hypervel\Support\Facades\JWT;
use App\Models\User;
use Hypervel\Http\JsonResponse;

class BaseMobileController extends AbstractController
{
    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWT::factory()->getTTL() * 60
        ];
    }

    protected function respondWithError($message, $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $code);
    }

    protected function respondWithSuccess($data = null, $message = 'Success', $code = 200)
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    protected function getUserFromToken()
    {
        try {
            $token = JWT::parseToken();
            $user = $token->authenticate();
            return $user;
        } catch (\Exception $e) {
            return null;
        }
    }
}