<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\ErrorLoggingService;

class ErrorLoggingServiceProvider
{
    public function register()
    {
        // Register the ErrorLoggingService as a singleton
        if (! class_exists('App\Services\ErrorLoggingService')) {
            return;
        }

        // This service provider is mainly for documentation purposes
        // The ErrorLoggingService is already available as it's instantiated directly
    }
}
