<?php

declare(strict_types=1);

return [
    'error_codes' => [
        'AUTH' => [
            'INVALID_CREDENTIALS' => 'AUTH_001',
            'INVALID_TOKEN' => 'AUTH_002',
            'TOKEN_EXPIRED' => 'AUTH_003',
            'TOKEN_BLACKLISTED' => 'AUTH_004',
            'UNAUTHORIZED' => 'AUTH_005',
            'FORBIDDEN' => 'AUTH_006',
            'USER_NOT_FOUND' => 'AUTH_007',
            'USER_ALREADY_EXISTS' => 'AUTH_008',
            'PASSWORD_RESET_INVALID' => 'AUTH_009',
            'PASSWORD_RESET_EXPIRED' => 'AUTH_010',
        ],

        'VALIDATION' => [
            'FAILED' => 'VAL_001',
            'REQUIRED_FIELD' => 'VAL_002',
            'INVALID_FORMAT' => 'VAL_003',
            'INVALID_TYPE' => 'VAL_004',
            'INVALID_LENGTH' => 'VAL_005',
            'INVALID_RANGE' => 'VAL_006',
            'INVALID_DATE' => 'VAL_007',
            'DUPLICATE_ENTRY' => 'VAL_008',
        ],

        'RESOURCE' => [
            'NOT_FOUND' => 'RES_001',
            'CREATION_FAILED' => 'RES_002',
            'UPDATE_FAILED' => 'RES_003',
            'DELETION_FAILED' => 'RES_004',
            'LOCKED' => 'RES_005',
            'INSUFFICIENT_BALANCE' => 'RES_006',
            'ALREADY_PROCESSED' => 'RES_007',
        ],

        'SERVER' => [
            'INTERNAL_ERROR' => 'SRV_001',
            'DATABASE_ERROR' => 'SRV_002',
            'EXTERNAL_SERVICE_ERROR' => 'SRV_003',
            'TIMEOUT' => 'SRV_004',
            'MAINTENANCE' => 'SRV_005',
        ],

        'RATE_LIMIT' => [
            'EXCEEDED' => 'RTL_001',
        ],
    ],

    'http_status_codes' => [
        'AUTH_001' => 401,
        'AUTH_002' => 401,
        'AUTH_003' => 401,
        'AUTH_004' => 401,
        'AUTH_005' => 401,
        'AUTH_006' => 403,
        'AUTH_007' => 404,
        'AUTH_008' => 409,
        'AUTH_009' => 400,
        'AUTH_010' => 400,

        'VAL_001' => 422,
        'VAL_002' => 422,
        'VAL_003' => 422,
        'VAL_004' => 422,
        'VAL_005' => 422,
        'VAL_006' => 422,
        'VAL_007' => 422,
        'VAL_008' => 409,

        'RES_001' => 404,
        'RES_002' => 400,
        'RES_003' => 400,
        'RES_004' => 400,
        'RES_005' => 423,
        'RES_006' => 400,
        'RES_007' => 409,

        'SRV_001' => 500,
        'SRV_002' => 500,
        'SRV_003' => 502,
        'SRV_004' => 504,
        'SRV_005' => 503,

        'RTL_001' => 429,
    ],
];
