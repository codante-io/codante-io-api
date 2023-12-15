<?php

namespace App\Http\Resources;

use App\Models\Reaction;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeUserResource extends JsonResource
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
            "user_name" => $this->user->name,
            "user_id" => $this->user->id,
            "user_github_user" => $this->user->github_user,
            "submission_url" => $this->submission_url,
            "fork_url" => $this->canViewForkUrl() ? $this->fork_url : null,
            "is_pro" => $this->user->is_pro,
            "submission_image_url" => $this->submission_image_url,
            "reactions" => Reaction::getReactions(
                "App\\Models\\ChallengeUser",
                $this->id
            ),
            "avatar" => new UserAvatarResource($this->user),
            "is_solution" => $this->is_solution,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "linkedin_user" => $this->user->linkedin_user,
        ];
    }

    private function canViewForkUrl(): bool
    {
        if (!$this->is_solution) {
            return true;
        }
        if (!Auth::check()) {
            return false;
        }
        if (!Auth::user()->is_pro) {
            return false;
        }
        return true;
    }
}
