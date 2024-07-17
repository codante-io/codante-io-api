<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateWorkshopUserPercentageCompletedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obter todos os registros da tabela workshop_user
        $workshopUsers = DB::table("workshop_user")->get();

        foreach ($workshopUsers as $workshopUser) {
            // Obter o ID do workshop
            $workshopId = $workshopUser->workshop_id;

            if (!$workshopId) {
                continue; // Pula se não encontrar o workshop correspondente
            }

            // Obter o número total de aulas do workshop
            $totalLessons = DB::table("lessons")
                ->where("workshop_id", $workshopId)
                ->count();

            // Obter o número de aulas assistidas pelo usuário no workshop
            $completedLessons = DB::table("lesson_user")
                ->join("lessons", "lesson_user.lesson_id", "=", "lessons.id")
                ->where("lesson_user.user_id", $workshopUser->user_id)
                ->where("lessons.workshop_id", $workshopId)
                ->count();

            // Calcular a porcentagem de aulas completadas
            $percentageCompleted =
                $totalLessons > 0
                    ? ($completedLessons / $totalLessons) * 100
                    : 0;

            // Atualizar o campo percentage_completed
            DB::table("workshop_user")
                ->where("id", $workshopUser->id)
                ->update(["percentage_completed" => $percentageCompleted]);
        }
    }
}
