<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorResource extends JsonResource
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
            'company' => $this->company,
            'email' => $this->email,
            'bio' => $this->bio,
            'avatar_url' => $this->avatar_url,
            'slug' => $this->slug,
            'workshops' => WorkshopResource::collection($this->whenLoaded('workshops')),
            'links' => $this->links,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
