<?php

namespace App\Classes\Facades;

use Illuminate\Support\Facades\Facade;
use function Symfony\Component\Translation\t;

class Permissions extends  Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'permissions';
    }
}
