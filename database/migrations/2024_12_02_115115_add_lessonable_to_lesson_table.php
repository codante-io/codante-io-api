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
                ->unsignedBigInteger("lessonable_id")
                ->nullable()
                ->after("id");
            $table
                ->string("lessonable_type")
                ->nullable()
                ->after("lessonable_id");
            
                // make workshop_id nullable
            $table->foreignId("workshop_id")->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("lessons", function (Blueprint $table) {
            $table->dropColumn("lessonable_id");
            $table->dropColumn("lessonable_type");
            $table->foreignId("workshop_id")->nullable(false)->change();
        });
    }
};
