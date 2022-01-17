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
        $router->get('/', 'Auth\SubscriptionController@GetSubscriptions');
        $router->get('/{subscription_id}', 'Auth\SubscriptionController@GetSubscription');
        $router->delete('/{subscription_id}', 'Auth\SubscriptionController@DeleteSubscription');
    });

    $router->group(['prefix' => 'admin/token', 'middleware' => ['auth.admin','filter.json']], function () use ($router) {
        $router->get('/{subscriptionID}', 'Auth\PersonalTokenControllers@GetPersonalTokens');
        $router->get('/{subscriptionID}/{tokenID}', 'Auth\PersonalTokenControllers@GetPersonalToken');
        $router->post('/', 'Auth\PersonalTokenControllers@CreatePersonalToken');
        $router->patch('/{subscriptionID}/{tokenID}', 'Auth\PersonalTokenControllers@UpdatePersonalToken');
        $router->delete('/{subscriptionID}/{tokenID}', 'Auth\PersonalTokenControllers@DeletePersonalToken');
    });
});