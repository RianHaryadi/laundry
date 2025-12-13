<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'max_discount',
        'min_order',
        'max_uses',
        'max_uses_per_user',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'is_public',
        'first_order_only',
        'exclude_discounted_items',
        // TAMBAHAN: Untuk membedakan coupon promo vs coupon reward member
        'is_member_reward', // Apakah ini kupon reward untuk member
        'outlet_id', // Jika kupon hanya berlaku untuk outlet tertentu
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_order' => 'decimal:2',
        'max_uses' => 'integer',
        'max_uses_per_user' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'first_order_only' => 'boolean',
        'exclude_discounted_items' => 'boolean',
        'is_member_reward' => 'boolean', // TAMBAHAN
    ];

    /**
     * Relationships
     */

    /**
     * Get the outlets that can use this coupon.
     */
    public function outlets(): BelongsToMany
    {
        return $this->belongsToMany(Outlet::class, 'coupon_outlet');
    }

    /**
     * Get the outlet if coupon is specific to one outlet.
     */
    public function outlet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * Get the orders that used this coupon.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scopes
     */

    /**
     * Scope a query to only include active coupons.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')
                    ->orWhereColumn('used_count', '<', 'max_uses');
            });
    }

    /**
     * Scope a query to only include expired coupons.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<', now());
    }

    /**
     * Scope a query to only include public coupons.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to only include fully used coupons.
     */
    public function scopeFullyUsed($query)
    {
        return $query->whereNotNull('max_uses')
            ->whereColumn('used_count', '>=', 'max_uses');
    }

    /**
     * Scope a query to filter by discount type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('discount_type', $type);
    }

    /**
     * Scope for promotional coupons only (bukan reward member).
     */
    public function scopePromotional($query)
    {
        return $query->where('is_member_reward', false);
    }

    /**
     * Scope for member reward coupons only.
     */
    public function scopeMemberReward($query)
    {
        return $query->where('is_member_reward', true);
    }

    /**
     * Scope for coupons available at specific outlet.
     */
    public function scopeForOutlet($query, $outletId)
    {
        return $query->where(function ($q) use ($outletId) {
            $q->whereNull('outlet_id') // Available at all outlets
              ->orWhere('outlet_id', $outletId); // Specific to this outlet
        });
    }

    /**
     * Status Check Methods
     */

    /**
     * Check if the coupon is currently active.
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Check if the coupon is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && now()->gt($this->expires_at);
    }

    /**
     * Check if the coupon is scheduled (not started yet).
     */
    public function isScheduled(): bool
    {
        return $this->is_active 
            && $this->starts_at 
            && now()->lt($this->starts_at);
    }

    /**
     * Check if the coupon can be used.
     */
    public function canBeUsed(): bool
    {
        return $this->isActive() && !$this->isExpired();
    }

    /**
     * Check if the coupon is fully used.
     */
    public function isFullyUsed(): bool
    {
        return $this->max_uses && $this->used_count >= $this->max_uses;
    }

    /**
     * Check if this is a member reward coupon.
     */
    public function isMemberReward(): bool
    {
        return $this->is_member_reward === true;
    }

    /**
     * Validation Methods
     */

    /**
     * Check if customer can use this coupon.
     */
    public function canBeUsedByCustomer(int $customerId, float $orderAmount, int $outletId = null): array
    {
        // Check if coupon is active
        if (!$this->canBeUsed()) {
            return [
                'valid' => false,
                'message' => 'Kupon ini tidak aktif atau sudah kadaluarsa.'
            ];
        }

        // Check if customer is member for member reward coupons
        if ($this->is_member_reward) {
            $customer = \App\Models\Customer::find($customerId);
            if (!$customer || !$customer->isMember()) {
                return [
                    'valid' => false,
                    'message' => 'Kupon ini hanya untuk member.'
                ];
            }
        }

        // Check outlet restriction
        if ($this->outlet_id && $outletId && $this->outlet_id != $outletId) {
            return [
                'valid' => false,
                'message' => 'Kupon ini tidak berlaku untuk outlet ini.'
            ];
        }

        // Check minimum order amount
        if ($this->min_order && $orderAmount < $this->min_order) {
            return [
                'valid' => false,
                'message' => 'Minimum order adalah Rp ' . number_format($this->min_order, 0, ',', '.')
            ];
        }

        // Check per-user usage limit
        if ($this->max_uses_per_user) {
            $userUsageCount = $this->orders()
                ->where('customer_id', $customerId)
                ->count();

            if ($userUsageCount >= $this->max_uses_per_user) {
                return [
                    'valid' => false,
                    'message' => 'Anda telah mencapai batas penggunaan kupon ini.'
                ];
            }
        }

        // Check first order only restriction
        if ($this->first_order_only) {
            $orderCount = \App\Models\Order::where('customer_id', $customerId)
                ->where('status', '!=', 'cancelled')
                ->count();
            
            if ($orderCount > 0) {
                return [
                    'valid' => false,
                    'message' => 'Kupon ini hanya untuk order pertama.'
                ];
            }
        }

        return [
            'valid' => true,
            'message' => 'Kupon valid!'
        ];
    }

    /**
     * Calculate discount amount for given order total.
     */
    public function calculateDiscount(float $orderAmount): float
    {
        if ($this->discount_type === 'percentage') {
            $discount = $orderAmount * ($this->discount_value / 100);
            
            // Apply max discount cap if set
            if ($this->max_discount && $discount > $this->max_discount) {
                $discount = $this->max_discount;
            }
            
            return round($discount, 2);
        }

        if ($this->discount_type === 'fixed') {
            // Fixed discount cannot exceed order amount
            return round(min($this->discount_value, $orderAmount), 2);
        }

        if ($this->discount_type === 'free_shipping') {
            return 0; // Handled separately in order logic
        }

        return 0;
    }

    /**
     * Usage Tracking Methods
     */

    /**
     * Increment the usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    /**
     * Decrement the usage count (for refunds/cancellations).
     */
    public function decrementUsage(): void
    {
        if ($this->used_count > 0) {
            $this->decrement('used_count');
        }
    }

    /**
     * Accessors
     */

    /**
     * Get the discount display text.
     */
    public function getDiscountDisplayAttribute(): string
    {
        return match($this->discount_type) {
            'percentage' => $this->discount_value . '%',
            'fixed' => 'Rp ' . number_format($this->discount_value, 0, ',', '.'),
            'free_shipping' => 'Gratis Ongkir',
            default => 'N/A',
        };
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->isExpired()) {
            return 'Kadaluarsa';
        }

        if ($this->isScheduled()) {
            return 'Terjadwal';
        }

        if ($this->isFullyUsed()) {
            return 'Habis Digunakan';
        }

        if ($this->isActive()) {
            return 'Aktif';
        }

        return 'Tidak Aktif';
    }

    /**
     * Get the status color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status_label) {
            'Aktif' => 'success',
            'Terjadwal' => 'warning',
            'Kadaluarsa' => 'danger',
            'Habis Digunakan' => 'secondary',
            'Tidak Aktif' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get the coupon type label.
     */
    public function getTypeLabeAttribute(): string
    {
        return $this->is_member_reward ? 'Reward Member' : 'Promosi';
    }

    /**
     * Get the coupon type color.
     */
    public function getTypeColorAttribute(): string
    {
        return $this->is_member_reward ? 'primary' : 'info';
    }

    /**
     * Get remaining uses.
     */
    public function getRemainingUsesAttribute(): ?int
    {
        if (!$this->max_uses) {
            return null; // Unlimited
        }

        return max(0, $this->max_uses - $this->used_count);
    }

    /**
     * Get usage percentage.
     */
    public function getUsagePercentageAttribute(): ?float
    {
        if (!$this->max_uses) {
            return null;
        }

        return ($this->used_count / $this->max_uses) * 100;
    }

    /**
     * Check if coupon expires soon (within 7 days).
     */
    public function expiresSoon(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isFuture() 
            && $this->expires_at->diffInDays(now()) <= 7;
    }

    /**
     * Get formatted expiry date.
     */
    public function getFormattedExpiryAttribute(): string
    {
        if (!$this->expires_at) {
            return 'Tidak ada batas waktu';
        }

        return $this->expires_at->format('d M Y, H:i');
    }

    /**
     * Get human-readable expiry.
     */
    public function getExpiryHumanAttribute(): ?string
    {
        return $this->expires_at ? $this->expires_at->diffForHumans() : null;
    }

    /**
     * Get formatted minimum order.
     */
    public function getFormattedMinOrderAttribute(): string
    {
        if (!$this->min_order) {
            return 'Tidak ada minimum';
        }

        return 'Rp ' . number_format($this->min_order, 0, ',', '.');
    }

    /**
     * Get formatted max discount.
     */
    public function getFormattedMaxDiscountAttribute(): string
    {
        if (!$this->max_discount) {
            return 'Tidak ada batas';
        }

        return 'Rp ' . number_format($this->max_discount, 0, ',', '.');
    }

    /**
     * Static Helper Methods
     */

    /**
     * Find coupon by code.
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', strtoupper($code))->first();
    }

    /**
     * Validate and get coupon by code.
     */
    public static function validateCode(string $code, int $customerId, float $orderAmount, int $outletId = null): array
    {
        $coupon = static::findByCode($code);

        if (!$coupon) {
            return [
                'valid' => false,
                'message' => 'Kode kupon tidak ditemukan.',
                'coupon' => null,
            ];
        }

        $validation = $coupon->canBeUsedByCustomer($customerId, $orderAmount, $outletId);

        return [
            'valid' => $validation['valid'],
            'message' => $validation['message'],
            'coupon' => $validation['valid'] ? $coupon : null,
            'discount' => $validation['valid'] ? $coupon->calculateDiscount($orderAmount) : 0,
        ];
    }

    /**
     * Get all active promotional coupons.
     */
    public static function getActivePromotions(int $outletId = null)
    {
        return static::active()
            ->promotional()
            ->public()
            ->when($outletId, fn($q) => $q->forOutlet($outletId))
            ->orderBy('discount_value', 'desc')
            ->get();
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate coupon code if not provided
        static::creating(function ($coupon) {
            if (!$coupon->code) {
                $coupon->code = static::generateUniqueCode();
            } else {
                // Ensure code is uppercase
                $coupon->code = strtoupper($coupon->code);
            }
        });

        // Ensure code is uppercase on update
        static::updating(function ($coupon) {
            if ($coupon->isDirty('code')) {
                $coupon->code = strtoupper($coupon->code);
            }
        });
    }

    /**
     * Generate unique coupon code.
     */
    private static function generateUniqueCode(int $length = 8): string
    {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}