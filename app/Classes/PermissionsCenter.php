<?php

namespace App\Classes;

use App\Models\AccessKey;
use App\Models\PersonalKey;
use Illuminate\Support\Facades\Hash;

class PermissionsCenter
{
    public static function checkUserPermission($token, $permissionName): bool
    {
        $accessKey = self::getUserAuthKey($token);

        if (empty($accessKey)) {
            return false;
        }

        if (in_array($permissionName, $accessKey->permissions) || in_array('*', $accessKey->permissions)) {
            return true;
        }

        return false;
    }

    public static function getUserAuthKey($token)
    {
        return PersonalKey::query()->where('key', substr($token, 0, 32))
            ->get()->filter(function ($v) use ($token) {
                return Hash::check(substr($token, 32), $v->secret, ['salt' => $v->secret_salt]);
            })->first();
    }

    public static function checkAdminPermission($token, $permissionName): bool
    {
        $accessKey = self::getAdminAuthKey($token);

        if (empty($accessKey)) {
            return false;
        }

        if (in_array($permissionName, $accessKey->permissions) || in_array('*', $accessKey->permissions)) {
            return true;
        }

        return false;
    }

    public static function getAdminAuthKey($token)
    {
        return AccessKey::query()->where('key', substr($token, 0, 32))
            ->get()->filter(function ($v) use ($token) {
                return Hash::check(substr($token, 32), $v->secret, ['salt' => $v->secret_salt]);
            })->first();
    }
}


