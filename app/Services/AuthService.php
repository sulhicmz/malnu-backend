<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AuthServiceInterface;
use App\Contracts\JWTServiceInterface;
use App\Contracts\TokenBlacklistServiceInterface;
use App\Contracts\UserRepositoryInterface;
use App\Models\PasswordResetToken;
use Exception;

class AuthService implements AuthServiceInterface
{
    private JWTServiceInterface $jwtService;

    private TokenBlacklistServiceInterface $tokenBlacklistService;

    private EmailService $emailService;

    private PasswordValidator $passwordValidator;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        JWTServiceInterface $jwtService,
        TokenBlacklistServiceInterface $tokenBlacklistService,
        EmailService $emailService,
        PasswordValidator $passwordValidator,
        UserRepositoryInterface $userRepository
    ) {
        $this->jwtService = $jwtService;
        $this->tokenBlacklistService = $tokenBlacklistService;
        $this->emailService = $emailService;
        $this->passwordValidator = $passwordValidator;
        $this->userRepository = $userRepository;
    }

    /**
     * Register a new user.
     */
    public function register(array $data): array
    {
        $existingUser = $this->userRepository->findByEmail($data['email']);
        if ($existingUser) {
            throw new Exception('User with this email already exists');
        }

        $errors = $this->passwordValidator->validate($data['password']);
        if (! empty($errors)) {
            throw new Exception(implode(' ', $errors));
        }

        $user = $this->userRepository->create([
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
        $user = $this->userRepository->findByEmail($email);

        if (! $user) {
            throw new Exception('Invalid credentials');
        }

        if (! password_verify($password, $user->password)) {
            throw new Exception('Invalid credentials');
        }

        $token = $this->jwtService->generateToken([
            'id' => $user->id,
            'email' => $user->email,
        ]);

        return [
            'user' => $user->toArray(),
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

        $user = $this->userRepository->findById($payload['data']['id']);

        return $user ? $user->toArray() : null;
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
        $user = $this->userRepository->findByEmail($email);

        if (! $user) {
            // Don't reveal if email exists to prevent enumeration
            return ['success' => true, 'message' => 'If the email exists, a reset link has been sent'];
        }

        // Delete any existing reset tokens for this user
        PasswordResetToken::where('user_id', $user->id)->delete();

        // Generate reset token (plaintext for email, hashed for storage)
        $resetToken = bin2hex(random_bytes(32));
        $hashedToken = password_hash($resetToken, PASSWORD_DEFAULT);
        $expiresAt = now()->addHour();

        // Store hashed token in database
        PasswordResetToken::create([
            'user_id' => $user->id,
            'token' => $hashedToken,
            'expires_at' => $expiresAt,
        ]);

        // Send email with plaintext token
        $this->emailService->sendPasswordResetEmail($email, $resetToken);

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
        $errors = $this->passwordValidator->validate($newPassword);
        if (! empty($errors)) {
            throw new Exception(implode(' ', $errors));
        }

        // Get all valid tokens from database
        $validTokens = PasswordResetToken::valid()->get();

        if ($validTokens->isEmpty()) {
            throw new Exception('Invalid or expired reset token');
        }

        // Find the matching token by verifying against all valid tokens
        $resetTokenRecord = null;
        foreach ($validTokens as $tokenRecord) {
            if (password_verify($token, $tokenRecord->token)) {
                $resetTokenRecord = $tokenRecord;
                break;
            }
        }

        // Check if token was found and is valid
        if (! $resetTokenRecord) {
            throw new Exception('Invalid reset token');
        }

        // Check if token is expired
        if ($resetTokenRecord->isExpired()) {
            $resetTokenRecord->delete();
            throw new Exception('Reset token has expired');
        }

        // Get user
        $user = $this->userRepository->findById($resetTokenRecord->user_id);

        if (! $user) {
            throw new Exception('User not found');
        }

        // Update user password
        $user->update([
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        // Delete used token
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
        // Fetch user from database
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            throw new Exception('User not found');
        }

        // Verify current password
        if (! password_verify($currentPassword, $user->password)) {
            throw new Exception('Current password is incorrect');
        }

        $errors = $this->passwordValidator->validate($newPassword);
        if (! empty($errors)) {
            throw new Exception('New password: ' . implode(' ', $errors));
        }

        // Update user password
        $user->update([
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        return [
            'success' => true,
            'message' => 'Password has been changed successfully',
        ];
    }
}
