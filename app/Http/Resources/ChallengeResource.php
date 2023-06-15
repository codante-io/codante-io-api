<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeResource extends JsonResource
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
            "description" => $this->description,
            "image_url" => $this->image_url,
            "status" => $this->status,
            "difficulty" => $this->difficulty,
            "duration_in_minutes" => $this->duration_in_minutes,
            "repository_name" => $this->repository_name,
            "workshop" => new WorkshopResource($this->whenLoaded("workshop")),
            "instructor" => new InstructorResource(
                $this->whenLoaded("instructor")
            ),
            "tags" => TagResource::collection($this->whenLoaded("tags")),
            // get users without resource
            // "users" => $this->whenLoaded("users"),
            "users" => UserAvatarResource::collection(
                //take 7 random users
                $this->whenLoaded("users")->take(5)
            ),
            "enrolled_users_count" => $this->users_count,
            "base_color" => $this->base_color,
            "published_at" => $this->published_at,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];

        // return parent::toArray($request);
    }
}
