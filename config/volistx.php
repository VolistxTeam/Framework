<?php

use VolistxTeam\VSkeletonKernel\ValidationRules\IPValidationRule;
use VolistxTeam\VSkeletonKernel\ValidationRules\KeyExpiryValidationRule;
use VolistxTeam\VSkeletonKernel\ValidationRules\RateLimitValidationRule;
use VolistxTeam\VSkeletonKernel\ValidationRules\RequestsCountValidationRule;
use VolistxTeam\VSkeletonKernel\ValidationRules\ValidKeyValidationRule;

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
        ValidKeyValidationRule::class,
        KeyExpiryValidationRule::class,
        IPValidationRule::class,
        RequestsCountValidationRule::class,
        RateLimitValidationRule::class
    ]
];