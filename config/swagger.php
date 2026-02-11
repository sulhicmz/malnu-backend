<?php

declare(strict_types=1);

return [
    'scan' => [
        'paths' => [
            'app/Http/Controllers/Api',
        ],
        'exclude' => [
            'vendor',
        ],
        'processors' => [
            'PathProcessor',
            'Processor',
        ],
        'annotations' => [
            'context' => 'api',
        ],
    ],
    'output' => [
        'file' => 'public/swagger.json',
        'yaml' => true,
    ],
    'servers' => [
        [
            'url' => 'http://localhost:9501',
            'description' => 'Local development server',
        ],
    ],
];
