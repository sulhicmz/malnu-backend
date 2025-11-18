<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\AbstractController;
use App\Models\User;
use Hypervel\Http\Request;
use Hypervel\JWT\JWT;
use Hypervel\JWT\JWTException;
use Illuminate\Support\Facades\Hash;

class MobileAuthController extends AbstractController
{
    protected $jwt;

    public function __construct(JWT $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Mobile user login
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (empty($credentials['email']) || empty($credentials['password'])) {
            return [
                'success' => false,
                'message' => 'Email and password are required',
            ];
        }

        try {
            // Attempt to verify the credentials and create a token for the user
            if (!$token = $this->jwt->attempt($credentials)) {
                return [
                    'success' => false,
                    'message' => 'Invalid credentials',
                ];
            }
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'Could not create token',
            ];
        }

        $user = User::where('email', $credentials['email'])->first();

        return [
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $this->getUserRole($user),
                ],
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $this->jwt->factory()->getTTL() * 60,
            ],
        ];
    }

    /**
     * Get user role based on their relationships
     */
    private function getUserRole($user)
    {
        if ($user->student()->exists()) {
            return 'student';
        } elseif ($user->parent()->exists()) {
            return 'parent';
        } elseif ($user->teacher()->exists()) {
            return 'teacher';
        } elseif ($user->staff()->exists()) {
            return 'staff';
        }
        
        return 'user';
    }

    /**
     * Get authenticated user info
     */
    public function me(Request $request)
    {
        try {
            $user = $this->jwt->parseToken()->authenticate();
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'User not authenticated',
            ];
        }

        return [
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $this->getUserRole($user),
                'avatar_url' => $user->avatar_url,
                'is_active' => $user->is_active,
            ],
        ];
    }

    /**
     * Refresh the token
     */
    public function refresh()
    {
        try {
            $token = $this->jwt->refresh();
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'Could not refresh token',
            ];
        }

        return [
            'success' => true,
            'data' => [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $this->jwt->factory()->getTTL() * 60,
            ],
        ];
    }

    /**
     * Log out user (Invalidate the token)
     */
    public function logout()
    {
        try {
            $this->jwt->parseToken()->invalidate();
        } catch (JWTException $e) {
            return [
                'success' => false,
                'message' => 'Could not logout, token is invalid',
            ];
        }

        return [
            'success' => true,
            'message' => 'Successfully logged out',
        ];
    }
}