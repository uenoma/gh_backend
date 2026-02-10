<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MobileSuitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the mobile suits index endpoint.
     */
    public function test_index(): void
    {
        // Seed the database
        $this->seed();

        $response = $this->get('/api/mobile-suits');

        $response->assertStatus(200)
                 ->assertJsonCount(3); // Assuming 3 mobile suits from seeder
    }
}
