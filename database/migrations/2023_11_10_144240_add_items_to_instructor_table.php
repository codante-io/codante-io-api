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
        Schema::table("instructors", function (Blueprint $table) {
            // add cpf, phone, address, dados bancários, username do github, data de nascimento, usuário do discord
            $table
                ->string("cpf")
                ->nullable()
                ->after("slug");
            $table
                ->string("phone")
                ->nullable()
                ->after("slug");
            $table
                ->text("address")
                ->nullable()
                ->after("slug");
            $table
                ->text("bank_data")
                ->nullable()
                ->after("slug");
            $table
                ->string("github_username")
                ->nullable()
                ->after("slug");
            $table
                ->string("discord_username")
                ->nullable()
                ->after("slug");
            $table
                ->date("birth_date")
                ->nullable()
                ->after("slug");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("instructors", function (Blueprint $table) {
            $table->dropColumn("cpf");
            $table->dropColumn("phone");
            $table->dropColumn("address");
            $table->dropColumn("bank_data");
            $table->dropColumn("github_username");
            $table->dropColumn("discord_username");
            $table->dropColumn("birth_date");
        });
    }
};
