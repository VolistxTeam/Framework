<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
Please DO NOT touch any routes here!!
*/

$router->group(['prefix' => 'sys-bin'], function () use ($router) {
    $router->get('/ping', function () {
        return response('Hi!');
    });

    $router->group(['prefix' => 'admin', 'middleware' => 'auth.admin'], function () use ($router) {
        $router->get('/{id}', 'Auth\AdminController@GetTokens');
        $router->get('/{id}/{token}', 'Auth\AdminController@GetToken');
        $router->get('/{id}/{token}/stats', 'Auth\AdminController@GetStats');
        $router->get('/{id}/{token}/logs', 'Auth\AdminController@GetLogs');
        $router->post('/', 'Auth\AdminController@CreateInfo');
        $router->post('/{id}/{token}', 'Auth\AdminController@UpdateInfo');
        $router->patch('/{id}/{token}', 'Auth\AdminController@ResetInfo');
        $router->delete('/{id}/{token}', 'Auth\AdminController@DeleteInfo');
    });
});