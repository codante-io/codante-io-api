<?php

namespace App\Services;

use App\Http\Resources\TagResource;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChallengeService
{
    public static function queryChallenges(User|null $currentUser)
    {
        return Challenge::query()
            ->select(
                "id",
                "name",
                "slug",
                "short_description",
                "image_url",
                "status",
                "difficulty",
                "estimated_effort",
                "category",
                "is_premium",
                "weekly_featured_start_date",
                "solution_publish_date",
                "main_technology_id"
            )
            ->listed()
            ->with("mainTechnology")
            ->withCount("users")
            // This will be used to check if the current user is enrolled in the challenge as well get 5
            ->with([
                "users" => function ($query) use ($currentUser) {
                    $query
                        ->select(
                            "users.id",
                            "users.avatar_url",
                            "users.is_pro",
                            "users.is_admin"
                        )
                        ->when($currentUser, function ($query) use (
                            $currentUser
                        ) {
                            $query->orderByRaw("users.id = ? DESC", [
                                $currentUser->id,
                            ]);
                        })
                        ->inRandomOrder()
                        ->limit(5);
                },
            ])
            ->with("tags:id,name")
            ->orderByRaw(
                "-EXISTS (SELECT 1 FROM workshops WHERE workshops.challenge_id = challenges.id)"
            )
            ->orderBy("status", "asc")
            ->orderBy("position", "asc")
            ->orderBy("created_at", "desc");
    }

    public static function groupByTechnology($challenges)
    {
        return $challenges->reduce(function ($carry, $challenge) {
            $key = $challenge->isWeeklyFeatured()
                ? "featured"
                : ($challenge->mainTechnology
                    ? $challenge->mainTechnology->name
                    : "Outras tecnologias");
            $imageUrl = $challenge->mainTechnology
                ? $challenge->mainTechnology->image_url
                : null;

            if (!isset($carry[$key])) {
                $carry[$key] = [
                    "name" => $key,
                    "image_url" => $imageUrl,
                    "challenges" => [],
                ];
            }

            $carry[$key]["challenges"][] = $challenge;

            return $carry;
        }, []);
    }

    public static function getAllTechnologies($challenges)
    {
        return $challenges
            ->map(function ($challenge) {
                return $challenge->mainTechnology;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
