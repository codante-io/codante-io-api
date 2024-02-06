<?php

namespace App\Observers;

use App\Models\Certificate;
use App\Notifications\Discord;

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
        $status = $certificate->status;
        $message =
            $status == "published"
                ? "Projeto aprovado e certificado atualizado ✅"
                : "Projeto não aprovado ❌";

        new Discord(
            "Certificado ID: {$certificate->id} atualizado. $message",
            "pedidos-certificados"
        );
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
