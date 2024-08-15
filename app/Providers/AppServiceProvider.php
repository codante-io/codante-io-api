<?php

namespace App\Providers;

use App\Models\Certificate;
use App\Observers\CertificateObserver;
use Illuminate\Support\ServiceProvider;
use URL;

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

        // only use https in production
        // if ($this->app->environment("production")) {
        //     URL::forceScheme("https");
        // }
    }
}
