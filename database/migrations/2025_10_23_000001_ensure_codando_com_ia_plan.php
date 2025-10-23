<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PLAN_SLUG = 'codando-com-ia-v1';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $existingPlan = DB::table('plans')
            ->where('slug', self::PLAN_SLUG)
            ->first();

        $payload = [
            'name' => 'Codando com IA (Ao Vivo) v1',
            'duration_in_months' => 0,
            'price_in_cents' => 58800,
            'details' => json_encode([
                'product_slug' => 'curso-ao-vivo-codando-com-ia-v1',
                'description' => 'Venda do curso ao vivo Codando com IA',
            ]),
            'updated_at' => Carbon::now(),
        ];

        if ($existingPlan) {
            DB::table('plans')
                ->where('id', $existingPlan->id)
                ->update($payload);

            return;
        }

        DB::table('plans')->insert(array_merge($payload, [
            'slug' => self::PLAN_SLUG,
            'created_at' => Carbon::now(),
        ]));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('plans')
            ->where('slug', self::PLAN_SLUG)
            ->delete();
    }
};
