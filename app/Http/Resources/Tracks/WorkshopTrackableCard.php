<?php

namespace App\Http\Resources\Tracks;

use App\Http\Resources\SidebarLessonCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkshopTrackableCard extends JsonResource
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

        // get track slug
        $baseUrl = '/trilhas/'.$this->track_slug.'/modulo/'.$this->slug;

        $resource = [
            'id' => $this->id,
            'name' => $this->name,
            'type' => 'workshop',
            'slug' => $this->slug,
            'video_url' => $this->video_url,

            'lessons' => $lessons
                ? new SidebarLessonCollection($lessons, $baseUrl)
                : [],
            'lesson_sections' => $this->getLessonSectionsArray(),
        ];

        return $resource;
    }
}
