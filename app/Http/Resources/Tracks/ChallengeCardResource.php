<?php

namespace App\Http\Resources\Tracks;

use App\Http\Resources\LessonResource;
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
            "type" => $this->pivot->trackable_type,
            "short_description" => $this->short_description,
            "image_url" => $this->image_url,
            "video_url" => $this->video_url,
            "current_user_is_enrolled" => $this->users->contains(
                $this->current_user_id
            ),
            "lessons" => LessonResource::collection($this->whenLoaded("workshop")->lessons),
        ];
    }
}
