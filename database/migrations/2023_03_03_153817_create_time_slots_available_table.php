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
        Schema::create('time_slots_available', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chef_info_id');
            $table->unsignedBigInteger('from_time');
            $table->unsignedBigInteger('to_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots_available');
    }
};
