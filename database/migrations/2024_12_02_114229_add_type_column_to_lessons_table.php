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
                ->string("type")
                ->nullable()
                ->after("name");
        });

        // make type an index
        Schema::table("lessons", function (Blueprint $table) {
            $table->index("type");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop index
        Schema::table("lessons", function (Blueprint $table) {
            $table->dropIndex("lessons_type_index");
        });

        Schema::table("lessons", function (Blueprint $table) {
            $table->dropColumn("type");
        });
    }
};
