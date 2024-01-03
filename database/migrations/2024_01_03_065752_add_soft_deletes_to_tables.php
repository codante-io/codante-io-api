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
        Schema::table("blog_posts", function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table("challenge_user", function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table("challenges", function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table("instructors", function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table("lessons", function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table("plans", function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table("reactions", function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table("subscriptions", function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table("tags", function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table("technical_assessments", function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table("testimonials", function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table("tracks", function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table("users", function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table("workshops", function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("blog_posts", function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table("challenge_user", function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table("challenges", function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table("instructors", function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table("lessons", function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table("plans", function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table("reactions", function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table("subscriptions", function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table("tags", function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table("technical_assessments", function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table("testimonials", function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table("tracks", function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table("users", function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table("workshops", function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
