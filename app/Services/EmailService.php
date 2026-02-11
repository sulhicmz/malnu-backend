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

        $this->fromAddress = config('mail.from.address', 'noreply@example.com');
        $this->fromName = config('mail.from.name', config('app.name', 'Malnu'));
        $this->config = config('resilient_email', [
            'timeout' => 30,
            'retry_attempts' => 3,
        ]);

        $transport = (new Swift_SmtpTransport(
            config('mail.mailers.smtp.host', 'smtp.mailtrap.io'),
            (int) config('mail.mailers.smtp.port', 2525),
            config('mail.mailers.smtp.encryption', 'tls')
        ))
            ->setUsername(config('mail.mailers.smtp.username', ''))
            ->setPassword(config('mail.mailers.smtp.password', ''));

        $mailTimeout = config('mail.mailers.smtp.timeout');
        if ($mailTimeout !== null) {
            $transport->setTimeout((int) $mailTimeout);
        }

        $this->mailer = new Swift_Mailer($transport);
    }

    public function sendPasswordResetEmail(string $email, string $token): bool
    {
        return $this->retryService->executeWithCircuitBreaker(
            'email',
            function () use ($email, $token) {
                $appName = config('mail.from.name', config('app.name', 'Malnu'));
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
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        return "{$frontendUrl}/auth/reset-password?token={$token}";
    }

    private function getPasswordResetTemplate(string $resetLink): string
    {
        $appName = config('mail.from.name', config('app.name', 'Malnu'));

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
