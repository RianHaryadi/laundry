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
        'customer_type',      
        'guest_name',         
        'guest_phone',        
        'guest_address',      
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
        'coupon_id',
        'coupon_code',
        'coupon_earned', // TAMBAHAN: Track apakah sudah dapat kupon
        'reward_points', // TAMBAHAN: Poin reward untuk sistem kupon
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
        'coupon_earned' => 'boolean', // TAMBAHAN
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

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

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
            if ($coupon) {
                $discountAmount = $coupon->calculateDiscount($subtotal);
                $discountType = 'coupon';
            }
        }
        // B. Jika tidak ada kupon, cek Membership
        elseif ($this->customer && $this->customer->isMember()) {
            // Membership tidak memberikan diskon otomatis lagi
            // Member hanya mendapat kupon setelah selesai transaksi
            $discountAmount = 0;
            $discountType = null;
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

    /**
     * ============================================
     * KUPON REWARD SYSTEM - TAMBAHAN BARU
     * ============================================
     */

    /**
     * Mark order as completed and give coupon to member
     * Dipanggil ketika status order diubah menjadi 'completed'
     */
    public function markAsCompleted(): bool
    {
        // Update status ke completed
        $this->update(['status' => 'completed']);
        
        // Berikan kupon jika customer adalah member dan belum dapat kupon
        if ($this->customer && $this->customer->isMember() && !$this->coupon_earned) {
            $this->giveRewardCoupon();
        }
        
        return true;
    }

    /**
     * Give reward coupon to member customer
     * Hanya member yang dapat kupon reward
     */
    public function giveRewardCoupon(): bool
    {
        // Cek apakah customer adalah member
        if (!$this->customer || !$this->customer->isMember()) {
            return false;
        }

        // Cek apakah sudah pernah dapat kupon dari order ini
        if ($this->coupon_earned) {
            return false;
        }

        // Tambahkan kupon ke customer
        $success = $this->customer->addCoupon();

        if ($success) {
            // Tandai bahwa order ini sudah memberikan kupon
            $this->update(['coupon_earned' => true]);
            
            // OPTIONAL: Log atau tracking
            \Log::info("Coupon rewarded to customer #{$this->customer_id} from order #{$this->id}");
        }

        return $success;
    }

    /**
     * Check if this order has given reward coupon
     */
    public function hasGivenRewardCoupon(): bool
    {
        return $this->coupon_earned === true;
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

    // 1. Auto Calculate at Create
    static::creating(function ($order) {
        if (!$order->base_price && $order->service_id && $order->total_weight) {
            $order->calculatePrices();
        }
    });

    // 2. Auto create payment & tracking
    static::created(function ($order) {
        // Create Payment Record
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

        // Increment coupon usage
        if ($order->coupon_id) {
            $coupon = \App\Models\Coupon::find($order->coupon_id);
            if ($coupon) $coupon->incrementUsage();
        }

        // ✅ AUTO-CREATE TRACKING untuk pickup/delivery
        if (in_array($order->delivery_method, ['pickup', 'delivery', 'pickup_delivery'])) {
            $order->createInitialTracking();
        }
    });

    // 3. Recalculate price on change
    static::updating(function ($order) {
        if ($order->isDirty(['service_id', 'total_weight', 'service_speed', 'delivery_method', 'customer_id', 'coupon_id'])) {
            $order->calculatePrices();
        }
    });

    // 4. Handle tracking creation & reward after update
    static::updated(function ($order) {
        // Sync payment status
        $order->syncPaymentStatus();

        // ✅ CREATE TRACKING jika delivery_method berubah ke pickup/delivery
        if ($order->wasChanged('delivery_method')) {
            $needsTracking = in_array($order->delivery_method, ['pickup', 'delivery', 'pickup_delivery']);
            $hadTracking = in_array($order->getOriginal('delivery_method'), ['pickup', 'delivery', 'pickup_delivery']);
            
            // Buat tracking baru jika berubah dari walk_in ke pickup/delivery
            if ($needsTracking && !$hadTracking) {
                $order->createInitialTracking();
            }
            
            // Hapus tracking jika berubah ke walk_in (opsional)
            if (!$needsTracking && $hadTracking) {
                // Uncomment jika ingin auto-delete tracking saat ganti ke walk_in
                // $order->trackings()->delete();
                \Log::info("Order #{$order->id}: Changed to walk_in, tracking preserved");
            }
        }

        // ✅ UPDATE TRACKING STATUS jika order status berubah
        if ($order->wasChanged('status') && in_array($order->delivery_method, ['pickup', 'delivery', 'pickup_delivery'])) {
            $trackingStatus = match($order->status) {
                'confirmed' => 'pending',
                'processing' => 'pending',
                'ready' => 'picked_up',
                'picked_up' => 'picked_up',
                'in_delivery' => 'in_transit',
                'completed' => 'delivered',
                'cancelled' => 'failed',
                default => null,
            };

            if ($trackingStatus) {
                $order->updateTrackingStatus($trackingStatus);
            }
        }

        // ✅ Reward kupon ketika payment_status berubah jadi PAID
        if ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
            $customer = $order->customer;
            if (!$customer) return;

            // Cek apakah order ini sudah pernah kasih kupon
            if ($order->coupon_earned) return;

            // Tambah 1 poin
            $customer->reward_points += 1;

            // Jika sudah 6 → buat kupon gratis
            if ($customer->reward_points >= 6) {
                \App\Models\Coupon::create([
                    'code' => 'FREE-' . strtoupper(uniqid()),
                    'customer_id' => $customer->id,
                    'discount_type' => 'percentage',
                    'discount_value' => 100,
                    'min_spend' => 0,
                    'usage_limit' => 1,
                    'expires_at' => now()->addMonths(3),
                ]);

                $customer->reward_points = 0;

                // Send notification
                \Filament\Notifications\Notification::make()
                    ->success()
                    ->title('🎉 Free Coupon Earned!')
                    ->body("Customer {$customer->name} received a FREE service coupon!")
                    ->send();
            }

            $customer->save();

            // Tandai sudah memberikan reward
            $order->updateQuietly(['coupon_earned' => true]);
        }
    });

    // 5. Clean up relations on delete (opsional)
    static::deleting(function ($order) {
        // Delete related trackings
        $order->trackings()->delete();
        
        \Log::info("Order #{$order->id}: Deleted with all tracking records");
    });
}
/**
 * Create initial tracking record for pickup/delivery orders
 */
