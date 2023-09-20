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
            "image_url" => $this->image_url,
            "video_url" => $this->video_url,
            "status" => $this->status,
            "difficulty" => $this->difficulty,
            "duration_in_minutes" => $this->duration_in_minutes,
            "repository_name" => $this->repository_name,
            "featured" => $this->featured,
            "short_description" => $this->short_description,
            "description" => $this->description,
            "has_solution" => $this->hasSolution(),
            "enrolled_users_count" => $this->users_count,
            "current_user_is_enrolled" => $this->userJoined(),
            "tags" => TagResource::collection($this->whenLoaded("tags")),
            "workshop" => new WorkshopResource($this->whenLoaded("workshop")),
        ];
    }
}
