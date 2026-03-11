<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('game_session_participants', 'game_session_members');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('game_session_members', 'game_session_participants');
    }
};
