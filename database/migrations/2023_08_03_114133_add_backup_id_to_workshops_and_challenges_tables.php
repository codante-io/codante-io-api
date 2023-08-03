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
        Schema::table('workshops', function (Blueprint $table) {
            $table->unsignedInteger('backup_id')->nullable()->after('featured');
        });
        Schema::table('challenges', function (Blueprint $table) {
            $table->unsignedInteger('backup_id')->nullable()->after('base_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workshops', function (Blueprint $table) {
            $table->dropColumn('backup_id');
        });
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn('backup_id');
        });
    }
};
