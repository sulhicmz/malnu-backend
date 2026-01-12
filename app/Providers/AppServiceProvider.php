<?php

declare(strict_types=1);

namespace App\Providers;

use Hypervel\Support\ServiceProvider;
use Throwable;

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
        try {
            $validator = make(\App\Services\EnvironmentValidator::class);
            $validator->validate();
        } catch (Throwable $e) {
            if (env('APP_ENV') !== 'testing') {
                throw $e;
            }
        }
    }
}
