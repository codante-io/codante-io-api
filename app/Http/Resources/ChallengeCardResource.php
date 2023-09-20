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
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
            "short_description" => $this->short_description,
            "image_url" => $this->image_url,
            "status" => $this->status,
            "difficulty" => $this->difficulty,
            "has_solution" => $this->whenLoaded("workshop") ? true : false,
            "tags" => TagResource::collection($this->whenLoaded("tags")),
            "users" => UserAvatarResource::collection(
                //take 5 random users
                $this->whenLoaded("users")->take(5)
            ),
            "enrolled_users_count" => $this->users_count,
            "current_user_is_enrolled" => $this->userJoined(),
            "weekly_featured_start_date" => $this->weekly_featured_start_date,
            "solution_publish_date" => $this->solution_publish_date,
            "is_weekly_featured" => $this->isWeeklyFeatured(),
        ];
    }
}
