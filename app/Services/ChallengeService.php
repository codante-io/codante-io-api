<?php

namespace App\Services;

use App\Models\Challenge;
use Illuminate\Support\Facades\Auth;

class ChallengeService
{
    public static function queryChallenges()
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
            ->with("workshop:id,challenge_id")
            ->withCount("users")
            ->with([
                "users" => function ($query) {
                    $query
                        ->select(
                            "users.id",
                            "users.avatar_url",
                            "users.is_pro",
                            "users.is_admin"
                        )
                        ->when(Auth::check(), function ($query) {
                            $query->orderByRaw("users.id = ? DESC", [
                                auth()->id(),
                            ]);
                        })
                        ->inRandomOrder()
                        ->limit(5);
                },
            ])
            ->with("tags")
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
            ->get()
            ->map(function ($challenge) {
                return $challenge->mainTechnology;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }
}
