<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class EmailService
{
    private Swift_Mailer $mailer;

    private string $fromAddress;

    private string $fromName;

    private RetryService $retryService;

    private CircuitBreakerService $circuitBreaker;

    private array $config;

    private LoggerInterface $logger;

    public function __construct(
        RetryService $retryService,
        CircuitBreakerService $circuitBreaker,
        LoggerInterface $logger
    ) {
        $this->retryService = $retryService;
        $this->circuitBreaker = $circuitBreaker;
        $this->logger = $logger;

        $this->fromAddress = env('MAIL_FROM_ADDRESS', 'noreply@example.com');
        $this->fromName = env('MAIL_FROM_NAME', config('app.name', 'Malnu'));
        $this->config = config('resilient_email', [
            'timeout' => 30,
            'retry_attempts' => 3,
        ]);

        $transport = (new Swift_SmtpTransport(
            env('MAIL_HOST', 'smtp.mailtrap.io'),
            (int) env('MAIL_PORT', 2525),
            env('MAIL_ENCRYPTION', 'tls')
        ))
            ->setUsername(env('MAIL_USERNAME', ''))
            ->setPassword(env('MAIL_PASSWORD', ''));

        if (env('MAIL_TIMEOUT')) {
            $transport->setTimeout((int) env('MAIL_TIMEOUT', $this->config['timeout']));
        }

        $this->mailer = new Swift_Mailer($transport);
    }

    public function sendPasswordResetEmail(string $email, string $token): bool
    {
        return $this->retryService->executeWithCircuitBreaker(
            'email',
            function () use ($email, $token) {
                $appName = env('MAIL_FROM_NAME', config('app.name', 'Malnu'));
                $resetLink = $this->generateResetLink($token);

                $subject = "Password Reset Request - {$appName}";
                $body = $this->getPasswordResetTemplate($resetLink);

                $message = (new Swift_Message($subject))
                    ->setFrom([$this->fromAddress => $this->fromName])
                    ->setTo([$email])
                    ->setBody($body, 'text/html');

                $result = $this->mailer->send($message);

                $this->logger->info('Password reset email sent', [
                    'email' => $email,
                    'result' => $result,
                ]);

                return $result > 0;
            },
            $this->circuitBreaker,
            $this->getFallback('sendPasswordResetEmail', $email),
            [
                'max_attempts' => $this->config['retry_attempts'],
                'initial_delay' => 2000,
                'max_delay' => 30000,
                'multiplier' => 2,
                'jitter' => true,
                'retry_on' => [Exception::class],
                'operation_name' => 'send_password_reset_email',
            ]
        );
    }

    private function getFallback(string $operation, string $email): callable
    {
        return function (string $service) use ($operation, $email) {
            $this->logger->error('Email service unavailable - circuit breaker open', [
                'service' => $service,
                'operation' => $operation,
                'email' => $email,
            ]);

            return false;
        };
    }

    private function generateResetLink(string $token): string
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        return "{$frontendUrl}/auth/reset-password?token={$token}";
    }

    private function getPasswordResetTemplate(string $resetLink): string
    {
        $appName = env('MAIL_FROM_NAME', config('app.name', 'Malnu'));

        return "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Reset Your Password</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .button { display: inline-block; padding: 12px 24px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-top: 20px; }
        .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Password Reset</h1>
        </div>
        <div class='content'>
            <p>Hello,</p>
            <p>We received a request to reset your password for your <strong>{$appName}</strong> account.</p>
            <p>Click button below to reset your password:</p>
            <a href='{$resetLink}' class='button'>Reset Password</a>
            <p>Or copy and paste this link into your browser:</p>
            <p>{$resetLink}</p>
            <p><strong>This link will expire in 1 hour.</strong></p>
            <p>If you didn't request this password reset, please ignore this email or contact support if you have concerns.</p>
        </div>
        <div class='footer'>
            <p>&copy; " . date('Y') . " {$appName}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>";
    }
}
