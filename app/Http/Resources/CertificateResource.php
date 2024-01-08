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
            "source_type" => $this->source_type,
            "status" => $this->status,
            "username" => $this->user->name,
            $this->source_type === 'workshop' ? "workshop_id" : "challenge_id" => $this->{$this->source_type . "_id"},
        ];
    }
}
