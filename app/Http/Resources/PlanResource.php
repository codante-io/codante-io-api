<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource === null) {
            return [];
        }

        return [
            "id" => $this->id,
            "name" => $this->name,
            "price_in_cents" => $this->price_in_cents,
            "details" => $this->details,
            "slug" => $this->slug,
        ];
    }
}
