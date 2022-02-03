<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Permissions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'permissions';
    }
}
