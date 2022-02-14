<?php

use Volistx\FrameworkKernel\ValidationRules\IPValidationRule;
use Volistx\FrameworkKernel\ValidationRules\KeyExpiryValidationRule;
use Volistx\FrameworkKernel\ValidationRules\RateLimitValidationRule;
use Volistx\FrameworkKernel\ValidationRules\RequestsCountValidationRule;
use Volistx\FrameworkKernel\ValidationRules\ValidKeyValidationRule;

return [
    'firewall' => [
        'ipBlacklist' => [

        ],
    ],
    'logging' => [
        'adminLogMode' => env('LOG_AUTH_ADMIN_CHANNEL', 'local'),
        'adminLogHttpUrl' => env('LOG_AUTH_ADMIN_HTTP_URL'),
        'adminLogHttpToken' => env('LOG_AUTH_ADMIN_HTTP_TOKEN'),
        'userLogMode'       => env('LOG_AUTH_USER_CHANNEL', 'local'),
        'userLogHttpUrl'    => env('LOG_AUTH_USER_HTTP_URL'),
        'userLogHttpToken'  => env('LOG_AUTH_USER_HTTP_TOKEN'),
    ],
    'validators' => [
        ValidKeyValidationRule::class,
        KeyExpiryValidationRule::class,
        IPValidationRule::class,
        RequestsCountValidationRule::class,
        RateLimitValidationRule::class,
    ],
];
