<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Models\User;
use Hypervel\Http\Request;
use Hypervel\Support\Facades\Hash;
use Hypervel\Support\Facades\JWT;

class AuthController extends BaseMobileController
{
    /**
     * Get a JWT token via given credentials.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (empty($credentials['email']) || empty($credentials['password'])) {
            return $this->respondWithError('Email and password are required', 400);
        }

        if (!$token = JWT::attempt($credentials)) {
            return $this->respondWithError('Invalid credentials', 401);
        }

        // Update last login info
        $user = User::where('email', $credentials['email'])->first();
        if ($user) {
            $user->update([
                'last_login_time' => now(),
                'last_login_ip' => $request->ip()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => array_merge(
                $this->respondWithToken($token),
                ['user' => $this->formatUserResponse($user)]
            )
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return JsonResponse
     */
    public function me()
    {
        $user = $this->getUserFromToken();
        
        if (!$user) {
            return $this->respondWithError('User not authenticated', 401);
        }

        return $this->respondWithSuccess(
            ['user' => $this->formatUserResponse($user)],
            'User retrieved successfully'
        );
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return JsonResponse
     */
    public function logout()
    {
        try {
            JWT::parseToken()->invalidate();
            return $this->respondWithSuccess(null, 'Successfully logged out');
        } catch (\Exception $e) {
            return $this->respondWithError('Could not logout, please try again');
        }
    }

    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh()
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => $this->respondWithToken(JWT::parseToken()->refresh())
            ]);
        } catch (\Exception $e) {
            return $this->respondWithError('Could not refresh token');
        }
    }

    /**
     * Format user response for mobile API
     */
    private function formatUserResponse($user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'full_name' => $user->full_name,
            'phone' => $user->phone,
            'avatar_url' => $user->avatar_url,
            'is_active' => $user->is_active,
            'last_login_time' => $user->last_login_time,
            'role' => $this->getUserRole($user),
            'profile_type' => $this->getUserProfileType($user)
        ];
    }

    /**
     * Get user role
     */
    private function getUserRole($user)
    {
        // Get the user's role from the role relationships
        $modelHasRole = \App\Models\ModelHasRole::where('model_id', $user->id)
            ->where('model_type', User::class)
            ->first();
            
        if ($modelHasRole) {
            $role = \App\Models\Role::find($modelHasRole->role_id);
            return $role ? $role->name : 'user';
        }
        
        return 'user';
    }

    /**
     * Get user profile type (student, teacher, parent, staff)
     */
    private function getUserProfileType($user)
    {
        if ($user->student) {
            return 'student';
        } elseif ($user->teacher) {
            return 'teacher';
        } elseif ($user->parent) {
            return 'parent';
        } elseif ($user->staff) {
            return 'staff';
        }
        
        return 'user';
    }
}