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
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->string('slug')->unique();
            $table->string('status')->index(); // draft, published, soon, archived
            $table->boolean('is_standalone');
            $table->integer('difficulty')->default(1);
            $table->integer('duration_in_minutes')->nullable();
            $table->foreignId('instructor_id')->nullable()->references('id')->on('instructors');
            $table->foreignId('challenge_id')->nullable()->references('id')->on('challenges');
            $table->string('featured')->index()->nullable(); // featured, popular, new
            $table->date('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshops');
    }
};
