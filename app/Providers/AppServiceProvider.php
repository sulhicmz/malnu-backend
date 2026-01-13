<?php

declare(strict_types=1);

namespace App\Providers;

use Hyperf\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->validateEnvironmentVariables();
    }

    public function register(): void
    {
    }

    private function validateEnvironmentVariables(): void
    {
        $appEnv = config('app.env', 'production');
        $jwtSecret = config('jwt.secret', '');

        $insecurePatterns = [
            'your-secret-key-here',
            'secret',
            'password',
            'jwt-secret',
            'change-me',
            'changeme',
            'test-secret',
            'testing-secret',
        ];

        $isTesting = in_array($appEnv, ['testing', 'test']);

        if (!$isTesting) {
            if (empty($jwtSecret)) {
                throw new \RuntimeException(
                    'JWT_SECRET environment variable is not set. ' .
                    'Please generate a secure secret using: openssl rand -hex 32'
                );
            }

            foreach ($insecurePatterns as $pattern) {
                if (stripos($jwtSecret, $pattern) !== false) {
                    throw new \RuntimeException(
                        'JWT_SECRET contains an insecure or placeholder value. ' .
                        'Please generate a secure secret using: openssl rand -hex 32'
                    );
                }
            }

            if (strlen($jwtSecret) < 32) {
                throw new \RuntimeException(
                    'JWT_SECRET must be at least 32 characters long. ' .
                    'Please generate a secure secret using: openssl rand -hex 32'
                );
            }
        }
    }
}
