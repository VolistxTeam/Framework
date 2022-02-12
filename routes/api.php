
<?php

/** @var Router $router */

use Laravel\Lumen\Routing\Router;

$router->group(['middleware' => ['auth.user']], function () use ($router) {
    $router->get('/ping', 'ServiceController@Ping');
});