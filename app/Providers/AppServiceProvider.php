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
        // Set Rate Limiting
        if (config('ratelimit.enabled', false)) {
            app(RateLimiter::class)->for('api', function () {
                return Limit::perMinute(config('ratelimit.api'))->by(request()->getClientIp());
            });
        }
    }
}
