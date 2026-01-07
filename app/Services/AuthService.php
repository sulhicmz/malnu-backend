<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    private JWTService $jwtService;
    private TokenBlacklistService $tokenBlacklistService;

    public function __construct()
    {
        $this->jwtService = new JWTService();
        $this->tokenBlacklistService = new TokenBlacklistService();
    }

    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        $existingUser = User::where('email', $data['email'])->first();
        if ($existingUser) {
            throw new \Exception('User with this email already exists');
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);

        return ['user' => $user->toArray()];
    }

    /**
     * Authenticate user and return token
     */
    public function login(string $email, string $password): array
    {
        $users = $this->getAllUsers();
        $user = null;
        
        foreach ($users as $u) {
            if ($u['email'] === $email && password_verify($password, $u['password'])) {
                $user = $u;
                break;
            }
        }
        
        if (!$user) {
            throw new \Exception('Invalid credentials');
        }

        // Generate JWT token
        $token = $this->jwtService->generateToken([
            'id' => $user['id'],
            'email' => $user['email']
        ]);

        return [
            'user' => $user,
            'token' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $this->jwtService->getExpirationTime()
            ]
        ];
    }

    /**
     * Get authenticated user from token
     */
    public function getUserFromToken(string $token): ?array
    {
        // Check if token is blacklisted
        if ($this->tokenBlacklistService->isTokenBlacklisted($token)) {
            return null;
        }
        
        $payload = $this->jwtService->decodeToken($token);
        
        if (!$payload) {
            return null;
        }

        $users = $this->getAllUsers();
        foreach ($users as $user) {
            if ($user['id'] === $payload['data']['id']) {
                return $user;
            }
        }
        
        return null;
    }

    /**
     * Refresh token
     */
    public function refreshToken(string $token): array
    {
        // Check if token is blacklisted
        if ($this->tokenBlacklistService->isTokenBlacklisted($token)) {
            throw new \Exception('Token is blacklisted');
        }
        
        $newToken = $this->jwtService->refreshToken($token);

        return [
            'token' => [
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => $this->jwtService->getExpirationTime()
            ]
        ];
    }

    /**
     * Logout - add token to blacklist
     */
    public function logout(string $token): void
    {
        $this->tokenBlacklistService->blacklistToken($token);
    }

    /**
     * Request password reset
     */
    public function requestPasswordReset(string $email): array
    {
        // Find user by email (simplified approach)
        $users = $this->getAllUsers();
        $user = null;
        
        foreach ($users as $u) {
            if ($u['email'] === $email) {
                $user = $u;
                break;
            }
        }
        
        if (!$user) {
            // Don't reveal if email exists to prevent enumeration
            return ['success' => true, 'message' => 'If the email exists, a reset link has been sent'];
        }

        // Generate reset token
        $resetToken = bin2hex(random_bytes(32)); // 64 character hex string
        $expiresAt = time() + (60 * 60); // 1 hour from now

        // In a real implementation, this would save to database
        // For now, we'll just return the token
        return [
            'success' => true,
            'message' => 'If the email exists, a reset link has been sent',
            'reset_token' => $resetToken,
            'expires_at' => $expiresAt
        ];
    }

    /**
     * Reset password with token
     */
    public function resetPassword(string $token, string $newPassword): array
    {
        // In a real implementation, this would validate the reset token against the database
        // For now, we'll just validate the token format and update the password
        
        if (strlen($token) !== 64) { // 32 bytes = 64 hex chars
            throw new \Exception('Invalid reset token');
        }

        // Validate password strength
        if (strlen($newPassword) < 8) {
            throw new \Exception('Password must be at least 8 characters');
        }

        // In a real implementation, this would update the user's password in the database
        return [
            'success' => true,
            'message' => 'Password has been reset successfully'
        ];
    }

    /**
     * Change password for authenticated user
     */
    public function changePassword(string $userId, string $currentPassword, string $newPassword): array
    {
        // In a real implementation, this would fetch the user from the database
        // and verify the current password
        
        if (strlen($newPassword) < 8) {
            throw new \Exception('New password must be at least 8 characters');
        }

        // In a real implementation, this would update the user's password in the database
        return [
            'success' => true,
            'message' => 'Password has been changed successfully'
        ];
    }

    /**
     * Get all users from database
     */
    private function getAllUsers(): array
    {
        return User::all()->toArray();
    }
}