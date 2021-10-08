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
    $router->delete('/delete', 'Auth\AdminController@DeleteInfo');
    //
    $router->get('/{id}', 'Auth\AdminController@GetTokens');
    $router->get('/{id}/{token}', 'Auth\AdminController@GetToken');
    $router->get('/stats/{id}/{token}/{date}', 'Auth\AdminController@GetStats');
    $router->get('/logs/{id}/{token}', 'Auth\AdminController@GetLogs');
});