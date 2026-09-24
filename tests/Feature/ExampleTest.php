<?php

namespace Tests\Feature;

use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Hotel::create([
            'name' => 'Grand Horizon Hotel',
            'slug' => 'grand-horizon',
            'status' => 'active',
        ]);

        $response = $this->get('/facilities');

        $response->assertStatus(200);
    }
}
