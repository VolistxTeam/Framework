<?php

namespace App\Classes;

use App\Models\AccessKey;
use App\Models\PersonalToken;
use Illuminate\Support\Facades\Hash;

class PermissionsCenter
{
    public static function checkPermission($key, $permissionName): bool
    {
        if (empty($key)) {
            return false;
        }

        if (in_array($permissionName, $key->permissions) || in_array('*', $key->permissions)) {
            return true;
        }

        return false;
    }

    public static function getUserAuthKey($token)
    {
        return PersonalToken::query()->where('key', substr($token, 0, 32))
            ->get()->filter(function ($v) use ($token) {
                return Hash::check(substr($token, 32), $v->secret, ['salt' => $v->secret_salt]);
            })->first();
    }

    public static function getAdminAuthKey($token)
    {
        return AccessKey::query()->where('key', substr($token, 0, 32))
            ->get()->filter(function ($v) use ($token) {
                return Hash::check(substr($token, 32), $v->secret, ['salt' => $v->secret_salt]);
            })->first();
    }
}


