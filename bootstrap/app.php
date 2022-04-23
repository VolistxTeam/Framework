<?php

require_once __DIR__.'/../vendor/autoload.php';

use App\Http\Middleware\TrustProxies;
use LumenRateLimiting\ThrottleRequests;
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Volistx\FrameworkKernel\Providers\AdminLoggingServiceProvider;
use Volistx\FrameworkKernel\Providers\MessagesServiceProvider;
use Volistx\FrameworkKernel\Providers\PermissionsServiceProvider;
use Volistx\FrameworkKernel\Providers\UserLoggingServiceProvider;
use Volistx\FrameworkKernel\VolistxServiceProvider;

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

// Packages to provide compatibility with Laravel and Redis support
$app->register(Illuminate\Redis\RedisServiceProvider::class);

// Default providers of Lumen
$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);

// Kernel providers
$app->register(VolistxServiceProvider::class);
$app->register(PermissionsServiceProvider::class);
$app->register(MessagesServiceProvider::class);
$app->register(AdminLoggingServiceProvider::class);
$app->register(UserLoggingServiceProvider::class);

// Additional libraries
$app->register(Spatie\ResponseCache\ResponseCacheServiceProvider::class);
$app->register(Cryental\StackPath\TrustedProxyServiceProvider::class);
$app->register(\Monicahq\Cloudflare\TrustedProxyServiceProvider::class);
$app->register(Irazasyed\Larasupport\Providers\ArtisanServiceProvider::class);
$app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);
$app->register(\Torann\GeoIP\GeoIPServiceProvider::class);
$app->register(SwooleTW\Http\LumenServiceProvider::class);

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
    Volistx\FrameworkKernel\Http\Middleware\FirewallMiddleware::class,
    Volistx\FrameworkKernel\Http\Middleware\RequestLoggingMiddleware::class,
    Monicahq\Cloudflare\Http\Middleware\TrustProxies::class,
    Cryental\StackPath\Http\Middleware\TrustProxies::class,
    TrustProxies::class,
]);

$app->routeMiddleware([
    'auth.admin'    => Volistx\FrameworkKernel\Http\Middleware\AdminAuthMiddleware::class,
    'auth.user'     => Volistx\FrameworkKernel\Http\Middleware\UserAuthMiddleware::class,
    'filter.json'   => Volistx\FrameworkKernel\Http\Middleware\JsonBodyValidationFilteringMiddleware::class,
    'cacheResponse' => CacheResponse::class,
    'throttle'      => ThrottleRequests::class,
]);

$app->router->group([
    'namespace'  => 'App\Http\Controllers',
    'middleware' => 'throttle:api',
], function ($router) {
    require __DIR__.'/../routes/api.php';
});

return $app;
