<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use function Symfony\Component\Translation\t;

class Messages extends  Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'messages';
    }
}
