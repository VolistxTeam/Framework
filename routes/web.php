<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', 'PageController@Home');

$router->group(['middleware' => ['auth.user', 'cacheResponse']], function () use ($router) {
    $router->get('/example', 'Services\ExampleService@Example');
});

$router->group(['prefix' => 'manage', 'middleware' => 'auth.admin'], function () use ($router) {
    $router->post('/create', 'Auth\AdminController@CreateInfo');
    $router->post('/update', 'Auth\AdminController@UpdateInfo');
    $router->post('/reset', 'Auth\AdminController@ResetInfo');
    $router->post('/delete', 'Auth\AdminController@DeleteInfo');
    $router->post('/stats', 'Auth\AdminController@Stats');
    $router->post('/logs', 'Auth\AdminController@GetLogs');
});