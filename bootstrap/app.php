<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Http\Middleware\Auth\FirewallMiddleware;
use App\Http\Middleware\Auth\JsonBodyValidationFilteringMiddleware;
use App\Http\Middleware\Auth\ParametersSanitizerMiddleware;
use App\Http\Middleware\Auth\RequestLoggingMiddleware;
use App\Providers\AdminLoggingRepositoryServiceProvider;
use App\Providers\MessagesServiceProvider;
use App\Providers\PermissionsServiceProvider;
use jdavidbakr\CloudfrontProxies\CloudfrontProxies;
use LumenRateLimiting\ThrottleRequests;
use Monicahq\Cloudflare\Http\Middleware\TrustProxies;
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Torann\GeoIP\GeoIPServiceProvider;

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->register(Chuckrincon\LumenConfigDiscover\DiscoverServiceProvider::class);

$app->withFacades();
$app->withEloquent();

$app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);
$app->register(Irazasyed\Larasupport\Providers\ArtisanServiceProvider::class);

$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(GeoIPServiceProvider::class);
$app->register(Spatie\ResponseCache\ResponseCacheServiceProvider::class);
$app->register(SwooleTW\Http\LumenServiceProvider::class);
$app->register(Cryental\LaravelHashingSHA256\LaravelHashingSHA256ServiceProvider::class);
$app->register(PermissionsServiceProvider::class);
$app->register(MessagesServiceProvider::class);
$app->register(AdminLoggingRepositoryServiceProvider::class);


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
    \App\Http\Middleware\Auth\TrustProxies::class,
    CloudfrontProxies::class,
    TrustProxies::class,
    FirewallMiddleware::class,
    RequestLoggingMiddleware::class
]);

$app->routeMiddleware([
    'auth.admin' => \App\Http\Middleware\Auth\AdminAuthMiddleware::class,
    'auth.user' => App\Http\Middleware\UserAuthMiddleware::class,
    'sanitizer' => ParametersSanitizerMiddleware::class,
    'filter.json' => JsonBodyValidationFilteringMiddleware::class,
    'cacheResponse' => CacheResponse::class,
    'throttle' => ThrottleRequests::class,
]);

$app->router->group([
    'namespace' => 'App\Http\Controllers',
    'middleware' => 'throttle:api',
], function ($router) {
    require __DIR__ . '/../routes/api.php';
});

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__ . '/../routes/system.php';
});

return $app;