public function createInitialTracking(): void
{
    // Pastikan ada courier_id
    if (!$this->courier_id) {
        \Log::warning("Order #{$this->id}: Cannot create tracking without courier_id");
        return;
    }

    // Cek apakah sudah ada tracking untuk order ini
    if ($this->trackings()->exists()) {
        \Log::info("Order #{$this->id}: Tracking already exists, skipping creation");
        return;
    }

    // Tentukan initial status berdasarkan delivery method
    $initialStatus = 'pending';
    $notes = match($this->delivery_method) {
        'pickup' => 'Waiting for pickup from customer',
        'delivery' => 'Ready for delivery to customer',
        'pickup_delivery' => 'Waiting for pickup, then delivery',
        default => 'Order tracking initialized',
    };

    // Buat tracking record
    try {
        $tracking = $this->trackings()->create([
            'courier_id' => $this->courier_id,
            'status' => $initialStatus,
            'latitude' => null,
            'longitude' => null,
            'notes' => $notes,
        ]);

        \Log::info("Order #{$this->id}: Tracking #{$tracking->id} created successfully with status '{$initialStatus}'");

        // Send notification (opsional)
        \Filament\Notifications\Notification::make()
            ->success()
            ->title('📍 Tracking Created')
            ->body("Tracking record created for Order #{$this->formatted_id}")
            ->send();

    } catch (\Exception $e) {
        \Log::error("Order #{$this->id}: Failed to create tracking - {$e->getMessage()}");
    }
}

/**
 * Update tracking status based on order status
 */
public function updateTrackingStatus(string $trackingStatus, ?string $notes = null): void
{
    $latestTracking = $this->trackings()->latest()->first();

    if (!$latestTracking) {
        \Log::warning("Order #{$this->id}: No tracking found to update");
        return;
    }

    try {
        $latestTracking->update([
            'status' => $trackingStatus,
            'notes' => $notes ?? "Order status changed to {$this->status}, tracking updated to {$trackingStatus}",
            'updated_at' => now(),
        ]);

        \Log::info("Order #{$this->id}: Tracking #{$latestTracking->id} updated to '{$trackingStatus}'");

    } catch (\Exception $e) {
        \Log::error("Order #{$this->id}: Failed to update tracking - {$e->getMessage()}");
    }
}

}