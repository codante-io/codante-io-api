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
        Schema::table('challenge_user', function (Blueprint $table) {
            $table->string('submission_url')->nullable();
            $table->string('submission_image_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenge_user', function (Blueprint $table) {
            $table->dropColumn('submission_url');
            $table->dropColumn('submission_image_url');
        });
    }
};
