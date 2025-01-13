<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkshopCardResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'video_url' => $this->video_url,
            'duration_in_seconds' => $this->lessons_sum_duration_in_seconds,
            'status' => $this->status,
            'is_standalone' => $this->is_standalone,
            'is_premium' => $this->is_premium,
            'lessons_count' => $this->lessons_count,
            'instructor' => new InstructorCardResource(
                $this->whenLoaded('instructor')
            ),
            'streaming_url' => $this->streaming_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'published_at' => $this->published_at,
            'pivot' => $this->whenPivotLoaded('workshop_user', function () {
                return [
                    'status' => $this->pivot->status,
                    'completed_at' => $this->pivot->completed_at,
                    'started_at' => $this->pivot->created_at,
                    'percentage_completed' => $this->pivot->percentage_completed,
                ];
            }),
        ];

        return $resource;
    }
}
