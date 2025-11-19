<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\AbstractController;
use App\Models\User;
use Hypervel\Http\Request;
use Hypervel\JWT\JWT;
use Hypervel\JWT\JWTException;
use Illuminate\Support\Facades\Hash;

class AuthController extends AbstractController
{
    protected JWT $jwt;

    public function __construct(JWT $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Authenticate user and return JWT token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = $this->jwt->attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        $user = User::where('email', $request->email)->first();

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->jwt->factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $this->getUserRole($user),
            ]
        ]);
    }

    /**
     * Log out user (Invalidate token)
     */
    public function logout(Request $request)
    {
        try {
            $this->jwt->invalidate();
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not logout'], 500);
        }
    }

    /**
     * Refresh JWT token
     */
    public function refresh()
    {
        try {
            $newToken = $this->jwt->refresh();
            return response()->json([
                'token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => $this->jwt->factory()->getTTL() * 60,
            ]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not refresh token'], 500);
        }
    }

    /**
     * Get authenticated user info
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $this->getUserRole($user),
                'avatar_url' => $user->avatar_url,
                'is_active' => $user->is_active,
            ]
        ]);
    }

    /**
     * Get user role based on relationships
     */
    private function getUserRole(User $user): string
    {
        if ($user->student()->exists()) {
            return 'student';
        } elseif ($user->parent()->exists()) {
            return 'parent';
        } elseif ($user->teacher()->exists()) {
            return 'teacher';
        } elseif ($user->staff()->exists()) {
            return 'admin';
        }
        
        // Check if user has admin role through roles relationship
        // This is a simplified check - in a real app you'd check the roles properly
        return 'user';
    }
}