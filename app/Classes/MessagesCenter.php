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
}