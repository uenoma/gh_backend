<?php

namespace Database\Seeders;

use App\Models\ChatChannel;
use App\Models\User;
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
        $general = ChatChannel::firstOrCreate(
            ['name' => 'general'],
            [
                'description' => '一般チャンネル',
                'user_id'     => null,
                'is_system'   => true,
            ]
        );

        // 既存ユーザーを全員追加（既に参加済みのユーザーはスキップ）
        $now = now();
        $existingMemberIds = $general->members()->pluck('users.id');

        User::whereNotIn('id', $existingMemberIds)
            ->pluck('id')
            ->each(function ($userId) use ($general, $now) {
                $general->members()->attach($userId, [
                    'joined_at'    => $now,
                    'last_read_at' => $now,
                ]);
            });
    }
}
