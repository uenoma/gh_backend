<?php

namespace Database\Seeders;

use App\Models\ChatChannel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChatChannelSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ChatChannel::firstOrCreate(
            ['name' => 'general'],
            [
                'description' => '一般チャンネル',
                'user_id'     => null,
                'is_system'   => true,
            ]
        );
    }
}
