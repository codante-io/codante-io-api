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
        Schema::create('challenge_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->references('id')->on('challenges');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->boolean('completed')->default(false);
            $table->boolean('joined_discord')->default(false);
            $table->string('fork_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_user');
    }
};
