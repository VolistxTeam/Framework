<?php

return [
    'firewall' => [
        'ipBlacklist' => [

        ]
    ],
    'logging' => [
        'adminLogMode' => env('LOG_AUTH_ADMIN_CHANNEL', 'local'),
        'adminLogHttpUrl' => env('LOG_AUTH_ADMIN_HTTP_URL'),
        'adminLogHttpToken' => env('LOG_AUTH_ADMIN_HTTP_TOKEN'),
        'userLogMode' => env('LOG_AUTH_USER_CHANNEL', 'local'),
        'userLogHttpUrl' => env('LOG_AUTH_USER_HTTP_URL'),
        'userLogHttpToken' => env('LOG_AUTH_USER_HTTP_TOKEN'),
    ],
    'validators' => [
        \VolistxTeam\VSkeletonKernel\ValidationRules\ValidKeyValidationRule::class,
        \VolistxTeam\VSkeletonKernel\ValidationRules\KeyExpiryValidationRule::class,
        \VolistxTeam\VSkeletonKernel\ValidationRules\IPValidationRule::class,
        \VolistxTeam\VSkeletonKernel\ValidationRules\RequestsCountValidationRule::class,
        \VolistxTeam\VSkeletonKernel\ValidationRules\RateLimitValidationRule::class
    ]
];