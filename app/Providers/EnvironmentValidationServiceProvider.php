<?php

declare(strict_types=1);

namespace App\Providers;

use Hyperf\Support\ServiceProvider;
use Psr\Container\ContainerInterface;

class EnvironmentValidationServiceProvider extends ServiceProvider
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct($container);
    }

    public function boot(): void
    {
        $this->validateEnvironmentVariables();
    }

    public function register(): void
    {
    }

    private function validateEnvironmentVariables(): void
    {
        // Get environment from container
        $appEnv = $this->container->get(\Hyperf\Contract\ConfigInterface::class)->get('app.env', 'production');
        
        // Get JWT_SECRET from config
        $jwtSecret = $this->container->get(\Hyperf\Contract\ConfigInterface::class)->get('jwt.secret', '');
        
        if ($appEnv === 'production' && empty($jwtSecret)) {
            throw new \RuntimeException('Critical: JWT_SECRET environment variable is not set. This creates a security vulnerability in production.');
        }
        
        // Additional environment validations can be added here
    }
}