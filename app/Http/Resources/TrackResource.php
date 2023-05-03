<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackResource extends JsonResource
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
            'difficulty' => $this->difficulty,
            'duration_in_minutes' => $this->duration_in_minutes,
            'status' => $this->status,
            'trackables' => $this->trackables(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        return $resource;
    }
}
