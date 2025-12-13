<?php

namespace App\Providers;

use App\Models\Payment;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Observers\PaymentObserver;
use App\Observers\AuditLogObserver;
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
    public function boot(): void
    {
        // Existing observer
        Payment::observe(PaymentObserver::class);

        // Audit Log Observers
        Order::observe(AuditLogObserver::class);
        Service::observe(AuditLogObserver::class);
        User::observe(AuditLogObserver::class);
        // Tambahkan model lain yang ingin dicatat audit log-nya
    }
}
