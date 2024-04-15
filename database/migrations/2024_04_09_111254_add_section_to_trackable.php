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
        Schema::table("trackables", function (Blueprint $table) {
            $table
                ->string("name")
                ->nullable()
                ->after("position");

            $table
                ->text("description")
                ->nullable()
                ->after("name");

            $table
                ->foreignId("section_id")
                ->nullable()
                ->after("description")
                ->constrained("track_sections")
                ->onDelete("set null");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("trackables", function (Blueprint $table) {
            $table->dropColumn("name");
            $table->dropColumn("description");
            $table->dropForeign(["section_id"]);
            $table->dropColumn("section_id");
        });
    }
};
