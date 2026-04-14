<?php

<<<<<<< HEAD
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_successful_response(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }
}
=======
test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});
>>>>>>> 3wayfusionn-(use-this)
