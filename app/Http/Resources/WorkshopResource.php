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
        $baseUrl = config("app.frontend_url") . "/workshops/{$this->slug}";

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
            "duration_in_seconds" => $this->lessons_sum_duration_in_seconds,
            "status" => $this->status,
            "is_standalone" => $this->is_standalone,
            "is_premium" => $this->is_premium,
            "lesson_sections" => $this->whenLoaded(
                "lessons",
                $this->getLessonSectionsArray()
            ),
            "lessons" => $this->whenLoaded(
                "lessons",
                new SidebarLessonCollection($this->lessons, $baseUrl)
            ),

            "challenge" => $this->challenge,
            "first_unwatched_lesson" => new SidebarLessonResource(
                $this->first_unwatched_lesson,
                $baseUrl
            ),
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
