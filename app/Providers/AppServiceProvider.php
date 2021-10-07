<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
//        app(RateLimiter::class)->for('global', function () {
//            return Limit::perMinute(2500)->by(request()->getClientIp());
//        });
    }
}
