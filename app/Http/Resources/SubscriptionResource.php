<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
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
            "plan_name" => $this->plan->name ?? null,
            "status" => $this->status,
            "translated_status" => $this->translatedStatus(),
            "translated_payment_method" => $this->translatedPaymentMethod(),
            "payment_method" => $this->payment_method,
            "boleto_url" => $this->boleto_url,
            "price_paid_in_cents" => $this->price_paid_in_cents,
            "acquisition_type" => $this->acquisition_type,
            "starts_at" => $this->starts_at,
            "ends_at" => $this->ends_at,
            "canceled_at" => $this->canceled_at,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
