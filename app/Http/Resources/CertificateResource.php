<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificateResource extends JsonResource
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
            "metadata" => $this->metadata,
            "certifiable_type" => $this->certifiable_type,
            "certifiable_id" => $this->certifiable_id,
            "status" => $this->status,
            "user" => new UserResource($this->whenLoaded("user")),
            "certifiable" => $this->whenLoaded("certifiable"),
            "created_at" => $this->created_at,
        ];
    }
}
