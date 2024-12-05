<?php

namespace App\Http\Resources\Tracks;

use App\Http\Resources\SidebarLessonResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeTrackableCard extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lessons = $this->lessons()
            ->select('id', 'name', 'slug')
            ->get();

        $resource = [
            'id' => $this->id,
            'type' => 'challenge',
            'name' => $this->name,
            'slug' => $this->slug,
            'video_url' => $this->video_url,
            'lessons' => $lessons
                ? SidebarLessonResource::collection($lessons)
                : [],
            'lesson_sections' => $this->getLessonSectionsArray(),
        ];

        return $resource;
    }
}
