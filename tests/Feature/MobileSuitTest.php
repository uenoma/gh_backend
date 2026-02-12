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

    /**
     * Test the mobile suits store endpoint.
     */
    public function test_store(): void
    {
        $data = [
            'data_id' => 'test_data_id',
            'ms_name' => 'Test Mobile Suit',
            'ms_data' => ['key' => 'value'],
        ];

        $response = $this->postJson('/api/mobile-suits', $data);

        $response->assertStatus(201)
                 ->assertJson([
                     'data_id' => 'test_data_id',
                     'ms_name' => 'Test Mobile Suit',
                     'ms_data' => ['key' => 'value'],
                 ])
                 ->assertJsonMissing(['ms_number']); // ms_number should not be in response if null
    }

    /**
     * Test the mobile suits store endpoint with missing required fields.
     */
    public function test_store_missing_required(): void
    {
        $data = [
            'ms_name' => 'Test Mobile Suit', // missing data_id and ms_data
        ];

        $response = $this->postJson('/api/mobile-suits', $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['data_id', 'ms_data']);
    }

    /**
     * Test the mobile suits store endpoint with empty required fields.
     */
    public function test_store_empty_required(): void
    {
        $data = [
            'data_id' => '',
            'ms_name' => '',
            'ms_data' => [],
        ];

        $response = $this->postJson('/api/mobile-suits', $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['data_id', 'ms_name', 'ms_data']);
    }
}
