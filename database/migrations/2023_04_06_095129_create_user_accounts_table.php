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
        Schema::create('user_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('account_no')->nullable();
            $table->string('acc_token')->nullable();
            $table->string('person_id')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
    }
    // 'user_id',
    // 'account_no',
    // 'account_token',
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_accounts');
    }
};
