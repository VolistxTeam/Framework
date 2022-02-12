<?php

namespace App\Providers;

use App\Repositories\Auth\Interfaces\IAdminLogRepository;
use App\Repositories\Auth\LocalAdminLogRepository;
use App\Repositories\Auth\RemoteAdminLogRepository;
use Illuminate\Support\ServiceProvider;

class AdminLoggingRepositoryServiceProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        if (config('log.adminLogMode') === 'local') {
            $this->app->bind(IAdminLogRepository::class, LocalAdminLogRepository::class);
        } else {
            $this->app->bind(IAdminLogRepository::class, RemoteAdminLogRepository::class);
        }
    }
}