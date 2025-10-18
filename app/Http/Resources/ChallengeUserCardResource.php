<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeUserCardResource extends JsonResource
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
            'submission_image_url' => $this->submission_image_url,
            'avatar' => $this->when(
                $this->relationLoaded('user') && $this->user,
                fn () => new UserAvatarResource($this->user)
            ),
            'challenge' => $this->whenLoaded('challenge', function () {
                return [
                    'name' => $this->challenge->name,
                    'slug' => $this->challenge->slug,
                ];
            }),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'name' => $this->user->name,
                    'github_user' => $this->user->github_user,
                ];
            }),
        ];
    }
}
