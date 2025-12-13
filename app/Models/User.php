<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'outlet_id',
        'phone',
        'address',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // ========== ROLE CONSTANTS ==========
    public const ROLE_OWNER   = 'owner';
    public const ROLE_ADMIN   = 'admin';
    public const ROLE_STAFF   = 'staff';
    public const ROLE_COURIER = 'courier';

    /**
     * Get all available roles.
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_OWNER   => 'Owner',
            self::ROLE_ADMIN   => 'Admin/Administrator',
            self::ROLE_STAFF   => 'Staff',
            self::ROLE_COURIER => 'Courier',
        ];
    }

    // ========== RELATIONSHIPS ==========
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function ordersAsCourier(): HasMany
    {
        return $this->hasMany(Order::class, 'courier_id');
    }

    public function trackings(): HasMany
    {
        return $this->hasMany(Tracking::class, 'courier_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // ========== ROLE CHECKING HELPERS ==========
    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isCourier(): bool
    {
        return $this->role === self::ROLE_COURIER;
    }

    /**
     * Universal role checker (supports old aliases)
     */
    public function hasRole(string $role): bool
    {
        $role = strtolower($role);

        $aliases = [
            'superadmin'    => self::ROLE_OWNER,
            'administrator' => self::ROLE_ADMIN,
            'kasir'         => self::ROLE_STAFF,  // legacy
            'kurir'         => self::ROLE_COURIER, // legacy
        ];

        $finalRole = $aliases[$role] ?? $role;

        return $this->role === $finalRole;
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) return true;
        }
        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->isOwner() || $this->isAdmin();
    }

    // ========== FILAMENT PANEL ACCESS ==========
    public function canAccessPanel($panel): bool
    {
        // All active users can access Filament admin panel
        return $this->is_active && in_array($this->role, [
            self::ROLE_OWNER,
            self::ROLE_ADMIN,
            self::ROLE_STAFF,
            self::ROLE_COURIER
        ]);
    }

    // ========== NAVIGATION & MENU PERMISSIONS ==========
    public function canAccessDashboard(): bool
    {
        return true; // All roles can access dashboard
    }

    // Machine Management
    public function canAccessMachines(): bool
    {
        return $this->hasAnyRole(['owner', 'admin', 'staff']);
    }

    public function canManageMachines(): bool
    {
        return $this->hasAnyRole(['owner', 'admin']);
    }

    public function canAccessMaintenance(): bool
    {
        return $this->hasAnyRole(['owner', 'admin']);
    }

    // Order Management
    public function canAccessOrders(): bool
    {
        return true; // All roles can access orders (with different permissions)
    }

    public function canManageOrders(): bool
    {
        return $this->hasAnyRole(['owner', 'admin', 'staff']);
    }

    public function canCreateOrders(): bool
    {
        return $this->hasAnyRole(['owner', 'admin', 'staff']);
    }

    public function canDeleteOrders(): bool
    {
        return $this->hasAnyRole(['owner', 'admin']);
    }

    public function canAccessOrderItems(): bool
    {
        return $this->hasAnyRole(['owner', 'admin', 'staff']);
    }

    public function canAccessTracking(): bool
    {
        return true; // All roles can access tracking
    }

    public function canManageTracking(): bool
    {
        return $this->hasAnyRole(['owner', 'admin', 'staff', 'courier']);
    }

    public function canAccessPayments(): bool
    {
        return $this->hasAnyRole(['owner', 'admin']);
    }

    public function canManagePayments(): bool
    {
        return $this->hasAnyRole(['owner', 'admin']);
    }

    // Management Section
    public function canAccessServices(): bool
    {
        return true; // All can view services
    }

    public function canManageServices(): bool
    {
        return $this->hasAnyRole(['owner', 'admin']);
    }

    public function canAccessCoupons(): bool
    {
        return $this->hasAnyRole(['owner', 'admin']);
    }

    public function canManageCoupons(): bool
    {
        return $this->hasAnyRole(['owner', 'admin']);
    }

    public function canAccessCustomers(): bool
    {
        return $this->hasAnyRole(['owner', 'admin', 'staff']);
    }

    public function canManageCustomers(): bool
    {
        return $this->hasAnyRole(['owner', 'admin', 'staff']);
    }

    public function canAccessOutlets(): bool
    {
        return $this->isOwner();
    }

    public function canManageOutlets(): bool
    {
        return $this->isOwner();
    }

    public function canAccessOutletPrices(): bool
    {
        return $this->isOwner();
    }

    public function canManageOutletPrices(): bool
    {
        return $this->isOwner();
    }

    public function canAccessUsers(): bool
    {
        return $this->isOwner();
    }

    public function canManageUsers(): bool
    {
        return $this->isOwner();
    }

    // System Section
    public function canAccessAuditLogs(): bool
    {
        return $this->isOwner();
    }

    // ========== LEGACY PERMISSIONS (Backward Compatibility) ==========
    public function canManageProducts(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canProcessOrders(): bool
    {
        return $this->isOwner() || $this->isAdmin() || $this->isStaff();
    }

    public function canDeliverOrders(): bool
    {
        return $this->isCourier();
    }

    // ========== STATUS HELPERS ==========
    public function isActive(): bool
    {
        return $this->is_active;
    }

    // ========== QUERY SCOPES ==========
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeOwners($query)
    {
        return $query->where('role', self::ROLE_OWNER);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    public function scopeStaffs($query)
    {
        return $query->where('role', self::ROLE_STAFF);
    }

    public function scopeCouriers($query)
    {
        return $query->where('role', self::ROLE_COURIER);
    }

    public function scopeFromOutlet($query, int $outletId)
    {
        return $query->where('outlet_id', $outletId);
    }

    // ========== ATTRIBUTES ==========
    public function getRoleLabelAttribute(): string
    {
        return self::getRoles()[$this->role] ?? ucfirst($this->role);
    }

    public function getRoleColorAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_OWNER   => 'danger',
            self::ROLE_ADMIN   => 'warning',
            self::ROLE_STAFF   => 'info',
            self::ROLE_COURIER => 'success',
            default            => 'secondary',
        };
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->role_label})";
    }

    public function getTotalDeliveriesAttribute(): int
    {
        return $this->ordersAsCourier()
            ->where('status', 'delivered')
            ->count();
    }

    public function getActiveDeliveriesAttribute(): int
    {
        return $this->ordersAsCourier()
            ->whereIn('status', ['processing', 'delivering'])
            ->count();
    }

    // ========== OUTLET SCOPE HELPER ==========
    /**
     * Check if user can access data from specific outlet
     */
    public function canAccessOutletData(int $outletId): bool
    {
        // Owner can access all outlets
        if ($this->isOwner()) {
            return true;
        }

        // Others can only access their own outlet
        return $this->outlet_id === $outletId;
    }

    /**
     * Get outlets accessible by this user
     */
    public function getAccessibleOutlets()
    {
        if ($this->isOwner()) {
            return Outlet::all();
        }

        return Outlet::where('id', $this->outlet_id)->get();
    }
}