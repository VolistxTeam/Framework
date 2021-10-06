<?php

namespace App\Classes;

class MessagesCenter
{
    public static function Error($key , $info) {
        return [
            'error' => [
                'type' => $key,
                'info' => $info
            ]];
    }

    public static function E400($error = 'One or more invalid fields were specified using the fields parameters.') {
        return self::Error('xBadRequest', $error);
    }

    public static function E500($error = 'Something went wrong with the server. Please try later.') {
        return self::Error('xUnknownError', $error);
    }
}