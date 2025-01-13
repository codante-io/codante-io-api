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
        Schema::create('track_sections', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('track_id')
                ->constrained()
                ->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('track_sections', function (Blueprint $table) {
            Schema::dropIfExists('track_sections');
        });
    }
};
