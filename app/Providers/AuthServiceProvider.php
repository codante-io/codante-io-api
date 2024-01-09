<?php

namespace App\Providers;

use App\Models\Lesson;
use App\Models\User;
use App\Policies\LessonPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Lesson::class => LessonPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        ResetPassword::createUrlUsing(function (
            object $notifiable,
            string $token
        ) {
            return config("app.frontend_url") .
                "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        Gate::define("viewPulse", function (User $user) {
            return $user->isAdmin();
        });

        //
    }
}
