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
            'creator_name' => 'Test Creator',
            'edit_password' => 'test_password',
        ];

        $response = $this->postJson('/api/mobile-suits', $data);

        $response->assertStatus(201)
                 ->assertJson([
                     'data_id' => 'test_data_id',
                     'ms_name' => 'Test Mobile Suit',
                     'ms_data' => ['key' => 'value'],
                 ])
                 ->assertJsonMissing(['ms_number', 'creator']);
    }

    /**
     * Test the mobile suits store endpoint with missing required fields.
     */
    public function test_store_missing_required(): void
    {
        $data = [
            'ms_name' => 'Test Mobile Suit', // missing data_id, ms_data, creator_name, edit_password
        ];

        $response = $this->postJson('/api/mobile-suits', $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['data_id', 'ms_data', 'creator_name', 'edit_password']);
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
            'creator_name' => '',
            'edit_password' => '',
        ];

        $response = $this->postJson('/api/mobile-suits', $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['data_id', 'ms_name', 'ms_data', 'creator_name', 'edit_password']);
    }

    /**
     * Test the mobile suits update endpoint with unauthorized access.
     */
    public function test_update_unauthorized(): void
    {
        // First create a mobile suit
        $createData = [
            'data_id' => 'test_data_id',
            'ms_name' => 'Test Mobile Suit',
            'ms_data' => ['key' => 'value'],
            'creator_name' => 'Test Creator',
            'edit_password' => 'test_password',
        ];
        $this->postJson('/api/mobile-suits', $createData);

        // Try to update with wrong credentials
        $updateData = [
            'data_id' => 'test_data_id',
            'ms_name' => 'Updated Name',
            'ms_data' => ['key' => 'value'],
            'creator_name' => 'Wrong Creator',
            'edit_password' => 'wrong_password',
        ];

        $response = $this->putJson('/api/mobile-suits/1', $updateData);

        $response->assertStatus(403);
    }

    /**
     * Test the mobile suits update endpoint with validation error.
     */
    public function test_update_validation_error(): void
    {
        // First create a mobile suit
        $createData = [
            'data_id' => 'test_data_id',
            'ms_name' => 'Test Mobile Suit',
            'ms_data' => ['key' => 'value'],
            'creator_name' => 'Test Creator',
            'edit_password' => 'test_password',
        ];
        $this->postJson('/api/mobile-suits', $createData);

        // Try to update with missing required fields
        $updateData = [
            'ms_name' => 'Updated Name',
            // missing data_id, ms_data, creator_name, edit_password
        ];

        $response = $this->putJson('/api/mobile-suits/1', $updateData);

        $response->assertStatus(422)
                 ->assertJson([
                     'message' => 'データIDは必須です (and 3 more errors)',
                     'errors' => [
                         'data_id' => ['データIDは必須です'],
                         'ms_data' => ['MSデータは必須です'],
                         'creator_name' => ['作成者名は必須です'],
                         'edit_password' => ['編集パスワードは必須です'],
                     ],
                 ]);
    }
}
