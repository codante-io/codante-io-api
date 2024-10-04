<?php

namespace App\Http\Resources;

use App\Models\Comment;
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
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'content' => $this->content,
            'available_to' => $this->available_to,
            'user_can_view' => $this->canViewVideo(),
            'video_url' => $this->canViewVideo() ? $this->video_url : null,
            'type' => $this->video_url ? 'video' : 'text',
            'thumbnail_url' => $this->thumbnail_url,
            'duration_in_seconds' => $this->duration_in_seconds,
            'slug' => $this->slug,
            'position' => $this->position,
            'section' => $this->section,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user_completed' => $this->userCompleted(Auth::guard("sanctum")->id()),
            'comments' => Comment::getComments(
                'App\\Models\\Lesson',
                $this->id
            ),
        ];
    }

    private function canViewVideo(): bool
    {
        if (! Auth::check()) {
            if ($this->available_to === 'all') {
                return true;
            }

            return false;
        }

        return Auth::user()->can('view', $this->resource);
    }
}
