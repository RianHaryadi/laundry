<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'outlet_id',
        'service_id',
        'courier_id',
        'service_speed',
        'delivery_method',
        'status',
        'payment_status',
        'total_weight',
        'base_price',
        'total_price',
        'discount_amount',
        'final_price',
        'discount_type',
        'pickup_delivery_fee',
        'pickup_time',
        'delivery_time',
        'payment_gateway',
        'notes',
        // --- Added for Coupon Feature ---
        'coupon_id',
        'coupon_code',
    ];

    protected $casts = [
        'total_weight' => 'decimal:2',
        'base_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'pickup_delivery_fee' => 'decimal:2',
        'pickup_time' => 'datetime',
        'delivery_time' => 'datetime',
    ];

    /**
     * Relationships
     */
    
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    /**
     * Relationship to Coupon (NEW)
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Keep items() for backward compatibility
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function latestSuccessfulPayment(): HasOne
    {
        return $this->hasOne(Payment::class)
                    ->where('status', 'success')
                    ->latestOfMany();
    }

    public function trackings(): HasMany
    {
        return $this->hasMany(Tracking::class);
    }

    public function latestTracking(): HasOne
    {
        return $this->hasOne(Tracking::class)->latestOfMany();
    }

    /**
     * Accessors & Mutators
     */

    public function getFormattedIdAttribute(): string
    {
        return '#' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    public function getFormattedFinalPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->final_price, 0, ',', '.');
    }

    public function getFormattedDiscountAttribute(): string
    {
        return 'Rp ' . number_format($this->discount_amount, 0, ',', '.');
    }

    public function getFormattedPickupFeeAttribute(): string
    {
        return 'Rp ' . number_format($this->pickup_delivery_fee, 0, ',', '.');
    }

    /**
     * Business Logic Methods
     */

    public function getSavingsAmount(): float
    {
        $savingsFromDiscount = $this->discount_amount;
        
        $savingsFromDelivery = 0;
        if ($this->customer && method_exists($this->customer, 'hasFreePickupDelivery') && $this->customer->hasFreePickupDelivery()) {
            if (in_array($this->delivery_method, ['pickup', 'delivery', 'pickup_delivery'])) {
                $savingsFromDelivery = 10000;
            }
        }

        return $savingsFromDiscount + $savingsFromDelivery;
    }

    public function hasMembershipBenefits(): bool
    {
        // Modified to check if discount type is specifically membership
        return ($this->discount_amount > 0 && $this->discount_type === 'membership') || 
               ($this->pickup_delivery_fee == 0 && in_array($this->delivery_method, ['pickup', 'delivery', 'pickup_delivery']));
    }

    public function getDiscountPercentage(): float
    {
        if ($this->total_price <= 0) {
            return 0;
        }
        return ($this->discount_amount / $this->total_price) * 100;
    }

    /**
     * Calculate and update order prices based on weight, service, coupon, and customer
     */
    public function calculatePrices(): void
    {
        if (!$this->service_id || !$this->total_weight) {
            return;
        }

        $service = $this->service;
        if (!$service) {
            return;
        }

        // 1. Calculate base price
        $pricePerKg = $service->price_per_kg ?? $service->base_price;
        $basePrice = $this->total_weight * $pricePerKg;
        $this->base_price = round($basePrice, 2);

        // 2. Apply multipliers
        $speedMultiplier = $this->getSpeedMultiplier();
        $deliveryMultiplier = $this->getDeliveryMultiplier();
        $subtotal = $basePrice * $speedMultiplier * $deliveryMultiplier;

        // 3. Calculate pickup/delivery fee
        $pickupDeliveryFee = 0; 

        // 4. LOGIC DISKON (Updated: Coupon vs Membership)
        $discountAmount = 0;
        $discountType = null;

        // A. Cek Kupon Dulu (Prioritas Utama)
        if ($this->coupon_id) {
            $coupon = Coupon::find($this->coupon_id);
            // Gunakan subtotal (gross amount) untuk menghitung diskon
            if ($coupon) {
                // Pastikan method calculateDiscount ada di model Coupon
                $discountAmount = $coupon->calculateDiscount($subtotal);
                $discountType = 'coupon';
            }
        }
        // B. Jika tidak ada kupon, cek Membership
        elseif ($this->customer && method_exists($this->customer, 'hasActiveMembership') && $this->customer->hasActiveMembership()) {
            $discountPercentage = $this->customer->getMembershipDiscount();
            $discountAmount = ($subtotal * $discountPercentage) / 100;
            $discountType = 'membership';
            
            // Free pickup/delivery for premium members
            if (method_exists($this->customer, 'hasFreePickupDelivery') && $this->customer->hasFreePickupDelivery()) {
                $pickupDeliveryFee = 0;
            }
        }

        // 5. Update prices
        $this->total_price = round($subtotal, 2);
        $this->discount_amount = round($discountAmount, 2);
        $this->pickup_delivery_fee = round($pickupDeliveryFee, 2);
        $this->final_price = round($subtotal - $discountAmount + $pickupDeliveryFee, 2);
        $this->discount_type = $discountAmount > 0 ? $discountType : null;
    }

    public function getSpeedMultiplier(): float
    {
        return match($this->service_speed) {
            'express' => 1.5,
            'same_day' => 2.0,
            default => 1.0,
        };
    }

    public function getDeliveryMultiplier(): float
    {
        return match($this->delivery_method) {
            'pickup' => 1.2,
            'delivery' => 1.2,
            'pickup_delivery' => 1.4,
            default => 1.0,
        };
    }

    public function getSpeedMultiplierPercentage(): int
    {
        $multiplier = $this->getSpeedMultiplier();
        return (int) (($multiplier - 1) * 100);
    }

    public function getDeliveryMultiplierPercentage(): int
    {
        $multiplier = $this->getDeliveryMultiplier();
        return (int) (($multiplier - 1) * 100);
    }

    // --- Status Helper Methods ---

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) && !$this->isPaid();
    }

    public function needsPickup(): bool
    {
        return in_array($this->delivery_method, ['pickup', 'pickup_delivery']);
    }

    public function needsDelivery(): bool
    {
        return in_array($this->delivery_method, ['delivery', 'pickup_delivery']);
    }

    public function isOverdue(): bool
    {
        if (!$this->delivery_time) {
            return false;
        }
        return $this->delivery_time->isPast() && !in_array($this->status, ['completed', 'cancelled']);
    }

    public function getTotalPaidAmount(): float
    {
        return $this->payments()->where('status', 'success')->sum('amount');
    }

    public function getRemainingAmount(): float
    {
        $totalPaid = $this->getTotalPaidAmount();
        $remaining = $this->final_price - $totalPaid;
        return max(0, $remaining);
    }

    public function hasSuccessfulPayment(): bool
    {
        return $this->payments()->where('status', 'success')->exists();
    }

    public function isFullyPaid(): bool
    {
        return $this->getTotalPaidAmount() >= $this->final_price;
    }

    // --- Badge Colors ---

    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'secondary',
            'confirmed' => 'info',
            'processing' => 'warning',
            'ready' => 'primary',
            'picked_up' => 'info',
            'in_delivery' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    public function getPaymentStatusColor(): string
    {
        return match($this->payment_status) {
            'pending' => 'secondary',
            'paid' => 'success',
            'partial' => 'warning',
            'failed' => 'danger',
            'refunded' => 'info',
            default => 'gray',
        };
    }

    public function getSpeedColor(): string
    {
        return match($this->service_speed) {
            'regular' => 'secondary',
            'express' => 'warning',
            'same_day' => 'danger',
            default => 'gray',
        };
    }

    public function getDeliveryMethodColor(): string
    {
        return match($this->delivery_method) {
            'walk_in' => 'secondary',
            'pickup' => 'info',
            'delivery' => 'success',
            'pickup_delivery' => 'primary',
            default => 'gray',
        };
    }

    // --- Scopes ---

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    // ... (Keep existing scopes from your previous code: pending, confirmed, processing, etc.)
    public function scopePending($query) { return $query->where('status', 'pending'); }
    public function scopeConfirmed($query) { return $query->where('status', 'confirmed'); }
    public function scopeProcessing($query) { return $query->where('status', 'processing'); }
    public function scopeCompleted($query) { return $query->where('status', 'completed'); }
    public function scopeCancelled($query) { return $query->where('status', 'cancelled'); }
    public function scopePaid($query) { return $query->where('payment_status', 'paid'); }
    public function scopeUnpaid($query) { return $query->where('payment_status', '!=', 'paid'); }

    public function scopeNeedsCourier($query)
    {
        return $query->whereIn('delivery_method', ['pickup', 'delivery', 'pickup_delivery'])
                     ->whereNull('courier_id');
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('delivery_time')
                     ->where('delivery_time', '<', now())
                     ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
    
    // --- Boot & Sync ---

    public function syncPaymentStatus(): void
    {
        $payment = $this->payments()->latest()->first();
        if (!$payment) return;

        // 1. Sync Amount if pending
        if ($this->wasChanged('final_price') && $payment->status === 'pending') {
            $payment->amount = $this->final_price ?? $this->total_price;
        }

        // 2. Sync Status
        if ($this->wasChanged('payment_status')) {
            $newStatus = match ($this->payment_status) {
                'paid' => 'success',
                'failed' => 'failed',
                'refunded' => 'refunded',
                default => 'pending',
            };

            $payment->status = $newStatus;
            if ($newStatus === 'success' && !$payment->paid_at) {
                $payment->paid_at = now();
            } else if ($newStatus !== 'success') {
                $payment->paid_at = null;
            }
        }

        if ($payment->isDirty()) {
            $payment->save();
        }
    }

    protected static function boot()
    {
        parent::boot();

        // 1. Auto-calculate prices (Triggered on create & update)
        static::creating(function ($order) {
            if (!$order->base_price && $order->service_id && $order->total_weight) {
                $order->calculatePrices();
            }
        });

        // 2. Auto-create Payment
        static::created(function ($order) {
            $gateway = $order->payment_gateway ?? 'cash';
            $status = $order->payment_status ?? 'pending';
            $paymentStatus = match ($status) {
                'paid' => 'success',
                'failed' => 'failed',
                'refunded' => 'refunded',
                default => 'pending',
            };

            $order->payments()->create([
                'amount' => $order->final_price ?? $order->total_price ?? 0,
                'gateway' => $gateway,
                'status' => $paymentStatus,
                'transaction_id' => 'TRX-' . date('Ymd') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'paid_at' => ($paymentStatus === 'success') ? now() : null,
                'notes' => 'Auto-generated from Order creation',
            ]);
            
            // OPTIONAL: Increment Coupon Usage on Create
            if ($order->coupon_id) {
                $coupon = Coupon::find($order->coupon_id);
                if ($coupon) $coupon->incrementUsage();
            }
        });

        // 3. Recalculate & Sync Payment
        static::updating(function ($order) {
            // Re-calculate if critical fields change OR if coupon changed
            if ($order->isDirty(['service_id', 'total_weight', 'service_speed', 'delivery_method', 'customer_id', 'coupon_id'])) {
                $order->calculatePrices();
            }
        });

        static::updated(function ($order) {
            $order->syncPaymentStatus();
        });
    }
}