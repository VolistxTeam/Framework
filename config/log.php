<?php

return [
    'adminLogMode' => env('LOG_AUTH_ADMIN_CHANNEL', 'remote'),
    'adminLogHttpUrl' => env('LOG_AUTH_ADMIN_HTTP_URL', 'http://127.0.0.1:8000/logs/admins'),
    'adminLogHttpToken' => env('LOG_AUTH_ADMIN_HTTP_TOKEN', 'c5P6etI9KPnNCeQDcy8EH6u7p62WLYDafE3ICSx9NVtjRPdLdh1ARwzdJs8LIlX1'),
    'userLogMode' => env('LOG_AUTH_USER_CHANNEL', 'remote'),
    'userLogHttpUrl' => env('LOG_AUTH_USER_HTTP_URL', 'http://127.0.0.1:8000/logs/users'),
    'userLogHttpToken' => env('LOG_AUTH_USER_HTTP_TOKEN', 'c5P6etI9KPnNCeQDcy8EH6u7p62WLYDafE3ICSx9NVtjRPdLdh1ARwzdJs8LIlX1'),
];
