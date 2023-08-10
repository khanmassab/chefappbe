<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
//DB::statement("UPDATE users SET is_chef = 2 WHERE is_chef = true");
// DB::statement("UPDATE users SET is_chef = 1 WHERE is_chef = false");
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_chef')->default(0)->change();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
             
        });
    }
};
