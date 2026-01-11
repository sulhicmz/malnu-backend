<?php

declare(strict_types=1);

namespace App\Services;

use App\Patterns\CircuitBreaker;
use App\Patterns\RetryWithBackoff;
use Exception;
use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use Swift_TransportException;

class EmailService
{
    private Swift_Mailer $mailer;

    private string $fromAddress;

    private string $fromName;

    private CircuitBreaker $circuitBreaker;

    private RetryWithBackoff $retryWithBackoff;

    private LoggerInterface $logger;

    private int $timeoutSeconds;

    private int $maxRetries;

    private int $initialDelayMs;

    public function __construct(
        ?LoggerInterface $logger = null
    ) {
        $this->fromAddress = env('MAIL_FROM_ADDRESS', 'noreply@example.com');
        $this->fromName = env('MAIL_FROM_NAME', config('app.name', 'Malnu'));

        $this->timeoutSeconds = (int) env('MAIL_TIMEOUT', 10);
        $this->maxRetries = (int) env('MAIL_MAX_RETRIES', 3);
        $this->initialDelayMs = (int) env('MAIL_INITIAL_DELAY_MS', 100);

        $transport = (new Swift_SmtpTransport(
            env('MAIL_HOST', 'smtp.mailtrap.io'),
            (int) env('MAIL_PORT', 2525),
            env('MAIL_ENCRYPTION', 'tls')
        ))
            ->setUsername(env('MAIL_USERNAME', ''))
            ->setPassword(env('MAIL_PASSWORD', ''))
            ->setTimeout($this->timeoutSeconds);

        $this->mailer = new Swift_Mailer($transport);

        $this->logger = $logger ?? \Hyperf\Support\make(LoggerInterface::class);

        $cache = \Hyperf\Support\make(\Hyperf\Cache\Cache::class);

        $this->circuitBreaker = new CircuitBreaker(
            $cache,
            'email_service',
            (int) env('MAIL_CIRCUIT_BREAKER_FAILURES', 5),
            (int) env('MAIL_CIRCUIT_BREAKER_TIMEOUT', 60),
            1
        );

        $this->retryWithBackoff = new RetryWithBackoff(
            $this->maxRetries,
            $this->initialDelayMs,
            2.0,
            5000,
            $this->logger
        );
    }

    public function sendPasswordResetEmail(string $email, string $token): bool
    {
        $appName = env('MAIL_FROM_NAME', config('app.name', 'Malnu'));
        $resetLink = $this->generateResetLink($token);

        $subject = "Password Reset Request - {$appName}";
        $body = $this->getPasswordResetTemplate($resetLink);

        return $this->circuitBreaker->call(function () use ($email, $subject, $body) {
            return $this->retryWithBackoff->execute(function () use ($email, $subject, $body) {
                return $this->sendEmail($email, $subject, $body);
            }, [Swift_TransportException::class, Exception::class]);
        });
    }

    public function getHealthStatus(): array
    {
        $circuitBreakerMetrics = $this->circuitBreaker->getMetrics();

        return [
            'service' => 'email',
            'status' => $circuitBreakerMetrics['state'] === 'closed' ? 'healthy' : 'degraded',
            'circuit_breaker' => $circuitBreakerMetrics,
            'configuration' => [
                'timeout_seconds' => $this->timeoutSeconds,
                'max_retries' => $this->maxRetries,
                'initial_delay_ms' => $this->initialDelayMs,
            ],
        ];
    }

    public function getCircuitBreaker(): CircuitBreaker
    {
        return $this->circuitBreaker;
    }

    private function sendEmail(string $email, string $subject, string $body): bool
    {
        $message = (new Swift_Message($subject))
            ->setFrom([$this->fromAddress => $this->fromName])
            ->setTo([$email])
            ->setBody($body, 'text/html');

        try {
            $result = $this->mailer->send($message);
            $this->logger->info('Password reset email sent successfully', [
                'email' => $email,
                'result' => $result,
            ]);
            return $result > 0;
        } catch (Swift_TransportException $e) {
            $this->logger->error('Failed to send password reset email - transport error', [
                'email' => $email,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            throw $e;
        } catch (Exception $e) {
            $this->logger->error('Failed to send password reset email - unexpected error', [
                'email' => $email,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            throw $e;
        }
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
