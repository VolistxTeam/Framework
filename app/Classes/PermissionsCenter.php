<?php

namespace App\Classes;

use App\Models\PersonalKeys;

class PermissionsCenter
{
    public static function checkUserPermission($token,$key): bool
    {
        $accessKey = PersonalKeys::query()->where('key', $token)->first();

        if (empty($accessKey)) {
            return false;
        }

        if (in_array($key, $accessKey->permissions) || in_array('*', $accessKey->permissions)) {
            return true;
        }

        return false;
    }
}


