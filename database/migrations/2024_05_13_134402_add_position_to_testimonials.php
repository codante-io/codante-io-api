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
        Schema::table("testimonials", function (Blueprint $table) {
            $table
                ->integer("position")
                ->nullable()
                ->after("featured");

            $table
                ->string("role")
                ->nullable()
                ->after("avatar_url");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("testimonials", function (Blueprint $table) {
            $table->dropColumn("position");

            $table->dropColumn("role");
        });
    }
};
