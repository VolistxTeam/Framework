<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Messages extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'messages';
    }
}
