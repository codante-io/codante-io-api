<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkshopUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = [
            "id" => $this->id,
            "user_id" => $this->user_id,
            "workshop_id" => $this->workshop_id,
            "status" => $this->status,
            "completed_at" => $this->completed_at,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "certificate" => $this->whenLoaded("certificate")
                ? new CertificateResource($this->certificate)
                : null,
        ];

        return $resource;
        // return parent::toArray($request);
    }
}
