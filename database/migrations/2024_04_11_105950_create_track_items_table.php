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
        Schema::create('track_items', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['external_link', 'markdown']);
            $table->string('name');
            $table->text('content');
            $table->integer('position');
            $table->enum('status', [
                'soon',
                'published',
                'unlisted',
                'draft',
                'archived',
            ]);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('track_items');
    }
};
