<?php

<<<<<<< HEAD
namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}
=======
test('that true is true', function () {
    expect(true)->toBeTrue();
});
>>>>>>> 3wayfusionn-(use-this)
