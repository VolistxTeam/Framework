<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders()
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        // channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(RouteServiceProvider::HOME);

        $middleware->append([
            \Volistx\FrameworkKernel\Http\Middleware\FirewallMiddleware::class,
            \Volistx\FrameworkKernel\Http\Middleware\RequestLoggingMiddleware::class,
            \App\Http\Middleware\Locale::class,
        ]);

        $middleware->replace(\Illuminate\Http\Middleware\TrustProxies::class, \App\Http\Middleware\TrustProxies::class);

        $middleware->alias([
            'auth.admin' => \Volistx\FrameworkKernel\Http\Middleware\AdminAuthMiddleware::class,
            'auth.user' => \Volistx\FrameworkKernel\Http\Middleware\UserAuthMiddleware::class,
            'filter.json' => \Volistx\FrameworkKernel\Http\Middleware\JsonBodyValidationFilteringMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
