<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'address',
        'is_member',
        'available_coupons', // Jumlah voucher yang tersedia
        'member_since',
        'birthday',
        'preferred_outlet_id',
        'email_notifications',
        'sms_notifications',
        'notes',
        'membership_level', // bronze, silver, gold, platinum, vip
        'points', // Loyalty points (opsional, berbeda dari voucher)
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_member' => 'boolean',
        'available_coupons' => 'integer',
        'points' => 'integer',
        'member_since' => 'date',
        'birthday' => 'date',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function preferredOutlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class, 'preferred_outlet_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    /**
     * Scopes
     */
    public function scopeMembers($query)
    {
        return $query->where('is_member', true);
    }

    public function scopeNonMembers($query)
    {
        return $query->where('is_member', false);
    }

    public function scopeWithCoupons($query, int $minCoupons = 1)
    {
        return $query->where('available_coupons', '>=', $minCoupons);
    }

    public function scopeBirthdayThisMonth($query)
    {
        return $query->whereMonth('birthday', now()->month);
    }

    public function scopeBirthdayToday($query)
    {
        return $query->whereMonth('birthday', now()->month)
            ->whereDay('birthday', now()->day);
    }

    public function scopeEmailOptedIn($query)
    {
        return $query->where('email_notifications', true)
            ->whereNotNull('email');
    }

    public function scopeSmsOptedIn($query)
    {
        return $query->where('sms_notifications', true);
    }

    public function scopeWithWebAccount($query)
    {
        return $query->whereNotNull('password');
    }

    /**
     * Accessors & Computed Properties
     */
    public function getTotalSpentAttribute(): float
    {
        return $this->orders()->sum('final_price') ?? 0;
    }

    public function getFormattedTotalSpentAttribute(): string
    {
        return 'Rp ' . number_format($this->total_spent, 0, ',', '.');
    }

    public function getTotalOrdersAttribute(): int
    {
        return $this->orders()->count();
    }

    public function getMembershipColorAttribute(): string
    {
        return $this->is_member ? 'success' : 'secondary';
    }

    public function getMembershipLabelAttribute(): string
    {
        return $this->is_member ? 'Member' : 'Non-Member';
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birthday ? $this->birthday->age : null;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name . ($this->is_member ? ' ⭐' : '');
    }

    public function getFormattedCouponsAttribute(): string
    {
        return $this->available_coupons . ' Voucher';
    }

    /**
     * Business Logic Methods
     */
    
    public function isBirthdayToday(): bool
    {
        if (!$this->birthday) {
            return false;
        }

        return $this->birthday->month === now()->month 
            && $this->birthday->day === now()->day;
    }

    public function isBirthdayThisMonth(): bool
    {
        if (!$this->birthday) {
            return false;
        }

        return $this->birthday->month === now()->month;
    }

    public function isMember(): bool
    {
        return $this->is_member === true;
    }

    public function hasWebAccount(): bool
    {
        return !empty($this->password);
    }

    public function hasCoupons(int $requiredCoupons = 1): bool
    {
        return $this->available_coupons >= $requiredCoupons;
    }

    /**
     * ============================================
     * VOUCHER MANAGEMENT METHODS
     * ============================================
     */

    /**
     * Add voucher counter when free voucher is created
     * This is called from Coupon model or Order model
     */
    public function addCoupon(int $quantity = 1): bool
    {
        if (!$this->is_member) {
            return false; // Only members get vouchers
        }
        
        $this->increment('available_coupons', $quantity);
        
        \Log::info("Customer #{$this->id}: Added {$quantity} voucher(s), total now: {$this->available_coupons}");
        
        return true;
    }

    /**
     * Use/consume a voucher when order is created with coupon
     */
    public function useCoupon(int $quantity = 1): bool
    {
        if ($this->available_coupons < $quantity) {
            \Log::warning("Customer #{$this->id}: Not enough vouchers (has {$this->available_coupons}, needs {$quantity})");
            return false;
        }
        
        $this->decrement('available_coupons', $quantity);
        
        \Log::info("Customer #{$this->id}: Used {$quantity} voucher(s), remaining: {$this->available_coupons}");
        
        return true;
    }

    /**
     * Get all unused FREE vouchers for this customer
     */
    public function getAvailableFreeVouchers()
    {
        return Coupon::where('customer_id', $this->id)
            ->where('discount_type', 'percentage')
            ->where('discount_value', 100)
            ->where('used_count', '<', \DB::raw('usage_limit'))
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->get();
    }

    /**
     * Get completed orders count (for progress tracking)
     */
    public function getCompletedOrdersCount(): int
    {
        return $this->orders()
            ->whereIn('status', ['completed', 'picked_up', 'in_delivery'])
            ->count();
    }

    /**
     * Get progress to next free voucher
     * Returns: ['progress' => 3, 'remaining' => 3, 'percentage' => 50]
     */
    public function getFreeVoucherProgress(): array
    {
        if (!$this->is_member) {
            return [
                'progress' => 0,
                'remaining' => 6,
                'percentage' => 0,
            ];
        }

        $completedOrders = $this->getCompletedOrdersCount();
        $progress = $completedOrders % 6;
        $remaining = 6 - $progress;
        $percentage = ($progress / 6) * 100;

        return [
            'progress' => $progress,
            'remaining' => $remaining,
            'percentage' => round($percentage, 2),
            'total_completed' => $completedOrders,
        ];
    }

    /**
     * Check if customer is eligible for free voucher reward
     */
    public function isEligibleForFreeVoucher(): bool
    {
        if (!$this->is_member) {
            return false;
        }

        $completedOrders = $this->getCompletedOrdersCount();
        
        return ($completedOrders % 6) === 0 && $completedOrders >= 6;
    }

    /**
     * Membership Management
     */
    
    public function activateMembership(): void
    {
        if (!$this->is_member) {
            $this->update([
                'is_member' => true,
                'member_since' => now(),
            ]);
        }
    }

    public function deactivateMembership(): void
    {
        $this->update([
            'is_member' => false,
            'member_since' => null,
            'available_coupons' => 0, // Reset vouchers
        ]);
    }

    /**
     * Notification Preferences
     */
    
    public function hasEmail(): bool
    {
        return !empty($this->email);
    }

    public function canReceiveEmailNotifications(): bool
    {
        return $this->email_notifications && $this->hasEmail();
    }

    public function canReceiveSmsNotifications(): bool
    {
        return $this->sms_notifications;
    }

    /**
     * Membership Duration
     */
    
    public function getMembershipDurationAttribute(): ?int
    {
        return $this->member_since ? $this->member_since->diffInDays(now()) : null;
    }

    public function getMembershipDurationHumanAttribute(): ?string
    {
        return $this->member_since ? $this->member_since->diffForHumans(null, true) : null;
    }
}