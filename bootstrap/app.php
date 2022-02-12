<?php

require_once __DIR__ . '/../vendor/autoload.php';

use jdavidbakr\CloudfrontProxies\CloudfrontProxies;
use LumenRateLimiting\ThrottleRequests;
use Monicahq\Cloudflare\Http\Middleware\TrustProxies;
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Torann\GeoIP\GeoIPServiceProvider;
use VolistxTeam\VSkeletonKernel\Providers\AdminLoggingRepositoryServiceProvider;
use VolistxTeam\VSkeletonKernel\Providers\MessagesServiceProvider;
use VolistxTeam\VSkeletonKernel\Providers\PermissionsServiceProvider;

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
$app->register(\VolistxTeam\VSkeletonKernel\VolistxServiceProvider::class);
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
    VolistxTeam\VSkeletonKernel\Http\Middleware\TrustProxies::class,
    CloudfrontProxies::class,
    TrustProxies::class,
    VolistxTeam\VSkeletonKernel\Http\Middleware\FirewallMiddleware::class,
    VolistxTeam\VSkeletonKernel\Http\Middleware\RequestLoggingMiddleware::class,
]);

$app->routeMiddleware([
    'auth.admin' => VolistxTeam\VSkeletonKernel\Http\Middleware\AdminAuthMiddleware::class,
    'auth.user' => VolistxTeam\VSkeletonKernel\Http\Middleware\UserAuthMiddleware::class,
    'sanitizer' => VolistxTeam\VSkeletonKernel\Http\Middleware\ParametersSanitizerMiddleware::class,
    'filter.json' => VolistxTeam\VSkeletonKernel\Http\Middleware\JsonBodyValidationFilteringMiddleware::class,
    'cacheResponse' => CacheResponse::class,
    'throttle' => ThrottleRequests::class,
]);


return $app;