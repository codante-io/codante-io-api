<?php

namespace App\Http\Resources;

use App\Models\User;
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
            "id" => $this->id,
            "submission_image_url" => $this->submission_image_url,
            "avatar" => new UserAvatarResource(
                $this->user
                    ->query()
                    ->select("avatar_url", "name", "is_pro", "is_admin")
                    ->find($this->user_id)
            ),
            "challenge" => $this->whenLoaded("challenge", [
                "name" => $this->challenge->name,
                "slug" => $this->challenge->slug,
            ]),
        ];
    }
}
