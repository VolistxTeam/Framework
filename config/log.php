<?php

return [
    'adminLogMode' => env('LOG_AUTH_ADMIN_CHANNEL', 'remote'),
    'adminLogHttpUrl' => env('LOG_AUTH_ADMIN_HTTP_URL', 'http://127.0.0.1:8000/logs/admins/'),
    'adminLogHttpToken' => env('LOG_AUTH_ADMIN_HTTP_TOKEN', 'Pu4LNDDSpYB7heIcfTnkzFzQEv8tI1n5ON1kJhAjuRaV60M82t73mtzXwdeDiLqb'),
    'userLogMode' => env('LOG_AUTH_USER_CHANNEL', 'remote'),
    'userLogHttpUrl' => env('LOG_AUTH_USER_HTTP_URL', 'http://127.0.0.1:8000/logs/users'),
    'userLogHttpToken' => env('LOG_AUTH_USER_HTTP_TOKEN', 'Pu4LNDDSpYB7heIcfTnkzFzQEv8tI1n5ON1kJhAjuRaV60M82t73mtzXwdeDiLqb'),
];
