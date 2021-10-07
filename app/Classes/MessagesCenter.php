<?php

namespace App\Classes;

class MessagesCenter
{
    public static function Error($type, $info)
    {
        return [
            'error' => [
                'type' => $type,
                'info' => $info
            ]
        ];
    }


    public static function E400($error = 'One or more invalid fields were specified using the fields parameters.') {
        return self::Error('xInvalidParameters', $error);
    }

    public static function E500($error = 'Something went wrong with the server. Please try later.') {
        return self::Error('xUnknownError', $error);
    }

    public static function E404($error = 'No item found with provided parameters.') {
        return self::Error('xInvalidItem', $error);
    }
}