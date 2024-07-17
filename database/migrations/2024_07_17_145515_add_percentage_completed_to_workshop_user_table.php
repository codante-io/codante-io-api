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
        Schema::table("workshop_user", function (Blueprint $table) {
            $table
                ->integer("percentage_completed")
                ->default(0)
                ->after("status");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("workshop_user", function (Blueprint $table) {
            $table->dropColumn("percentage_completed");
        });
    }
};
