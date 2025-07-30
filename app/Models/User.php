<?php

namespace App\Models;

use App\Services\Mail\EmailOctopusService;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Carbon\Carbon;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Laravel\Sanctum\HasApiTokens;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;
use Storage;
use Tuupola\Base62;

class User extends Authenticatable
{
    use CrudTrait;
    use HasApiTokens;
    use HasEagerLimit;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $guarded = [
        'id',
        'github_user',
        'linkedin_user',
        'discord_user',
        'discord_data',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'discord_data',
        'github_data',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'settings' => 'array',
        'discord_data' => 'array',
        'github_data' => 'array',
        'is_pro' => 'boolean',
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
            $user->email = $user->email.'-deleted';
            $user->save();
        });
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function challenges()
    {
        return $this->belongsToMany(Challenge::class)
            ->withPivot(['completed', 'fork_url', 'joined_discord'])
            ->withTimestamps();
    }

    public function trackables()
    {
        return $this->belongsToMany(
            Trackable::class,
            'trackable_user',
            'user_id',
            'trackable_id'
        );
    }

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class)->withPivot(['completed_at']);
    }

    public function challengeUsers()
    {
        return $this->hasMany(ChallengeUser::class);
    }

    public function workshopUsers()
    {
        return $this->hasMany(WorkshopUser::class);
    }

    public function workshops()
    {
        return $this->belongsToMany(Workshop::class, 'workshop_user')
            ->withPivot(['status', 'completed_at', 'percentage_completed'])
            ->withTimestamps();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscribeToPlan(
        $planId,
        $providerId = null,
        $acquisitionType = 'purchase',
        $status = 'pending',
        $paymentMethod = null,
        $boletoUrl = null,
        $pricePaidInCents = null
    ): Subscription {
        $plan = Plan::findOrFail($planId);

        $subscription = new Subscription;
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

        if ($status === 'active') {
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
                ->where('status', 'active')
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
        $emailOctopus = new EmailOctopusService;
        $emailOctopus->deleteUser($this);
    }

    public function isAdmin(): bool
    {
        return $this->is_admin == 1;
    }

    public function changeAvatar(UploadedFile $avatar)
    {
        $base62 = new Base62;
        $encodedEmail = $base62->encode($this->email);

        // reduce image size
        $image = Image::read($avatar->getRealPath())
            ->resize(600, 600, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->toAvif(80);

        // path: user-avatars/encodedemail/randomstring.avif
        $avatarPath =
            'user-avatars/'.$encodedEmail.'/'.Str::random(10).'.avif';

        Storage::disk('s3')->put($avatarPath, $image);
        $avatarUrl = config('app.frontend_assets_url').'/'.$avatarPath;

        $settings = $this->settings;
        $settings['changed_avatar'] = Carbon::now();
        $this->settings = $settings;

        $this->avatar_url = $avatarUrl;
        $this->save();

        // delete old avatar
        $files = Storage::disk('s3')->files('user-avatars/'.$encodedEmail);

        // delete all, except the one we just uploaded
        foreach ($files as $file) {
            if ($file !== $avatarPath) {
                Storage::disk('s3')->delete($file);
            }
        }
    }
}
