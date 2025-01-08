<?php

namespace App\Providers;

use App\Events\AdminPublishedCertificate;
use App\Events\ChallengeCompleted;
use App\Events\ChallengeForked;
use App\Events\ChallengeJoined;
use App\Events\LeadRegistered;
use App\Events\PurchaseCompleted;
use App\Events\PurchaseStarted;
use App\Events\ReactionCreated;
use App\Events\ReactionDeleted;
use App\Events\UserCommented;
use App\Events\UserCompletedLesson;
use App\Events\UserErasedLesson;
use App\Events\UserJoinedWorkshop;
use App\Events\UserRequestedCertificate;
use App\Events\UsersFirstWorkshop;
use App\Events\UserStatusUpdated;
use App\Listeners\AwardPoints;
use App\Listeners\CertificatePublished;
use App\Listeners\CertificateRequested;
use App\Listeners\CommentCreated;
use App\Listeners\EmailOctopus;
use App\Listeners\LessonCompleted;
use App\Listeners\LessonRemoved;
use App\Listeners\Registered as RegisteredListener;
use App\Listeners\SendDiscordNotificationChallengeSubmitted;
use App\Listeners\SendEventToMetaPixel;
use App\Listeners\UserJoinedWorkshop as ListenersUserJoinedWorkshop;
use App\Listeners\UserStatusUpdated as UserStatusUpdatedListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEventToMetaPixel::class,
            SendEmailVerificationNotification::class,
            RegisteredListener::class,
        ],
        LeadRegistered::class => [SendEventToMetaPixel::class],
        ChallengeCompleted::class => [
            AwardPoints::class,
            SendDiscordNotificationChallengeSubmitted::class,
        ],
        ChallengeJoined::class => [AwardPoints::class, EmailOctopus::class],
        ChallengeForked::class => [AwardPoints::class],
        ReactionCreated::class => [AwardPoints::class],
        ReactionDeleted::class => [AwardPoints::class],
        UserStatusUpdated::class => [UserStatusUpdatedListener::class],
        UserCommented::class => [CommentCreated::class],
        UserRequestedCertificate::class => [CertificateRequested::class],
        AdminPublishedCertificate::class => [CertificatePublished::class],
        UserCompletedLesson::class => [LessonCompleted::class],
        UserErasedLesson::class => [LessonRemoved::class],
        PurchaseCompleted::class => [SendEventToMetaPixel::class],
        PurchaseStarted::class => [
            SendEventToMetaPixel::class,
            EmailOctopus::class,
        ],
        UsersFirstWorkshop::class => [EmailOctopus::class],
        UserJoinedWorkshop::class => [
            ListenersUserJoinedWorkshop::class,
            EmailOctopus::class,
        ],
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
