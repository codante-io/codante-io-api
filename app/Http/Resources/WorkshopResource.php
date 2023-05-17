<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkshopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $resource = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'video_url' => $this->video_url,
            'difficulty' => $this->difficulty,
            'duration_in_minutes' => $this->duration_in_minutes,
            'status' => $this->status,
            'lessons' => LessonResource::collection($this->whenLoaded('lessons')),
            'instructor' => new InstructorResource($this->whenLoaded('instructor')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'published_at' => $this->published_at,
        ];

        if ($this->lessons_count) {
            $resource['lessons_count'] = $this->lessons_count;
        }

        return $resource;
        // return parent::toArray($request);
    }
}
