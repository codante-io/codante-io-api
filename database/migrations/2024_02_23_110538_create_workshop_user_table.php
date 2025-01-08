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
        Schema::create('workshop_user', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('workshop_id')
                ->references('id')
                ->on('workshops');
            $table
                ->foreignId('user_id')
                ->references('id')
                ->on('users');
            $table->string('status')->default('in-progress');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_user');
    }
};
