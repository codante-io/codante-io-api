<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use CrudTrait, HasFactory, SoftDeletes;

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function validCoupon($couponCode)
    {
        return $this->coupon()
            ->where('code', $couponCode)
            ->where('active', true)
            ->where('expires_at', '>', now())
            ->whereColumn('redemptions', '<', 'max_redemptions')
            ->first();
    }
}
