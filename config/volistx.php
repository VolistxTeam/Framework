<?php

return [
    'firewall' => [
        'blacklist' => [

        ],
    ],
    'logging' => [
        'adminLogMode'      => env('LOG_AUTH_ADMIN_CHANNEL', 'local'),
        'adminLogHttpUrl'   => env('LOG_AUTH_ADMIN_HTTP_URL'),
        'adminLogHttpToken' => env('LOG_AUTH_ADMIN_HTTP_TOKEN'),
        'userLogMode'       => env('LOG_AUTH_USER_CHANNEL', 'local'),
        'userLogHttpUrl'    => env('LOG_AUTH_USER_HTTP_URL'),
        'userLogHttpToken'  => env('LOG_AUTH_USER_HTTP_TOKEN'),
    ],
    'validators' => [
        Volistx\FrameworkKernel\ValidationRules\ValidKeyValidationRule::class,
        Volistx\FrameworkKernel\ValidationRules\KeyExpiryValidationRule::class,
        Volistx\FrameworkKernel\ValidationRules\IPValidationRule::class,
        Volistx\FrameworkKernel\ValidationRules\RequestsCountValidationRule::class,
        Volistx\FrameworkKernel\ValidationRules\RateLimitValidationRule::class,
    ],
    'services_permissions'=> [
        '*',
    ],
];
