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
        Schema::table('book_chefs', function (Blueprint $table) {
           $table->boolean('reminder_sent')->default(false)->after('status');
            $table->timestamp('reminder_time')->after('reminder_sent')->nullable();
        });
        DB::statement("ALTER TABLE `book_chefs` CHANGE `status` `status` ENUM('pending', 'paid', 'cancel', 'complete');");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_chefs', function (Blueprint $table) {
            //
        });
    }
};
