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
        $router->group(['middleware' => 'json.filter'], function () use ($router) {
            $router->post('/', 'Auth\AdminController@CreatePersonalToken');
            $router->patch('/{token_id}', 'Auth\AdminController@UpdatePersonalToken');
        });

        $router->patch('/{token_id}/reset', 'Auth\AdminController@ResetPersonalToken');
        $router->delete('/{token_id}', 'Auth\AdminController@DeletePersonalToken');
        $router->get('/', 'Auth\AdminController@GetPersonalTokens');
        $router->get('/{token_id}', 'Auth\AdminController@GetPersonalToken');
        $router->get('/{token_id}/stats', 'Auth\AdminController@GetPersonalTokenStats');
        $router->get('/{token_id}/logs', 'Auth\AdminController@GetPersonalTokenLogs');
    });
});