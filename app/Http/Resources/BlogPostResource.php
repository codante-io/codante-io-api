<?php

namespace App\Http\Resources;

use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...$this->resource->only([
                "id",
                "title",
                "content",
                "image_url",
                "short_description",
                "slug",
                "status",
                "created_at",
            ]),
            "reactions" => Reaction::getReactions(
                "App\\Models\\BlogPost",
                $this->id
            ),
            "instructor" => new InstructorResource($this->instructor),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
        ];
    }
}
