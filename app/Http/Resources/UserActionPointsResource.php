<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserActionPointsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "avatar" => new UserAvatarResource($this),
            "points" => $this->points,
            "completed_challenge_count" => $this->completed_challenge_count,
            "received_reaction_count" => $this->received_reaction_count,
        ];
    }
}
