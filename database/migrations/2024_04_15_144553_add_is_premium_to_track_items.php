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
        Schema::table('track_items', function (Blueprint $table) {
            $table
                ->boolean('is_premium')
                ->default(true)
                ->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('track_items', function (Blueprint $table) {
            $table->dropColumn('is_premium');
        });
    }
};
