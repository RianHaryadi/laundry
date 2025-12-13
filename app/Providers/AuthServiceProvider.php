<?php

namespace App\Providers;

use App\Models\AuditLog;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

// Import Models
use App\Models\Order;
use App\Models\Machine;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\Outlet;
use App\Models\OutletPrice;
use App\Models\User;
use App\Models\Maintenance;

// Import Policies
use App\Policies\OrderPolicy;
use App\Policies\MachinePolicy;
use App\Policies\ServicePolicy;
use App\Policies\CustomerPolicy;
use App\Policies\CouponPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\OutletPolicy;
use App\Policies\OutletPricePolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\UserPolicy;
use App\Policies\MaintenancePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Order::class => OrderPolicy::class,
        Machine::class => MachinePolicy::class,
        Service::class => ServicePolicy::class,
        Customer::class => CustomerPolicy::class,
        Coupon::class => CouponPolicy::class,
        Payment::class => PaymentPolicy::class,
        Outlet::class => OutletPolicy::class,
        Maintenance::class => MaintenancePolicy::class,
        OutletPrice::class => OutletPricePolicy::class,
        AuditLog::class => AuditLogPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define additional gates if needed
        Gate::define('manage-outlets', function ($user) {
            return $user->canManageOutlets();
        });

        Gate::define('manage-users', function ($user) {
            return $user->canManageUsers();
        });

        Gate::define('access-audit-logs', function ($user) {
            return $user->canAccessAuditLogs();
        });

        Gate::define('manage-machines', function ($user) {
            return $user->canManageMachines();
        });

        Gate::define('manage-services', function ($user) {
            return $user->canManageServices();
        });

        Gate::define('manage-payments', function ($user) {
            return $user->canManagePayments();
        });
    }
}