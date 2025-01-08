<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image_url' => $this->image_url,
            'status' => $this->status,
            'difficulty' => $this->difficulty,
            'has_solution' => $this->whenLoaded('workshop') ? true : false,
            'estimated_effort' => $this->estimated_effort,
            'category' => $this->category,
            'is_premium' => $this->is_premium,
            'main_technology' => $this->whenLoaded(
                'mainTechnology',
                function () {
                    return new TagResource($this->mainTechnology);
                }
            ),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'avatars' => UserAvatarResource::collection(
                //take 5 random users
                $this->whenLoaded('users') ? $this->users->take(5) : []
            ),
            'enrolled_users_count' => $this->users_count,
            'current_user_is_enrolled' => $this->users->contains(
                $this->current_user_id
            ),
            'weekly_featured_start_date' => $this->weekly_featured_start_date,
            'solution_publish_date' => $this->solution_publish_date,
            'is_weekly_featured' => $this->isWeeklyFeatured(),
        ];
    }
}
