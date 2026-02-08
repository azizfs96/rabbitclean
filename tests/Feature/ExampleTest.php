<?php

namespace Tests\Feature;

<<<<<<< HEAD
use Illuminate\Foundation\Testing\RefreshDatabase;
=======
// use Illuminate\Foundation\Testing\RefreshDatabase;
>>>>>>> 7d2250222b1076404c7124acb2f73be59dd3ce1a
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
<<<<<<< HEAD
     *
     * @return void
     */
    public function test_example()
=======
     */
    public function test_the_application_returns_a_successful_response(): void
>>>>>>> 7d2250222b1076404c7124acb2f73be59dd3ce1a
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
