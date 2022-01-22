
<?php

/** @var Router $router */

use Laravel\Lumen\Routing\Router;

$router->group(['middleware' => ['auth.user', 'cacheResponse:5']], function () use ($router) {
    $router->get('/example', 'Services\ExampleService@Example');
});