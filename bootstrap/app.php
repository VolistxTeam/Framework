<?php

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Volistx\FrameworkKernel\Facades\Messages;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders()
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        // channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(AppServiceProvider::HOME);

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
        $exceptions->reportable(function (Throwable $e) {
            //
        });

        $exceptions->renderable(function (NotFoundHttpException $e, $request) {
            return response()->noContent(404);
        });

        $exceptions->renderable(function (MethodNotAllowedHttpException $e, $request) {
            return response()->noContent(405);
        });

        $exceptions->renderable(function (ThrottleRequestsException $e, $request) {
            return response()->json(Messages::E429(), 429);
        });
    })->create();
