<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\Workshop;
use Illuminate\Database\Seeder;

class MoveLessonsToChallenge extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $workshops = Workshop::all()->where('is_standalone', false);
        foreach ($workshops as $workshop) {
            $lessons = $workshop->lessons;
            $challengeId = $workshop->challenge_id;
            $videoUrl = $workshop->video_url;

            $challenge = Challenge::find($challengeId);

            if ($challenge) {
                $challenge->thumb_video_url = $videoUrl;
                $challenge->save();
            }

            if ($lessons->count() === 0) {
                continue;
            }

            foreach ($lessons as $lesson) {
                // $lesson->workshop_id = null;
                $lesson->challenge_id = $challengeId;
                $lesson->save();
            }
        }
    }
}
