<?php

namespace App\Http\Resources;

use App\Models\WorkshopUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkshopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $workshopUser = $request->user()
            ? WorkshopUser::where("user_id", $request->user()->id)
                ->where("workshop_id", $this->id)
                ->first()
            : null;

        $resource = [
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
            "short_description" => $this->short_description,
            "description" => $this->description,
            "image_url" => $this->image_url,
            "video_url" => $this->video_url,
            "difficulty" => $this->difficulty,
            "duration_in_minutes" => $this->duration_in_minutes,
            "status" => $this->status,
            "is_standalone" => $this->is_standalone,
            "is_premium" => $this->is_premium,
            "lesson_sections" => $this->whenLoaded(
                "lessons",
                $this->getLessonSectionsArray()
            ),
            "lessons" => $this->lessons->groupBy('section')->map(function ($lessons, $section) {
                return LessonResource::collection($lessons);
            }),
            "challenge" => $this->challenge,
            "next_lesson" => $this->next_lesson,
            "instructor" => new InstructorResource(
                $this->whenLoaded("instructor")
            ),
            "tags" => TagResource::collection($this->whenLoaded("tags")),
            "streaming_url" => $this->streaming_url,
            "resources" => $this->resources,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "published_at" => $this->published_at,
            "workshop_user" => $workshopUser
                ? new WorkshopUserResource($workshopUser)
                : null,
        ];

        if ($this->lessons_count) {
            $resource["lessons_count"] = $this->lessons_count;
        }

        return $resource;
        // return parent::toArray($request);
    }
}
