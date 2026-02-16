<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\EnvironmentValidator;
use App\Services\Integration\IntegrationManagerService;
use App\Services\Integration\IntegrationService;
use Hypervel\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        (new EnvironmentValidator())->validate();
    }

    public function register(): void
    {
        $this->app->singleton(IntegrationService::class, IntegrationManagerService::class);
    }
}
