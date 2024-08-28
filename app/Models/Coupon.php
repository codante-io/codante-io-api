<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $guarded = ['id'];

    public function getValidCoupon($couponCode, $planId = null)
    {
        return $this->where('code', $couponCode)
            ->where('active', true)
            ->where('expires_at', '>', now())
            ->whereColumn('redemptions', '<', 'max_redemptions')
            ->where('plan_id', $planId)
            ->first();
    }

    public function markAsUsed()
    {
        $this->redemptions++;
        $this->save();
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
