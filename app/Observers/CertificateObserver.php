<?php

namespace App\Observers;

use App\Models\Certificate;
use App\Notifications\Discord;
use Notification;

class CertificateObserver
{
    /**
     * Handle the Certificate "created" event.
     */
    public function created(Certificate $certificate): void
    {
        //
    }

    /**
     * Handle the Certificate "updated" event.
     */
    public function updated(Certificate $certificate)
    {
        $prevStatus = $certificate->getOriginal("status");
        $status = $certificate->status;

        if ($status !== $prevStatus) {
            $user = $certificate->user;
            $certifiable = $certificate->certifiable;

            event(
                new \App\Events\AdminPublishedCertificate(
                    $user,
                    $certificate,
                    $certifiable
                )
            );
        }
    }

    /**
     * Handle the Certificate "deleted" event.
     */
    public function deleted(Certificate $certificate): void
    {
        //
    }

    /**
     * Handle the Certificate "restored" event.
     */
    public function restored(Certificate $certificate): void
    {
        //
    }

    /**
     * Handle the Certificate "force deleted" event.
     */
    public function forceDeleted(Certificate $certificate): void
    {
        //
    }
}
