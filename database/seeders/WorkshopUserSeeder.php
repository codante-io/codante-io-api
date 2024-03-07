<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\User;
use App\Models\Workshop;
use App\Models\WorkshopUser;
use DB;
use Illuminate\Database\Seeder;

class WorkshopUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lessonUsers = DB::table("lesson_user")
            ->join("lessons", "lesson_user.lesson_id", "=", "lessons.id")
            ->join("users", "lesson_user.user_id", "=", "users.id")
            ->select("lesson_user.*", "lessons.workshop_id", "users.is_pro")
            ->get();

        foreach ($lessonUsers as $lessonUser) {
            $workshopId = $lessonUser->workshop_id;
            $workshop = Workshop::find($workshopId);

            $lessonUserCount = DB::table("lesson_user")
                ->join("lessons", "lesson_user.lesson_id", "=", "lessons.id")
                ->where("lessons.workshop_id", $workshopId)
                ->where("lesson_user.user_id", $lessonUser->user_id)
                ->count();

            $lessonCount = DB::table("lessons")
                ->where("workshop_id", $workshopId)
                ->count();

            $status =
                $lessonUserCount >= $lessonCount ? "completed" : "in-progress";

            $latestLessonUser = DB::table("lesson_user")
                ->join("lessons", "lesson_user.lesson_id", "=", "lessons.id")
                ->where("lessons.workshop_id", $workshopId)
                ->where("lesson_user.user_id", $lessonUser->user_id)
                ->orderBy("lesson_user.completed_at", "desc")
                ->first();

            $completedAt =
                $status == "completed" ? $latestLessonUser->completed_at : null;

            if ($workshop->is_standalone) {
                $workshopUser = WorkshopUser::firstOrCreate(
                    [
                        "workshop_id" => $workshopId,
                        "user_id" => $lessonUser->user_id,
                    ],
                    ["status" => $status, "completed_at" => $completedAt]
                );

                if ($status == "completed") {
                    $durationInSeconds = $workshop
                        ->lessons()
                        ->sum("duration_in_seconds");
                    Certificate::firstOrCreate(
                        [
                            "certifiable_type" => "App\Models\WorkshopUser",
                            "certifiable_id" => $workshopUser->id,
                        ],
                        [
                            "user_id" => $lessonUser->user_id,
                            "status" => "published",
                            "metadata" => [
                                "certifiable_source_name" => $workshop->name,
                                "end_date" =>
                                    $workshopUser->completed_at ??
                                    now()->format("Y-m-d H:i:s"),
                                "certifiable_slug" => $workshop->slug,
                                "duration_in_seconds" => $durationInSeconds,
                            ],
                        ]
                    );
                }
            }
        }
    }
}
