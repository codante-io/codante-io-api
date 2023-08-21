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
            "has_workshop" => $this->whenLoaded("workshop") ? true : false,
            //  "has_workshop" => $this->whenLoaded("workshop"),
            "tags" => TagResource::collection($this->whenLoaded("tags")),
            // get users without resource
            // "users" => $this->whenLoaded("users"),
            "users" => UserAvatarResource::collection(
                //take 7 random users
                $this->whenLoaded("users")->take(5)
            ),
            "enrolled_users_count" => $this->users_count,
            "current_user_is_enrolled" => $this->userJoined(),
        ];
    }
}
