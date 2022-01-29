<?php

namespace App\Classes;

use App\Models\AccessToken;
use App\Models\PersonalToken;
use Illuminate\Support\Facades\Hash;
use Wikimedia\IPSet;

class PermissionsCenter
{
    public function check($key, $permissionName): bool
    {
        $permissions = $key->permissions;

        if (in_array($permissionName, $permissions) || in_array('*', $permissions)) {
            return true;
        }

        return false;
    }
}


