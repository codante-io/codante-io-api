<?php

namespace Database\Seeders;

use App\Models\UserActionPoints;
use DB;
use Illuminate\Database\Seeder;

class CalculateRanking extends Seeder
{
    public function run(): void
    {
        // Fetch completed challenges from the challenge_user table
        // and join it with the challenges table to get the difficulty
        $challenges = DB::table("challenge_user")
            ->join(
                "challenges",
                "challenge_user.challenge_id",
                "=",
                "challenges.id"
            )
            ->select(
                "challenge_user.id",
                "challenge_user.user_id",
                "challenges.difficulty",
                "challenge_user.submitted_at",
                "challenge_user.created_at",
                "challenge_user.fork_url",
                "challenge_user.submission_url"
            )
            ->get();

        foreach ($challenges as $challenge) {
            // Create an entry in the UserRankings table
            UserActionPoints::create([
                "user_id" => $challenge->user_id,
                "action_name" => "challenge_joined",
                "points" => 1,
                "pointable_id" => $challenge->id,
                "pointable_type" => "App\Models\ChallengeUser",
                "created_at" => $challenge->created_at,
            ]);

            if ($challenge->fork_url) {
                UserActionPoints::create([
                    "user_id" => $challenge->user_id,
                    "action_name" => "challenge_forked",
                    "points" => 3,
                    "pointable_id" => $challenge->id,
                    "pointable_type" => "App\Models\ChallengeUser",
                    "created_at" => $challenge->created_at,
                ]);
            }

            if ($challenge->submission_url) {
                // Create an entry in the UserRankings table
                UserActionPoints::create([
                    "user_id" => $challenge->user_id,
                    "action_name" => "challenge_completed",
                    "points" => 10 * $challenge->difficulty,
                    "pointable_id" => $challenge->id,
                    "pointable_type" => "App\Models\ChallengeUser",
                    "created_at" => $challenge->submitted_at,
                ]);
            }
        }

        // Fetch reactions for completed challenges
        $reactions = DB::table("reactions")
            ->join(
                "challenge_user",
                "reactions.reactable_id",
                "=",
                "challenge_user.id"
            )
            ->select(
                "reactions.id",
                "reactions.user_id",
                "challenge_user.submitted_at",
                "challenge_user.created_at"
            )
            ->get();

        foreach ($reactions as $reaction) {
            // Calculate points based on reactions
            $points = 1;

            // Create an entry in the UserRankings table
            UserActionPoints::create([
                "user_id" => $reaction->user_id,
                "action_name" => "reaction_received",
                "points" => $points,
                "pointable_id" => $reaction->id,
                "pointable_type" => "App\Models\Reaction",
                "created_at" => $reaction->created_at,
            ]);
        }

        echo "UserActionPoints populated successfully!";
    }
}
