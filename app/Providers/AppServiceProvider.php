<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\EnvironmentValidator;
use Hyperf\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->validateEnvironment();
    }

    public function register(): void
    {
    }

    private function validateEnvironment(): void
    {
        if ($this->shouldSkipValidation()) {
            return;
        }

        $validator = new EnvironmentValidator();
        $validator->validate();
    }

    private function shouldSkipValidation(): bool
    {
        $enabled = config('app.env_validation_enabled', true);
        $env = config('app.env', 'local');

        return !$enabled || $env === 'testing';
    }
}
