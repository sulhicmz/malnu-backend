<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AuthServiceInterface;
use App\Contracts\JWTServiceInterface;
use App\Contracts\TokenBlacklistServiceInterface;
use App\Models\PasswordResetToken;
use App\Models\User;
use Exception;

class AuthService implements AuthServiceInterface
{
    private JWTServiceInterface $jwtService;

    private TokenBlacklistServiceInterface $tokenBlacklistService;

    private EmailService $emailService;

    public function __construct()
    {
        $this->jwtService = new JWTService();
        $this->tokenBlacklistService = new TokenBlacklistService();
        $this->emailService = new EmailService();
    }

    /**
     * Register a new user.
     */
    public function register(array $data): array
    {
        $existingUser = User::where('email', $data['email'])->first();
        if ($existingUser) {
            throw new Exception('User with this email already exists');
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'is_active' => true,
        ]);

        return ['user' => $user->toArray()];
    }

    /**
     * Authenticate user and return token.
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

        if (! $user) {
            throw new Exception('Invalid credentials');
        }

        // Generate JWT token
        $token = $this->jwtService->generateToken([
            'id' => $user['id'],
            'email' => $user['email'],
        ]);

        return [
            'user' => $user,
            'token' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $this->jwtService->getExpirationTime(),
            ],
        ];
    }

    /**
     * Get authenticated user from token.
     */
    public function getUserFromToken(string $token): ?array
    {
        // Check if token is blacklisted
        if ($this->tokenBlacklistService->isTokenBlacklisted($token)) {
            return null;
        }

        $payload = $this->jwtService->decodeToken($token);

        if (! $payload) {
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
     * Refresh token.
     */
    public function refreshToken(string $token): array
    {
        // Check if token is blacklisted
        if ($this->tokenBlacklistService->isTokenBlacklisted($token)) {
            throw new Exception('Token is blacklisted');
        }

        $newToken = $this->jwtService->refreshToken($token);

        return [
            'token' => [
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => $this->jwtService->getExpirationTime(),
            ],
        ];
    }

    /**
     * Logout - add token to blacklist.
     */
    public function logout(string $token): void
    {
        $this->tokenBlacklistService->blacklistToken($token);
    }

    /**
     * Request password reset.
     */
    public function requestPasswordReset(string $email): array
    {
        // Find user by email
        $user = User::where('email', $email)->first();

        if (! $user) {
            // Don't reveal if email exists to prevent enumeration
            return ['success' => true, 'message' => 'If the email exists, a reset link has been sent'];
        }

        // Delete any existing reset tokens for this user
        PasswordResetToken::where('user_id', $user->id)->delete();

        // Generate reset token
        $resetToken = bin2hex(random_bytes(32)); // 64 character hex string
        $expiresAt = date('Y-m-d H:i:s', time() + (60 * 60)); // 1 hour from now

        // Store hashed token in database
        PasswordResetToken::create([
            'user_id' => $user->id,
            'token' => password_hash($resetToken, PASSWORD_DEFAULT),
            'expires_at' => $expiresAt,
        ]);

        // Send email with reset token (plaintext, not hashed)
        $this->emailService->sendPasswordResetEmail($email, $resetToken);

        // Return success message without exposing the token
        return [
            'success' => true,
            'message' => 'If the email exists, a reset link has been sent',
        ];
    }

    /**
     * Reset password with token.
     */
    public function resetPassword(string $token, string $newPassword): array
    {
        // Find valid reset token in database
        $resetTokenRecord = null;
        $allTokens = PasswordResetToken::all();

        foreach ($allTokens as $record) {
            if (password_verify($token, $record->token)) {
                $resetTokenRecord = $record;
                break;
            }
        }

        if (! $resetTokenRecord || strtotime($resetTokenRecord->expires_at) < time()) {
            throw new Exception('Invalid or expired reset token');
        }

        // Validate password strength
        if (strlen($newPassword) < 8) {
            throw new Exception('Password must be at least 8 characters');
        }

        // Find user and update password
        $user = User::find($resetTokenRecord->user_id);
        if (! $user) {
            throw new Exception('User not found');
        }

        $user->password = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->save();

        // Delete used reset token
        $resetTokenRecord->delete();

        return [
            'success' => true,
            'message' => 'Password has been reset successfully',
        ];
    }

    /**
     * Change password for authenticated user.
     */
    public function changePassword(string $userId, string $currentPassword, string $newPassword): array
    {
        // Find user
        $user = User::find($userId);
        if (! $user) {
            throw new Exception('User not found');
        }

        // Verify current password
        if (! password_verify($currentPassword, $user->password)) {
            throw new Exception('Current password is incorrect');
        }

        // Validate new password strength
        if (strlen($newPassword) < 8) {
            throw new Exception('New password must be at least 8 characters');
        }

        // Update password
        $user->password = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->save();

        return [
            'success' => true,
            'message' => 'Password has been changed successfully',
        ];
    }

    /**
     * Get all users from database.
     */
    private function getAllUsers(): array
    {
        return User::all()->toArray();
    }
}
