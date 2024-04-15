<?php

namespace App\Models;

use App\Services\Mail\EmailOctopusService;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    use SoftDeletes;

    protected $guarded = [
        "id",
        "github_user",
        "linkedin_user",
        "discord_user",
        "discord_data",
    ];

    protected $hidden = [
        "password",
        "remember_token",
        "discord_data",
        "github_data",
    ];

    protected $casts = [
        "email_verified_at" => "datetime",
        "settings" => "array",
        "discord_data" => "array",
        "github_data" => "array",
    ];

    protected static function booted()
    {
        // Quando vamos deletar um usuário, precisamos:
        static::deleting(function ($user) {
            // remove user from all challenges
            $user->challenges()->detach();

            // remove user from all lessons
            $user->lessons()->detach();

            // remove user from all subscriptions
            $user->subscriptions()->delete();

            // remove user from email lists
            $user->removeFromEmailLists();

            // add deleted to user email (to avoid email conflicts when user resubscribe)
            $user->email = $user->email . "-deleted";
            $user->save();
        });
    }

    public function challenges()
    {
        return $this->belongsToMany(Challenge::class)
            ->withPivot(["completed", "fork_url", "joined_discord"])
            ->withTimestamps();
    }

    public function trackables()
    {
        return $this->belongsToMany(
            Trackable::class,
            "trackable_user",
            "user_id",
            "trackable_id"
        );
    }

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class)->withPivot(["completed_at"]);
    }

    public function workshops()
    {
        return $this->belongsToMany(Workshop::class, "workshop_user")
            ->withPivot(["status", "completed_at"])
            ->withTimestamps();
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
    ): Subscription {
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

        if ($status === "active") {
            $this->upgradeUserToPro();
        }

        return $subscription;
    }

    public function upgradeUserToPro()
    {
        $this->is_pro = true;
        $this->save();

        // dispatch the event
        event(new \App\Events\UserStatusUpdated($this));
    }

    public function downgradeUserFromPro()
    {
        // do not downgrade if there is an active subscription
        if (
            $this->subscriptions()
                ->where("status", "active")
                ->count() > 0
        ) {
            return;
        }

        $this->is_pro = false;
        $this->save();

        // dispatch the event
        event(new \App\Events\UserStatusUpdated($this));
    }

    public function removeFromEmailLists()
    {
        $emailOctopus = new EmailOctopusService();
        $emailOctopus->deleteUser($this);
    }

    public function isAdmin(): bool
    {
        return $this->is_admin == 1;
    }
}
