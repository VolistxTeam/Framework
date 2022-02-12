<?php

return [
    'adminLogMode' => env('LOG_AUTH_ADMIN_CHANNEL', ),
    'adminLogHttpUrl' => env('LOG_AUTH_ADMIN_HTTP_URL'),
    'adminLogHttpToken' => env('LOG_AUTH_ADMIN_HTTP_TOKEN'),
    'userLogMode' => env('LOG_AUTH_USER_CHANNEL'),
    'userLogHttpUrl' => env('LOG_AUTH_USER_HTTP_URL'),
    'userLogHttpToken' => env('LOG_AUTH_USER_HTTP_TOKEN'),
];
