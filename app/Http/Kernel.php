<?php

namespace App\Http;

use App\Http\Middleware\Locale;
use App\Http\Middleware\TrustProxies;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Volistx\FrameworkKernel\Http\Middleware\AdminAuthMiddleware;
use Volistx\FrameworkKernel\Http\Middleware\FirewallMiddleware;
use Volistx\FrameworkKernel\Http\Middleware\JsonBodyValidationFilteringMiddleware;
use Volistx\FrameworkKernel\Http\Middleware\RequestLoggingMiddleware;
use Volistx\FrameworkKernel\Http\Middleware\UserAuthMiddleware;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        ValidatePostSize::class,
        ConvertEmptyStringsToNull::class,
        FirewallMiddleware::class,
        RequestLoggingMiddleware::class,
        Locale::class,
        TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [

    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        'auth.admin' => AdminAuthMiddleware::class,
        'auth.user' => UserAuthMiddleware::class,
        'filter.json' => JsonBodyValidationFilteringMiddleware::class,
        'throttle' => ThrottleRequests::class,
    ];
}
