<?php

namespace App\Providers;

use App\Classes\PermissionsCenter;
use Illuminate\Support\ServiceProvider;

class PermissionsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('permissions',function(){
            return new PermissionsCenter();
        });
    }
}
