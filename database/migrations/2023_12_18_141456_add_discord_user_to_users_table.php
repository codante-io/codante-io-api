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
        Schema::table('users', function (Blueprint $table) {
            $table
                ->string('discord_user')
                ->nullable()
                ->after('github_user');

            $table
                ->json('discord_data')
                ->nullable()
                ->after('discord_user');

            $table
                ->json('github_data')
                ->nullable()
                ->after('github_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('discord_user');
            $table->dropColumn('discord_data');
            $table->dropColumn('github_data');
        });
    }
};
