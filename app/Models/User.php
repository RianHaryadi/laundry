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

    /**
     * Role constants.
     */
    public const ROLE_OWNER   = 'owner';
    public const ROLE_ADMIN   = 'admin';
    public const ROLE_CASHIER = 'cashier';
    public const ROLE_COURIER = 'courier';

    /**
     * Get all available roles.
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_OWNER   => 'Owner',
            self::ROLE_ADMIN   => 'Admin/Administrator',
            self::ROLE_CASHIER => 'Cashier',
            self::ROLE_COURIER => 'Courier',
        ];
    }

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

    /** Role checking helpers */
    public function isOwner(): bool   { return $this->role === self::ROLE_OWNER; }
    public function isAdmin(): bool   { return $this->role === self::ROLE_ADMIN; }
    public function isCashier(): bool { return $this->role === self::ROLE_CASHIER; }
    public function isCourier(): bool { return $this->role === self::ROLE_COURIER; }

    /**
     * Universal role checker (supports old aliases)
     */
    public function hasRole(string $role): bool
    {
        $role = strtolower($role);

        $aliases = [
            'superadmin'   => self::ROLE_OWNER,
            'administrator'=> self::ROLE_ADMIN,
            'kasir'        => self::ROLE_CASHIER,  // legacy
            'kurir'        => self::ROLE_COURIER,  // legacy
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

    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canManageOutlets(): bool
    {
        return $this->isOwner();
    }

    public function canManageProducts(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canProcessOrders(): bool
    {
        return $this->isOwner() || $this->isAdmin() || $this->isCashier();
    }

    public function canDeliverOrders(): bool
    {
        return $this->isCourier();
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

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

    public function scopeCashiers($query)
    {
        return $query->where('role', self::ROLE_CASHIER);
    }

    public function scopeCouriers($query)
    {
        return $query->where('role', self::ROLE_COURIER);
    }

    public function scopeFromOutlet($query, int $outletId)
    {
        return $query->where('outlet_id', $outletId);
    }

    public function getRoleLabelAttribute(): string
    {
        return self::getRoles()[$this->role] ?? ucfirst($this->role);
    }

    public function getRoleColorAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_OWNER   => 'danger',
            self::ROLE_ADMIN   => 'warning',
            self::ROLE_CASHIER => 'info',
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
}
