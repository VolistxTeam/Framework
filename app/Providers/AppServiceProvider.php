<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
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
        app(RateLimiter::class)->for('global', function () {
            return Limit::perMinute(config('ratelimit.global'))->by(request()->getClientIp());
        });
    }
}
