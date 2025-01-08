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
        Schema::table('challenges', function (Blueprint $table) {
            $table
                ->dateTime('weekly_featured_start_date')
                ->after('position')
                ->nullable();
            $table
                ->renameColumn('published_at', 'solution_publish_date')
                ->nullable();
            $table->dropColumn('base_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn('weekly_featured_start_date');
            $table->renameColumn('solution_publish_date', 'published_at');
            $table->string('base_color')->nullable();
        });
    }
};
