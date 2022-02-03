<?php

namespace App\Classes\Auth;

use JetBrains\PhpStorm\Pure;

class MessagesCenter
{
    #[Pure] public function E400($error = 'One or more invalid fields were specified using the fields parameters.'): array
    {
        return self::Error('xInvalidParameters', $error);
    }

    public function Error($type, $info)
    {
        return [
            'error' => [
                'type' => $type,
                'info' => $info
            ]
        ];
    }

    #[Pure] public function E401($error = 'Insufficient permissions to perform this request.'): array
    {
        return self::Error('xUnauthorized', $error);
    }

    #[Pure] public function E403($error = 'Forbidden request.'): array
    {
        return self::Error('xForbidden', $error);
    }

    #[Pure] public function E404($error = 'No item found with provided parameters.'): array
    {
        return self::Error('xNotFound', $error);
    }

    #[Pure] public function E429($error = 'Too many requests.'): array
    {
        return self::Error('xManyRequests', $error);
    }

    #[Pure] public function E500($error = 'Something went wrong with the server. Please try later.'): array
    {
        return self::Error('xUnknownError', $error);
    }
}