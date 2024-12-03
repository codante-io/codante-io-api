<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Workshop;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MigrateLessonToLessonable extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // get all lessons
        $lessons = Lesson::all();

        foreach ($lessons as $lesson) {
            $this->migrateLesson($lesson);
        }
    }

    private function migrateLesson(Lesson $lesson): void
    {
        // if the lesson is from a standalone workshop.
        // we will set the lessonable_type to workshop and lessonable_id to the workshop id
        $workshop = Workshop::find($lesson->workshop_id);

        if ($workshop->is_standalone) {
            $lesson->lessonable_type = "App\Models\Workshop";
            $lesson->lessonable_id = $lesson->workshop_id;
        }

        // if the lesson is from a challenge.
        // we will set the lessonable_type to challenge and lessonable_id to the challenge id

        if (!$workshop->is_standalone) {
            $lesson->lessonable_type = "App\Models\Challenge";
            $lesson->lessonable_id = $workshop->challenge_id;
        }

        $lesson->save();

        // if the lesson is from a track.
        // we will set the lessonable_type to track and lessonable_id to the track id
    }
}
