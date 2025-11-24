<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Api\BaseController;
use App\Models\User;
use Hyperf\Support\Facades\Hash;
use Hyperf\Support\Facades\Auth;

class JwtAuthController extends BaseController
{
    /**
     * Handle a login request.
     */
    public function login()
    {
        $credentials = $this->request->all(['email', 'password']);

        if (!isset($credentials['email']) || !isset($credentials['password'])) {
            return $this->errorResponse('Email and password are required', 'VALIDATION_ERROR', null, 400);
        }

        // Attempt to authenticate the user
        if (!Auth::guard('jwt')->attempt($credentials)) {
            return $this->errorResponse('Invalid credentials', 'AUTHENTICATION_FAILED', null, 401);
        }

        // Get the authenticated user
        $user = Auth::guard('jwt')->user();
        
        // Generate token
        $token = Auth::guard('jwt')->login($user);

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('jwt')->getTTL() * 60,
        ]);
    }

    /**
     * Handle a register request.
     */
    public function register()
    {
        $data = $this->request->all(['name', 'email', 'password', 'password_confirmation']);

        // Validate required fields
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            return $this->errorResponse('Name, email and password are required', 'VALIDATION_ERROR', null, 400);
        }

        // Check password confirmation
        if ($data['password'] !== $data['password_confirmation']) {
            return $this->errorResponse('Password confirmation does not match', 'VALIDATION_ERROR', null, 400);
        }

        // Check if user already exists
        $existingUser = User::where('email', $data['email'])->first();
        if ($existingUser) {
            return $this->errorResponse('User with this email already exists', 'DUPLICATE_USER', null, 409);
        }

        // Create the user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Generate token
        $token = Auth::guard('jwt')->login($user);

        return $this->successResponse([
            'message' => 'User registered successfully',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('jwt')->getTTL() * 60,
            'user' => $user,
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout()
    {
        // Invalidate the current token by adding it to the blacklist
        Auth::guard('jwt')->logout();

        return $this->successResponse(null, 'Successfully logged out');
    }

    /**
     * Refresh a token.
     */
    public function refresh()
    {
        try {
            $newToken = Auth::guard('jwt')->refresh();
            
            return $this->successResponse([
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => Auth::guard('jwt')->getTTL() * 60,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse('Could not refresh token', 'TOKEN_REFRESH_FAILED', null, 401);
        }
    }

    /**
     * Get the authenticated User.
     */
    public function me()
    {
        $user = Auth::guard('jwt')->user();
        
        if (!$user) {
            return $this->errorResponse('User not authenticated', 'UNAUTHORIZED', null, 401);
        }
        
        return $this->successResponse($user);
    }
}