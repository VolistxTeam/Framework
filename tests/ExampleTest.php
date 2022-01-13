<?php

use Laravel\Lumen\Testing\TestCase as BaseTestCase;

class ExampleTest extends BaseTestCase
{
    public function createApplication(): \Laravel\Lumen\Application
    {
        return require __DIR__.'/../bootstrap/app.php';
    }
}
