<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

class User extends Authenticatable
{
    use HasEagerLimit;
    use CrudTrait;
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    protected $fillable = ["name", "email", "password"];

    protected $hidden = ["password", "remember_token"];

    protected $casts = [
        "email_verified_at" => "datetime",
    ];

    public function challenges()
    {
        return $this->belongsToMany(Challenge::class)
            ->withPivot(["completed", "fork_url", "joined_discord"])
            ->withTimestamps();
    }

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class)->withPivot(["completed_at"]);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscribeToPlan(
        $planId,
        $providerId = null,
        $acquisitionType = "purchase",
        $status = "pending",
        $paymentMethod = null,
        $boletoUrl = null,
        $pricePaidInCents = null
    ) {
        $plan = Plan::findOrFail($planId);

        $subscription = new Subscription();
        $subscription->user_id = $this->id;
        $subscription->plan_id = $planId;
        $subscription->provider_id = $providerId;
        $subscription->starts_at = now();

        if ($plan->duration_in_months) {
            $subscription->ends_at = now()->addMonths(
                $plan->duration_in_months
            );
        } else {
            $subscription->ends_at = null;
        }

        $subscription->status = $status;
        $subscription->payment_method = $paymentMethod;
        $subscription->boleto_url = $boletoUrl;
        $subscription->price_paid_in_cents = $pricePaidInCents;
        $subscription->acquisition_type = $acquisitionType;
        $subscription->save();
    }

    public function upgradeUserToPro()
    {
        $this->is_pro = true;
        $this->save();
    }

    public function downgradeUserFromPro()
    {
        $this->is_pro = false;
        $this->save();
    }
}
