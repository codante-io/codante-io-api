<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestimonialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "name" => $this->name,
            "body" => $this->body,
            "avatar_url" => $this->avatar_url,
            "social_media_link" => $this->social_media_link,
            "social_media_nickname" => $this->social_media_nickname,
            // "company" => $this->company,
            // "source" => $this->source,
            "featured" => $this->featured,
        ];
    }
}
