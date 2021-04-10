<?php

use Spatie\ResponseCache\Middlewares\CacheResponse;

require_once __DIR__ . '/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();
$app->withEloquent();

$app->register(App\Providers\AuthServiceProvider::class);
$app->register(\Torann\GeoIP\GeoIPServiceProvider::class);
$app->register(TwigBridge\ServiceProvider::class);
$app->register(Spatie\ResponseCache\ResponseCacheServiceProvider::class);

if (!class_exists('Twig')) {
    class_alias('TwigBridge\Facade\Twig', 'Twig');
}

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->configure('app');

$app->middleware([
    App\Http\Middleware\FirewallMiddleware::class,
]);

$app->routeMiddleware([
    'auth.user' => App\Http\Middleware\UserAuthMiddleware::class,
    'auth.admin' => App\Http\Middleware\AdminAuthMiddleware::class,
    'cacheResponse' => CacheResponse::class,
]);

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__ . '/../routes/web.php';
});

collect(scandir(__DIR__ . '/../config'))->each(function ($item) use ($app) {
    $app->configure(basename($item, '.php'));
});

return $app;
