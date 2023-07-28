<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TechnicalAssessmentResource extends JsonResource
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
            'title' => $this->title,
            'image_url' => $this->image_url,
            'image_url_dark' => $this->image_url_dark,
            'slug' => $this->slug,
            'type' => $this->type,
            'status' => $this->status,
            'tags' => $this->tags->pluck("name"),
            'has_challenge' => !is_null($this->challenge_id),
        ];

        return $resource;
    }
}
