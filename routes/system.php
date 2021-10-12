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
        $router->get('/{userID}', 'Auth\AdminController@GetTokens');
        $router->get('/{userID}/{keyID}', 'Auth\AdminController@GetToken');
        $router->get('/{userID}/{keyID}/stats', 'Auth\AdminController@GetStats');
        $router->get('/{userID}/{keyID}/logs', 'Auth\AdminController@GetLogs');
        $router->post('/', 'Auth\AdminController@CreateInfo');
        $router->post('/{userID}/{keyID}', 'Auth\AdminController@UpdateInfo');
        $router->patch('/{userID}/{keyID}', 'Auth\AdminController@ResetInfo');
        $router->delete('/{userID}/{keyID}', 'Auth\AdminController@DeleteInfo');
    });
});