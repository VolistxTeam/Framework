<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
Please DO NOT touch any routes here!!
*/

$router->group(['prefix' => 'sys-bin/load-balancer'], function () use ($router) {
    $router->get('/', function () {
        return 'Hi to Load Balancer!';
    });
});

$router->group(['prefix' => 'sys-bin/admin', 'middleware' => 'auth.admin'], function () use ($router) {
    $router->post('/', 'Auth\AdminController@CreateInfo');
    $router->get('/{id}', 'Auth\AdminController@GetTokens');
    $router->get('/{id}/{token}', 'Auth\AdminController@GetToken');
    $router->get('/{id}/{token}/stats', 'Auth\AdminController@GetStats');
    $router->get('/{id}/{token}/logs', 'Auth\AdminController@GetLogs');
    $router->post('/{id}/{token}', 'Auth\AdminController@UpdateInfo');
    $router->delete('/{id}/{token}', 'Auth\AdminController@DeleteInfo');
    $router->post('/{id}/{token}/reset', 'Auth\AdminController@ResetInfo');
});