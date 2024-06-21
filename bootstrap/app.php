<?php

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Symfony\Component\HttpFoundation\Request as RequestAlias;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Volistx\FrameworkKernel\Facades\Messages;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders()
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: ''
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(remove: [
            StartSession::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            ShareErrorsFromSession::class,
            ValidateCsrfToken::class,
            SubstituteBindings::class
        ]);

        $middleware->append([
            \Volistx\FrameworkKernel\Http\Middleware\FirewallMiddleware::class,
            \Volistx\FrameworkKernel\Http\Middleware\RequestLoggingMiddleware::class,
            \App\Http\Middleware\Locale::class,
            \App\Http\Middleware\Cors::class,
        ]);

        $middleware->replace(
            \Illuminate\Http\Middleware\TrustProxies::class,
            \Monicahq\Cloudflare\Http\Middleware\TrustProxies::class
        );

        $middleware->trustProxies(at: [
            '127.0.0.1'
        ]);

        $middleware->trustProxies(headers: RequestAlias::HEADER_X_FORWARDED_FOR |
            RequestAlias::HEADER_X_FORWARDED_HOST |
            RequestAlias::HEADER_X_FORWARDED_PORT |
            RequestAlias::HEADER_X_FORWARDED_PROTO |
            RequestAlias::HEADER_X_FORWARDED_AWS_ELB
        );

        $middleware->alias([
            'auth.admin' => \Volistx\FrameworkKernel\Http\Middleware\AdminAuthMiddleware::class,
            'auth.user' => \Volistx\FrameworkKernel\Http\Middleware\UserAuthMiddleware::class,
            'filter.json' => \Volistx\FrameworkKernel\Http\Middleware\JsonBodyValidationFilteringMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e) {
            //
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            return response()->json(Messages::E404(), 404);
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            return response()->json(Messages::E404(), 404);
        });

        $exceptions->render(function (ThrottleRequestsException $e, $request) {
            return response()->json(Messages::E429(), 429);
        });

        $exceptions->render(function (ErrorException $e, Request $request) {
            return response()->json(Messages::E500(), 500);
        });
    })->create();
