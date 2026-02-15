<?php

declare(strict_types=1);

use Hypervel\Foundation\Application;
use Hypervel\Database\Model\Factories\Factory;

if (! function_exists('factory')) {
    function factory(string $class)
    {
        return ApplicationContext::getContainer()
            ->get(ModelFactory::class)
            ->factory($class);
    }
}
