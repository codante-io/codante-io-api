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
        Schema::create('trackables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('track_id')->references('id')->on('tracks');
            $table->morphs('trackable');
            $table->unique(['track_id', 'trackable_id', 'trackable_type']);
            $table->float('position', 8, 4)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trackables');
    }
};
