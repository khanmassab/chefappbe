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

        Schema::table('users', function (Blueprint $table) {
            $table->string('social_token', 1000)->change()->nullable();

    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('user_payments', function (Blueprint $table) {
            $table->dropColumn('social_token');

        });
    }
};