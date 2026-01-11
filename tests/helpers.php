<?php

declare(strict_types=1);

use Hyperf\Context\ApplicationContext;
use Hyperf\Testing\ModelFactory;

if (! function_exists('factory')) {
    function factory(string $class)
    {
        return ApplicationContext::getContainer()
            ->get(ModelFactory::class)
            ->factory($class);
    }
}
