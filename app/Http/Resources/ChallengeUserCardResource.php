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
            'avatar' => new UserAvatarResource(
                $this->user
                    ->query()
                    ->select(
                        'avatar_url',
                        'name',
                        'github_user',
                        'is_pro',
                        'is_admin',
                        'settings'
                    )
                    ->find($this->user_id)
            ),
            'challenge' => $this->whenLoaded('challenge', [
                'name' => $this->challenge->name,
                'slug' => $this->challenge->slug,
            ]),
            'user' => $this->whenLoaded('user', [
                'name' => $this->user->name,
                'github_user' => $this->user->github_user,
            ]),
        ];
    }
}
