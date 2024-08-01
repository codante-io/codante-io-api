<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    public function getValidCoupon($couponCode, $planId = null)
    {
        return $this->where("code", $couponCode)
            ->where("active", true)
            ->where("expires_at", ">", now())
            ->whereColumn("redemptions", "<", "max_redemptions")
            ->where("plan_id", $planId)
            ->first();
    }

    public function markAsUsed()
    {
        $this->redemptions++;
        $this->save();
    }
}
