<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    #[Test]
    public function the_application_returns_a_successful_response()
    {
        $response = $this->get('/');

        $response->assertStatus(404);
    }
}
