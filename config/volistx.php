<?php

return [
    'firewall'         => [
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
        Volistx\FrameworkKernel\UserAuthValidationRules\ValidKeyValidationRule::class,
        Volistx\FrameworkKernel\UserAuthValidationRules\KeyExpiryValidationRule::class,
        Volistx\FrameworkKernel\UserAuthValidationRules\IPValidationRule::class,
        Volistx\FrameworkKernel\UserAuthValidationRules\CountryValidationRule::class,
        Volistx\FrameworkKernel\UserAuthValidationRules\RequestsCountValidationRule::class,
        Volistx\FrameworkKernel\UserAuthValidationRules\RateLimitValidationRule::class,
    ],
    'geoPoint' =>[
        'BaseUrl' => env('GEOPOINT_API_URL'),
        "APIToken" => env('GEOPOINT_API_KEY')
    ],
    'services_permissions'=> [
        '*',
    ],
];
