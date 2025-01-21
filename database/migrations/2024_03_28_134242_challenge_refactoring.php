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
        Schema::table('challenges', function (Blueprint $table) {
            $table
                ->enum('estimated_effort', ['1_day', '2_days', '1_week'])
                ->default('1_DAY')
                ->after('featured');

            $table
                ->enum('category', ['frontend', 'fullstack'])
                ->default('frontend')
                ->after('featured');

            $table
                ->boolean('is_premium')
                ->default(false)
                ->after('featured');

            $table
                ->unsignedBigInteger('main_technology_id')
                ->nullable()
                ->after('featured');

            $table
                ->foreign('main_technology_id')
                ->references('id')
                ->on('tags')
                ->onDelete('set null')
                ->after('featured');
        });

        // Update the difficulty column to be an enum (it was an integer before)
        DB::statement(
            "ALTER TABLE challenges MODIFY COLUMN difficulty enum('newbie', 'intermediate', 'advanced') DEFAULT 'NEWBIE'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn('estimated_effort');
            $table->dropColumn('category');
            $table->dropColumn('is_premium');
            $table->dropForeign('main_technology_id');
            $table->dropColumn('main_technology_id');
        });

        DB::statement(
            'ALTER TABLE challenges MODIFY COLUMN difficulty integer DEFAULT 1'
        );
    }
};
