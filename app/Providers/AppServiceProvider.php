<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\EnvironmentValidator;
use Psr\Container\ContainerInterface;
use Hyperf\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    public function boot(): void
    {
        $this->validateEnvironment();
    }

    public function register(): void
    {
    }

    private function validateEnvironment(): void
    {
        $validator = $this->container->get(EnvironmentValidator::class);
        $validator->validate();
    }
}
