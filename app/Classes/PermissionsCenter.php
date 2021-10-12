<?php

namespace App\Classes;

use App\Models\AccessKeys;
use App\Models\PersonalKeys;
use Illuminate\Support\Facades\Hash;
use function Symfony\Component\Translation\t;

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

    public static function getUserAuthKey($token)
    {
        return PersonalKeys::query()->where('key', substr($token, 0, 32))
            ->get()->filter(function ($v) use ($token) {
                return Hash::check(substr($token, 32), $v->secret, ['salt' => $v->secret_salt]);
            })->first();
    }

    public static function getAdminAuthKey($token)
    {
        return AccessKeys::query()->where('key', substr($token, 0, 32))
            ->get()->filter(function ($v) use ($token) {
                return Hash::check(substr($token, 32), $v->secret, ['salt' => $v->secret_salt]);
            })->first();
    }
}


