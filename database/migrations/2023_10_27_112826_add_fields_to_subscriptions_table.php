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
        Schema::table("subscriptions", function (Blueprint $table) {
            $table
                ->string("boleto_url")
                ->nullable()
                ->after("price_paid_in_cents");
            $table
                ->string("payment_method")
                ->after("price_paid_in_cents")
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("subscriptions", function (Blueprint $table) {
            $table->dropColumn("payment_method");
            $table->dropColumn("boleto_url");
        });
    }
};
