<?php

namespace App\Http\Resources;

use App\Models\Comment;
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
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'challenge' => new ChallengeSummaryResource(
                $this->whenLoaded('challenge')
            ),
            'submission_url' => $this->submission_url,
            'fork_url' => $this->canViewForkUrl() ? $this->fork_url : null,
            'joined_discord' => $this->joined_discord,
            'completed' => $this->completed,
            'completed_at' => $this->completed_at,
            'is_pro' => $this->user->is_pro,
            'submission_image_url' => $this->submission_image_url,
            'reactions' => Reaction::getReactions(
                'App\\Models\\ChallengeUser',
                $this->id
            ),
            'avatar' => new UserAvatarResource($this->user),
            'is_solution' => $this->is_solution,
            'submitted_at' => $this->submitted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'linkedin_user' => $this->user->linkedin_user,
            'certificate' => $this->whenLoaded('certificate')
                ? new CertificateResource($this->certificate)
                : null,
            'comments' => Comment::getComments(
                'App\\Models\\ChallengeUser',
                $this->id
            ),
            'listed' => $this->listed,
        ];
    }

    private function canViewForkUrl(): bool
    {
        if (! $this->is_solution) {
            return true;
        }
        if (! Auth::check()) {
            return false;
        }

        return true;
    }
}
