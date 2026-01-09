<?php

declare(strict_types=1);

namespace App\Services;

use Exception;

class EmailService
{
    public function sendPasswordResetEmail(string $email, string $resetToken): bool
    {
        $resetUrl = env('APP_URL', 'http://localhost') . '/reset-password?token=' . $resetToken;

        try {
            $mailer = \Hyperf\Support\make(\Symfony\Component\Mailer\MailerInterface::class);

            $emailMessage = (new \Symfony\Component\Mime\Email())
                ->from(env('MAIL_FROM_ADDRESS', 'noreply@example.com'), env('MAIL_FROM_NAME', config('app_name', 'App')))
                ->to($email)
                ->subject('Password Reset Request')
                ->text(
                    "You are receiving this email because we received a password reset request for your account.\n\n"
                    . "Click the link below to reset your password:\n"
                    . $resetUrl . "\n\n"
                    . "This link will expire in 1 hour.\n\n"
                    . 'If you did not request a password reset, no further action is required.'
                );

            $mailer->send($emailMessage);

            return true;
        } catch (Exception $e) {
            \Hyperf\Support\make(\Psr\Log\LoggerInterface::class)->error('Failed to send password reset email: ' . $e->getMessage());
            return false;
        }
    }
}
