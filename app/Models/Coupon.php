<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
    ];

    

    /**
     * Get the outlets that can use this coupon.
     */
    public function outlets(): BelongsToMany
    {
        return $this->belongsToMany(Outlet::class, 'coupon_outlet');
    }

    /**
     * Get the orders that used this coupon.
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'coupon_order');
    }

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
     * Check if customer can use this coupon.
     */
    public function canBeUsedByCustomer(int $customerId, float $orderAmount): array
    {
        // Check if coupon is active
        if (!$this->canBeUsed()) {
            return [
                'valid' => false,
                'message' => 'This coupon is not active or has expired.'
            ];
        }

        // Check minimum order amount
        if ($this->min_order && $orderAmount < $this->min_order) {
            return [
                'valid' => false,
                'message' => 'Minimum order amount is Rp ' . number_format($this->min_order, 0, ',', '.')
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
                    'message' => 'You have reached the usage limit for this coupon.'
                ];
            }
        }

        return [
            'valid' => true,
            'message' => 'Coupon is valid!'
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
            
            return $discount;
        }

        if ($this->discount_type === 'fixed') {
            // Fixed discount cannot exceed order amount
            return min($this->discount_value, $orderAmount);
        }

        if ($this->discount_type === 'free_shipping') {
            return 0; // Handled separately in order logic
        }

        return 0;
    }

    /**
     * Increment the usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    /**
     * Decrement the usage count (for refunds).
     */
    public function decrementUsage(): void
    {
        if ($this->used_count > 0) {
            $this->decrement('used_count');
        }
    }

    /**
     * Get the discount display text.
     */
    public function getDiscountDisplayAttribute(): string
    {
        return match($this->discount_type) {
            'percentage' => $this->discount_value . '%',
            'fixed' => 'Rp ' . number_format($this->discount_value, 0, ',', '.'),
            'free_shipping' => 'Free Shipping',
            default => 'N/A',
        };
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->isExpired()) {
            return 'Expired';
        }

        if ($this->isScheduled()) {
            return 'Scheduled';
        }

        if ($this->isActive()) {
            return 'Active';
        }

        return 'Inactive';
    }

    /**
     * Get the status color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status_label) {
            'Active' => 'success',
            'Scheduled' => 'warning',
            'Expired' => 'danger',
            'Inactive' => 'secondary',
            default => 'secondary',
        };
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
            return 'No expiration';
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
}