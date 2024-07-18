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
        Schema::table("workshops", function (Blueprint $table) {
            $table
                ->foreignId("main_technology_id")
                ->nullable()
                ->after("status")
                ->constrained("tags");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("workshops", function (Blueprint $table) {
            $table->dropForeign(["main_technology_id"]);
            $table->dropColumn("main_technology_id");
        });
    }
};
