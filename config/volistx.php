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
    'geolocation' => [
        'token'         => env('GEOPOINT_API_KEY'),
        'base_url'      => env('GEOPOINT_API_URL'),
        'verification'  => env('GEOPOINT_API_HMAC_VERIFICATION', false),
    ],
    'validators' => [
        Volistx\FrameworkKernel\UserAuthValidationRules\ValidKeyValidationRule::class,
        Volistx\FrameworkKernel\UserAuthValidationRules\PlanExpiryValidationRule::class,
        Volistx\FrameworkKernel\UserAuthValidationRules\KeyExpiryValidationRule::class,
        Volistx\FrameworkKernel\UserAuthValidationRules\IPValidationRule::class,
        Volistx\FrameworkKernel\UserAuthValidationRules\CountryValidationRule::class,
        Volistx\FrameworkKernel\UserAuthValidationRules\RequestsCountValidationRule::class,
        Volistx\FrameworkKernel\UserAuthValidationRules\SubscriptionRateLimitValidationRule::class,
        Volistx\FrameworkKernel\UserAuthValidationRules\IPRateLimitValidationRule::class,
    ],
    'services_permissions' => [
        '*',
    ],
];
