<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("lessons", function (Blueprint $table) {
            $table
                ->float("position")
                ->nullable()
                ->after("slug");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("lessons", function (Blueprint $table) {
            $table->dropColumn("position");
        });
    }
};
