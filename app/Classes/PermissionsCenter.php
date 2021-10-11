<?php

namespace App\Classes;

use App\Models\AccessKeys;
use App\Models\PersonalKeys;
use Illuminate\Support\Facades\Hash;

class PermissionsCenter
{
    public static function checkUserPermission($token, $permissionName): bool
    {
        $accessKey = PersonalKeys::query()->where('key', substr($token, 0, 32))
            ->get()->filter(function ($v) use ($token) {
                return Hash::check(substr($token, 32), $v->secret, ['salt' => $v->secret_salt]);
            })->first();

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
        $accessKey = AccessKeys::query()->where('key', substr($token, 0, 32))
            ->get()->filter(function ($v) use ($token) {
                return Hash::check(substr($token, 32), $v->secret, ['salt' => $v->secret_salt]);
            })->first();

        if (empty($accessKey)) {
            return false;
        }

        if (in_array($permissionName, $accessKey->permissions) || in_array('*', $accessKey->permissions)) {
            return true;
        }

        return false;
    }
}


