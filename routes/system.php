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
    $router->post('/create', 'Auth\AdminController@CreateInfo');
    $router->post('/update', 'Auth\AdminController@UpdateInfo');
    $router->post('/reset', 'Auth\AdminController@ResetInfo');
    $router->post('/delete', 'Auth\AdminController@DeleteInfo');
    $router->post('/stats', 'Auth\AdminController@Stats');
    $router->post('/logs', 'Auth\AdminController@GetLogs');
    $router->post('/list', 'Auth\AdminController@GetTokens');
});