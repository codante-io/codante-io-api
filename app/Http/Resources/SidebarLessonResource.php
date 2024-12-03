<?php

namespace App\Http\Resources;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class SidebarLessonResource extends JsonResource
{
    private $baseUrl;

    public function __construct($resource, $baseUrl)
    {
        parent::__construct($resource);
        $this->baseUrl = $baseUrl;
    }

    public function toArray(Request $request): array
    {
        $url = $this->baseUrl . "/" . $this->slug;

        return [
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
            "url" => $url,
            "user_completed" => $this->userCompleted(
                Auth::guard("sanctum")->id()
            ),
            "open" => $this->canViewVideo(),
        ];
    }

    private function canViewVideo(): bool
    {
        if (!Auth::check()) {
            if ($this->available_to === "all") {
                return true;
            }

            return false;
        }

        return Auth::user()->can("view", $this->resource);
    }
}
