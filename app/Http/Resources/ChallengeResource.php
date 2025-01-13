<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ChallengeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $baseUrl = config('app.frontend_url')."/mini-projetos/{$this->slug}/resolucao";

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image_url' => $this->image_url,
            'video_url' => $this->video_url,
            'status' => $this->status,
            'difficulty' => $this->difficulty,
            'duration_in_minutes' => $this->duration_in_minutes,
            'repository_name' => $this->repository_name,
            'featured' => $this->featured,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'has_solution' => $this->hasSolution(),
            'is_premium' => $this->is_premium,
            'resources' => $this->resources,
            'enrolled_users_count' => $this->users_count,
            'current_user_is_enrolled' => $this->userJoined(),
            'current_user_status' => $this->userStatus(),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            // "workshop" => new WorkshopResource($this->whenLoaded("workshop")),
            'solution' => [
                'lesson_sections' => $this->whenLoaded(
                    'lessons',
                    $this->getLessonSectionsArray()
                ),
                'lessons' => $this->whenLoaded(
                    'lessons',
                    new SidebarLessonCollection($this->lessons, $baseUrl)
                ),
                'first_unwatched_lesson' => new SidebarLessonResource($this->firstUnwatchedLesson(), $baseUrl),
            ],

            'weekly_featured_start_date' => $this->weekly_featured_start_date,
            'solution_publish_date' => $this->solution_publish_date,
            'stars' => $this->stars,
            'forks' => $this->forks,
        ];
    }

    public function firstUnwatchedLesson()
    {
        $lessons = $this->lessons;

        if (! Auth::check()) {
            return $lessons->first();
        }

        foreach ($lessons as $lesson) {
            $watched = $lesson->userCompleted(Auth::id());

            if (! $watched) {
                return $lesson;
            }
        }

        return $lessons->first();
    }
}
