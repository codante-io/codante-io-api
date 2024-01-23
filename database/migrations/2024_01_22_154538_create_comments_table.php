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
        Schema::create("comments", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("user_id")
                ->references("id")
                ->on("users");
            $table->string("commentable_type");
            $table->unsignedBigInteger("commentable_id");
            $table->unsignedBigInteger("replying_to")->nullable();
            $table->string("comment", 240);
            $table->timestamps();
        });
    }

    // Schema::create('reactions', function (Blueprint $table) {
    //     $table->id();
    //     $table->string('reactable_type');
    //     $table->unsignedBigInteger('reactable_id');
    //     $table->unsignedBigInteger('user_id');
    //     $table->string('reaction');
    //     $table->timestamps();
    // });

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("comments");
    }
};
