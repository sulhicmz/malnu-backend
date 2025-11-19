<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\User;
use Hypervel\Auth\JWT\JWTManager;
use Hypervel\Http\Request;
use Hypervel\Support\Facades\Hash;

class LoginController extends ApiController
{
    protected JWTManager $jwt;

    public function __construct(JWTManager $jwt)
    {
        $this->jwt = $jwt;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        if (!$user->is_active) {
            return $this->errorResponse('Account is not active', 401);
        }

        $token = $this->jwt->fromUser($user);
        $refreshToken = $this->jwt->getManager()->refresh($token);

        return $this->successResponse([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => $this->jwt->getPayload($token)->get('exp') - time(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $this->getUserRole($user),
            ],
        ]);
    }

    public function logout(Request $request)
    {
        try {
            $this->jwt->invalidate($request->bearerToken());
            return $this->successResponse(null, 'Successfully logged out');
        } catch (\Exception $e) {
            return $this->errorResponse('Could not logout', 400);
        }
    }

    public function refresh(Request $request)
    {
        try {
            $newToken = $this->jwt->refresh($request->bearerToken());
            return $this->successResponse([
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => $this->jwt->getPayload($newToken)->get('exp') - time(),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Could not refresh token', 400);
        }
    }

    public function me(Request $request)
    {
        try {
            $user = $this->jwt->toUser($request->bearerToken());
            return $this->successResponse([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $this->getUserRole($user),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Unable to retrieve user', 400);
        }
    }

    private function getUserRole(User $user): string
    {
        // Check relationships to determine role
        if ($user->student) {
            return 'student';
        } elseif ($user->teacher) {
            return 'teacher';
        } elseif ($user->parent) {
            return 'parent';
        } elseif ($user->staff) {
            return 'admin';
        }
        
        // Default to admin if no specific role found
        return 'admin';
    }
}