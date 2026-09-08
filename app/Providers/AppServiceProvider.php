<?php

namespace App\Providers;

use App\Support\VendorContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\URL;

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
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\Event::subscribe(\App\Listeners\AuditAuthenticationListener::class);

        \Illuminate\Support\Facades\Mail::extend('brevo', function (array $config = []) {
            $transportFactory = new \Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory();
            
            return $transportFactory->create(
                new \Symfony\Component\Mailer\Transport\Dsn(
                    'brevo+api',
                    'default',
                    config('services.brevo.key')
                )
            );
        });

        RateLimiter::for('vendor-api', function (Request $request) {
            $vendor = VendorContext::current();

            return Limit::perMinute(60)->by($vendor?->id ?? $request->ip());
        });
    }
}
