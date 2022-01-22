<?php

namespace App\Classes;

class MessagesCenter
{
    public static function E400($error = 'One or more invalid fields were specified using the fields parameters.')
    {
        return self::Error('xInvalidParameters', $error);
    }

    public static function Error($type, $info)
    {
        return [
            'error' => [
                'type' => $type,
                'info' => $info
            ]
        ];
    }

    public static function E401($error = 'Insufficient permissions to perform this request.')
    {
        return self::Error('xUnauthorized', $error);
    }

    public static function E403($error = 'Forbidden request.')
    {
        return self::Error('xForbidden', $error);
    }

    public static function E404($error = 'No item found with provided parameters.')
    {
        return self::Error('xNotFound', $error);
    }

    public static function E429($error = 'Too many requests.')
    {
        return self::Error('xManyRequests', $error);
    }

    public static function E500($error = 'Something went wrong with the server. Please try later.')
    {
        return self::Error('xUnknownError', $error);
    }


}