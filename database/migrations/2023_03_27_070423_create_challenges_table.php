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
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('slug')->unique();
            $table->string('status')->index(); // draft, published, soon, archived
            $table->integer('difficulty');
            $table->integer('duration_in_minutes')->nullable();
            $table->string('repository_url')->nullable();
            $table->foreignId('track_id')->nullable()->references('id')->on('tracks');
            $table->float('track_position', 8, 4)->nullable();
            $table->date('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
