<?php

namespace App\Http\Resources\Tracks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkshopTrackableCard extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lessons = $this->lessons()
            ->select("id", "name", "slug")
            ->get();

        $resource = [
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
            "video_url" => $this->video_url,
            "lessons" => $lessons
                ? LessonListResource::collection($lessons)
                : [],
        ];

        return $resource;
    }
}
