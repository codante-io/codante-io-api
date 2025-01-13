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
<<<<<<< HEAD:app/Http/Resources/Tracks/ChallengeCardResource.php
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
=======
        $resource = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'difficulty' => $this->difficulty,
            'duration_in_minutes' => $this->duration_in_minutes,
            'status' => $this->status,
            'sections' => $this->sectionsWithTrackables(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
>>>>>>> main:app/Http/Resources/TrackResource.php
        ];
    }
}
