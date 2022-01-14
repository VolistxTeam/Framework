<?php

return [
    'enabled' => env('RATELIMIT', false),
    'global' => env('RATELIMIT_GLOBAL', 2500)
];