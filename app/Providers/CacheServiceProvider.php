<?php

declare(strict_types=1);

namespace App\Providers;

use Hypervel\Cache\CacheManager;
use Hypervel\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register any cache-related bindings
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Add cache warming functionality if needed
        $this->warmFrequentlyUsedCache();
    }

    /**
     * Warm up frequently used cache entries
     */
    private function warmFrequentlyUsedCache(): void
    {
        // This method can be used to pre-populate frequently accessed cache entries
        // For example, warming up user roles, permissions, or other static data
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [];
    }
}