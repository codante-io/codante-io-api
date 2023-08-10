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
        Schema::table("technical_assessments", function (Blueprint $table) {
            $table
                ->string("zipped_files_url")
                ->nullable()
                ->after("assessment_instructions_text");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("technical_assessments", function (Blueprint $table) {
            $table->dropColumn("zipped_files_url");
        });
    }
};
