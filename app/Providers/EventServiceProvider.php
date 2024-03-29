<?php

namespace App\Providers;

use App\Events\AdminPublishedCertificate;
use App\Events\ChallengeCompleted;
use App\Events\ChallengeForked;
use App\Events\ChallengeJoined;
use App\Events\ReactionCreated;
use App\Events\ReactionDeleted;
use App\Events\UserCommented;
use App\Events\UserCompletedLesson;
use App\Events\UserErasedLesson;
use App\Events\UserRequestedCertificate;
use App\Events\UserStatusUpdated;
use App\Listeners\AwardPoints;
use App\Listeners\CertificatePublished;
use App\Listeners\CertificateRequested;
use App\Listeners\CommentCreated;
use App\Listeners\LessonRemoved;
use App\Listeners\Registered as RegisteredListener;
use App\Listeners\SendDiscordNotificationChallengeSubmitted;
use App\Listeners\UserStatusUpdated as UserStatusUpdatedListener;
use App\Listeners\WorkshopUserCreated;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
            RegisteredListener::class,
        ],
        ChallengeCompleted::class => [
            AwardPoints::class,
            SendDiscordNotificationChallengeSubmitted::class,
        ],
        ChallengeJoined::class => [AwardPoints::class],
        ChallengeForked::class => [AwardPoints::class],
        ReactionCreated::class => [AwardPoints::class],
        ReactionDeleted::class => [AwardPoints::class],
        UserStatusUpdated::class => [UserStatusUpdatedListener::class],
        UserCommented::class => [CommentCreated::class],
        UserRequestedCertificate::class => [CertificateRequested::class],
        AdminPublishedCertificate::class => [CertificatePublished::class],
        UserCompletedLesson::class => [WorkshopUserCreated::class],
        UserErasedLesson::class => [LessonRemoved::class],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
