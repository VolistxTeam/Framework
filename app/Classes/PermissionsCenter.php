<?php

namespace App\Classes;

use App\Models\AccessKeys;
use App\Models\PersonalKeys;

class PermissionsCenter
{
    public static function checkUserPermission($token, $permissionName): bool
    {
        $accessKey = PersonalKeys::query()->where('key', $token)->first();

        if (empty($accessKey)) {
            return false;
        }

        if (in_array($permissionName, $accessKey->permissions) || in_array('*', $accessKey->permissions)) {
            return true;
        }

        return false;
    }

    public static function checkAdminPermission($token, $permissionName): bool
    {
        $accessKey = AccessKeys::query()->where('token', $token)->first();

        if (empty($accessKey)) {
            return false;
        }

        if (in_array("*", $accessKey->permissions)) {
            return true;
        }

        if (in_array($permissionName, $accessKey->permissions)) {
            return true;
        }

        return false;
    }
}


