<?php

namespace App\Http\Resources\Tracks;

use App\Http\Resources\SidebarLessonCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeTrackableCard extends JsonResource
{
    protected $trackSlug;

    public function __construct($resource, $trackSlug)
    {
        parent::__construct($resource);
        $this->trackSlug = $trackSlug;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $solutionLessons = $this->lessons()
            ->select('id', 'name', 'slug', 'type')
            ->where('type', 'solution')
            ->get();

        $solutionBaseUrl = "/trilhas/{$this->trackSlug}/projeto/{$this->slug}";

        $resource = [
            'id' => $this->id,
            'type' => 'challenge',
            'name' => $this->name,
            'slug' => $this->slug,
            'video_url' => $this->video_url,
            'track_lessons' => $this->getTrackLessons($this->trackSlug),
            'solution' => [
                'lessons' => $solutionLessons
                    ? new SidebarLessonCollection($solutionLessons, $solutionBaseUrl)
                    : [],
                'lesson_sections' => $this->getLessonSectionsArray(),
            ],
        ];

        return $resource;
    }
}
