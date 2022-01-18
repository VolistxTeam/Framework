<?php

/** @var Router $router */

/*
Please DO NOT touch any routes here!!
*/

use Laravel\Lumen\Routing\Router;


$router->group(['prefix' => 'sys-bin'], function () use ($router) {
    $router->group(['prefix' => 'admin/subscriptions', 'middleware' => 'auth.admin'], function () use ($router) {
        $router->group(['middleware' => ['filter.json']], function () use ($router) {
            $router->post('/', 'Auth\SubscriptionController@CreateSubscription');
            $router->put('/{subscription_id}', 'Auth\SubscriptionController@UpdateSubscription');
        });
        $router->delete('/{subscription_id}', 'Auth\SubscriptionController@DeleteSubscription');
        $router->get('/', 'Auth\SubscriptionController@GetSubscriptions');
        $router->get('/{subscription_id}', 'Auth\SubscriptionController@GetSubscription');
        $router->get('/{subscription_id}/logs', 'Auth\SubscriptionController@GetSubscriptionLogs');
    });

    $router->group(['prefix' => 'admin/personal-tokens', 'middleware' => 'auth.admin'], function () use ($router) {
        $router->group(['middleware' => ['filter.json']], function () use ($router) {
            $router->post('/{subscription_id}', 'Auth\PersonalTokenController@CreatePersonalToken');
            $router->put('/{subscription_id}/{token_id}', 'Auth\PersonalTokenController@UpdatePersonalToken');
        });
        $router->delete('/{subscription_id}/{token_id}', 'Auth\PersonalTokenController@DeletePersonalToken');
        $router->put('/{subscription_id}/{token_id}/reset', 'Auth\PersonalTokenController@ResetPersonalToken');
        $router->get('/{subscription_id}/{token_id}', 'Auth\PersonalTokenController@GetPersonalToken');
        $router->get('/{subscription_id}', 'Auth\PersonalTokenController@GetPersonalTokens');
        $router->get('/{subscription_id}/{token_id}/logs', 'Auth\PersonalTokenController@GetPersonalTokenLogs');
    });
});