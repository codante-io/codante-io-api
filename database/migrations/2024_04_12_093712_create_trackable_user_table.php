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
        Schema::create('trackable_user', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('trackable_id')
                ->constrained('trackables')
                ->cascadeOnDelete();
            $table
                ->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unique(['trackable_id', 'user_id']);
            $table
                ->boolean('completed')
                ->nullable()
                ->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trackable_user');
    }
};
