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
        Schema::create('technical_assessments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('company_name');
            $table->string('image_url')->nullable();
            $table->string('image_url_dark')->nullable();
            $table->string('status')->default('draft');
            $table->string('type');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('challenge_id')->nullable();
            $table->string('company_website')->nullable();
            $table->string('company_headquarters')->nullable();
            $table->text('company_description')->nullable();
            $table->string('company_size')->nullable();
            $table->string('company_industry')->nullable();
            $table->string('company_linkedin')->nullable();
            $table->string('company_github')->nullable();
            $table->text('assessment_description')->nullable();
            $table->string('assessment_year')->nullable();
            $table->string('assessment_language')->default('pt');
            $table->string('assessment_instructions_url')->nullable();
            $table->longText('assessment_instructions_text')->nullable();
            $table->string('job_position')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technical_assessments');
    }
};
