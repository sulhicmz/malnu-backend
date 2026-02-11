<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\EnvironmentValidator;
use Hyperf\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        (new EnvironmentValidator())->validate();
    }

    public function register(): void
    {
    }
}
