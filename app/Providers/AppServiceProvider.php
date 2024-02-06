<?php

namespace App\Providers;

use App\Models\Certificate;
use App\Observers\CertificateObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Certificate::observe(CertificateObserver::class);
    }
}
