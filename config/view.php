<?php

declare(strict_types=1);

use Hypervel\View\Mode;
use Hypervel\View\Engines\PhpEngine;

return [
    'engine' => HyperfViewEngine::class,
    'mode' => Mode::SYNC,
    'config' => [
        'view_path' => base_path('resources/views'),
        'cache_path' => storage_path('framework/views'),
    ],
    'event' => [
        'enable' => false,
    ],
    'components' => [
    ],
];
