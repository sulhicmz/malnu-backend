<?php

declare(strict_types=1);

namespace App\Services;

use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class EmailService
{
    private Swift_Mailer $mailer;
    private string $fromAddress;
    private string $fromName;

    public function __construct()
    {
        $this->fromAddress = env('MAIL_FROM_ADDRESS', 'noreply@example.com');
        $this->fromName = env('MAIL_FROM_NAME', config('app.name', 'Malnu'));

        $transport = (new Swift_SmtpTransport(
            env('MAIL_HOST', 'smtp.mailtrap.io'),
            (int) env('MAIL_PORT', 2525),
            env('MAIL_ENCRYPTION', 'tls')
        ))
            ->setUsername(env('MAIL_USERNAME', ''))
            ->setPassword(env('MAIL_PASSWORD', ''));

        $this->mailer = new Swift_Mailer($transport);
    }

    public function sendPasswordResetEmail(string $email, string $token): bool
    {
        $appName = env('MAIL_FROM_NAME', config('app.name', 'Malnu'));
        $resetLink = $this->generateResetLink($token);

        $subject = "Password Reset Request - {$appName}";
        $body = $this->getPasswordResetTemplate($resetLink);

        $message = (new Swift_Message($subject))
            ->setFrom([$this->fromAddress => $this->fromName])
            ->setTo([$email])
            ->setBody($body, 'text/html');

        try {
            $result = $this->mailer->send($message);
            \Hyperf\Support\make(\Psr\Log\LoggerInterface::class)->info('Password reset email sent', [
                'email' => $email,
                'result' => $result,
            ]);
            return $result > 0;
        } catch (\Exception $e) {
            \Hyperf\Support\make(\Psr\Log\LoggerInterface::class)->error('Failed to send password reset email', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return false;
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
            <p>Click the button below to reset your password:</p>
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

    public function sendNotificationEmail(string $to, string $subject, string $htmlBody, ?string $plainText = null): bool
    {
        try {
            $message = (new Swift_Message($subject))
                ->setFrom([$this->fromAddress => $this->fromName])
                ->setTo([$to])
                ->setBody($htmlBody, 'text/html');

            if ($plainText) {
                $message->addPart($plainText, 'text/plain');
            }

            $result = $this->mailer->send($message);

            \Hyperf\Support\make(\Psr\Log\LoggerInterface::class)->info('Notification email sent', [
                'to' => $to,
                'subject' => $subject,
                'result' => $result,
            ]);

            return $result > 0;
        } catch (\Exception $e) {
            \Hyperf\Support\make(\Psr\Log\LoggerInterface::class)->error('Failed to send notification email', [
                'to' => $to,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function getSchoolEmailTemplate(): string
    {
        $appName = env('MAIL_FROM_NAME', config('app.name', 'Malnu'));

        return "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>{subject}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .button { display: inline-block; padding: 12px 24px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-top: 20px; }
        .footer { padding: 20px; text-align: center; color: #666; font-size: 12px; }
        .info-box { background-color: #e8f5e9; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .success-box { background-color: #d4edda; padding: 15px; border-radius: 4px; margin: 15px 0; }
        .warning-box { background-color: #fff3cd; padding: 15px; border-radius: 4px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>{$appName}</h1>
        </div>
        <div class='content'>
            {content}
        </div>
        <div class='footer'>
            <p>&copy; " . date('Y') . " {$appName}. All rights reserved.</p>
            <p>This is an automated email. Please do not reply to this message.</p>
        </div>
    </div>
</body>
</html>";
    }

    public function renderTemplate(string $htmlTemplate, array $variables = []): string
    {
        $content = $htmlTemplate;

        foreach ($variables as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
        }

        return $content;
    }
}
