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
           $table->string('social_token',255)->after('is_chef')->nullable();
           $table->integer('login_type')->after('social_token')->comment('0 for fb 1 for google')->nullable(); // 0 for facebook and 1 for google
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('social_token');
            $table->dropColumn('login_type');
        });
    }
};
