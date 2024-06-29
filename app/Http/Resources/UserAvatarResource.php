<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAvatarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "avatar_url" => $this->avatar_url,
            "name" => $this->whenNotNull($this->name),
            "badge" => $this->getBadgeName(),
            "github_user" => $this->whenNotNull($this->github_user),
        ];
    }

    private function getBadgeName()
    {
        // if user dont want to show badge
        if ($this->settings && $this->settings["show_badge"] === false) {
            return null;
        }

        if ($this->is_admin) {
            return "admin";
        }

        if ($this->is_pro) {
            return "pro";
        }

        return null;
    }
}
