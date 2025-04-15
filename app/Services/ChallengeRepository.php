<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\User;

class ChallengeRepository
{
    protected function challengeCardsBaseQuery($query, $currentUser)
    {
        return $query
            ->select(
                'id',
                'name',
                'slug',
                'short_description',
                'image_url',
                'status',
                'difficulty',
                'estimated_effort',
                'category',
                'is_premium',
                'weekly_featured_start_date',
                'solution_publish_date',
                'main_technology_id'
            )
            ->listed()
            ->with('mainTechnology:id,name,image_url')
            ->with('tags:id,name')
            ->withCount('users')
            ->with([
                'users' => function ($query) use ($currentUser) {
                    $query
                        ->select(
                            'users.id',
                            'users.avatar_url',
                            'users.is_pro',
                            'users.is_admin'
                        )
                        ->when($currentUser, function ($query) use (
                            $currentUser
                        ) {
                            $query->orderByRaw('users.id = ? DESC', [
                                $currentUser->id,
                            ]);
                        })
                        ->inRandomOrder()
                        ->limit(5);
                },
            ]);
    }

    public function challengeCardsQuery(?User $currentUser)
    {
        $query = Challenge::query();
        $query = $this->challengeCardsBaseQuery($query, $currentUser);
        return $query;
    }

    public function featuredChallengeCardQuery($currentUser)
    {
        $query = Challenge::query();
        $query = $this->challengeCardsBaseQuery($query, $currentUser);
        $query = $query
            ->listed()
            ->weeklyFeatured()
            ->orderBy('weekly_featured_start_date', 'desc');

        return $query;
    }

    public function getChallenges(?User $currentUser)
    {
        return $this->challengeCardsQuery($currentUser)->get();
    }

    public function getFeaturedChallenge(?User $currentUser)
    {
        return $this->featuredChallengeCardQuery($currentUser)->first();
    }

    // public static function groupByTechnology($challenges)
    // {
    //     return $challenges->reduce(function ($carry, $challenge) {
    //         $key = $challenge->isWeeklyFeatured()
    //             ? 'featured'
    //             : ($challenge->mainTechnology
    //                 ? $challenge->mainTechnology->name
    //                 : 'Outras tecnologias');
    //         $imageUrl = $challenge->mainTechnology
    //             ? $challenge->mainTechnology->image_url
    //             : null;

    //         if (! isset($carry[$key])) {
    //             $carry[$key] = [
    //                 'name' => $key,
    //                 'image_url' => $imageUrl,
    //                 'challenges' => [],
    //             ];
    //         }

    //         $carry[$key]['challenges'][] = $challenge;

    //         return $carry;
    //     }, []);
    // }

    // public static function getAllTechnologies($challenges)
    // {
    //     return $challenges
    //         ->map(function ($challenge) {
    //             return $challenge->mainTechnology;
    //         })
    //         ->filter()
    //         ->unique()
    //         ->values()
    //         ->toArray();
    // }
}
