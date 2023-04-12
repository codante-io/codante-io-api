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
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'image_url' => $this->imageURL,
            'status' => $this->status,
            'difficulty' => $this->difficulty,
            'duration_in_minutes' => $this->duration_in_minutes,
            'repository_url' => $this->repository_url,
            'workshop' => new WorkshopResource($this->whenLoaded('workshop')),
            'instructor' => new InstructorResource($this->whenLoaded('instructor')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // return parent::toArray($request);
    }
}
