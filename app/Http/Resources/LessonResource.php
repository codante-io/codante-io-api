<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class LessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "content" => $this->content,
            "is_free" => $this->is_free,
            "user_can_view" => $this->canViewVideo(),
            "video_url" => $this->canViewVideo() ? $this->video_url : null,
            "duration_in_seconds" => $this->duration_in_seconds,
            "slug" => $this->slug,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "user_completed" => $this->user_completed ? true : false,
        ];
    }

    private function canViewVideo(): bool
    {
        if ($this->is_free) {
            return true;
        }

        if (!Auth::user()) {
            return false;
        }

        return Auth::user()->can("view", $this->resource);
    }
}
