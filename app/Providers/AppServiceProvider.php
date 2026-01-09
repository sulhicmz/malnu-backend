<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\EnvironmentValidator;
use Hypervel\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->validateEnvironment();
    }

    public function register(): void
    {
    }

    protected function validateEnvironment(): void
    {
        $validationEnabled = $_ENV['ENV_VALIDATION_ENABLED'] ?? 'true';

        if ($validationEnabled === 'false' || $validationEnabled === '0') {
            return;
        }

        $appEnv = $_ENV['APP_ENV'] ?? 'production';

        if ($appEnv === 'testing') {
            return;
        }

        $validator = new EnvironmentValidator();
        $validator->validate();
    }
}
