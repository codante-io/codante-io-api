<?php

namespace App\Http\Resources\Tracks;

use App\Http\Resources\InstructorCardResource;
use App\Http\Resources\LessonResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkshopCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = [
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
            "video_url" => $this->video_url,
            "instructor" => new InstructorCardResource(
                $this->whenLoaded("instructor")
            ),
            "type" => $this->pivot->trackable_type,
            "lessons" => $this->lessons->groupBy('section')->map(function ($lessons, $section) {
                return LessonResource::collection($lessons);
            }),
        ];

        return $resource;
    }
}
