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
        Schema::create('mobile_suits', function (Blueprint $table) {
            $table->id();
            $table->string('data_id');
            $table->string('ms_number');
            $table->string('ms_name');
            $table->string('ms_name_optional')->nullable();
            $table->string('ms_icon')->nullable();
            $table->json('ms_data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_suits');
    }
};
