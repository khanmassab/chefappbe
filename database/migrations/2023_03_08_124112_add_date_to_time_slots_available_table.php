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
        Schema::table('time_slots_available', function (Blueprint $table) {
            $table->string('date')->after('to_time')->nullable();
            $table->enum('status', ['available','unavailable'])->default('available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_slots_available', function (Blueprint $table) {
            $table->dropColumn('date');
            $table->dropColumn('status');
        });
    }
};
