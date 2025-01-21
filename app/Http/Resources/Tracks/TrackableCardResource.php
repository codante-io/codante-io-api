<?php

namespace App\Http\Resources\Tracks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackableCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return match ($this->pivot->trackable_type) {
            'App\\Models\\Workshop' => (new WorkshopTrackableCard(
                $this
            ))->toArray($request),
            'App\\Models\\Challenge' => (new ChallengeTrackableCard(
                $this, $this->track_slug
            ))->toArray($request),
            default => [
                'id' => $this->pivot->id,
                'name' => $this->name,
                'slug' => $this->slug,
                'type' => $this->pivot->trackable_type,
                'short_description' => $this->short_description,
                'image_url' => $this->image_url,
                'video_url' => $this->video_url,
                'track_slug' => $this->track_slug,
            ],
        };

    }
}
