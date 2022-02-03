<?php

namespace App\Providers;

use App\Classes\Auth\MessagesCenter;
use Illuminate\Support\ServiceProvider;

class MessagesServiceProvider extends ServiceProvider
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
        $this->app->bind('messages', function () {
            return new MessagesCenter();
        });
    }
}
