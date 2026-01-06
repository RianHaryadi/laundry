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
        'is_free_service' // TAMBAHAN: Tandai jika order adalah layanan gratis
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
        'is_free_service' => 'boolean', 
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

protected static function boot()
{
    parent::boot();

    // 1. SEBELUM CREATE: Set default values, JANGAN calculate dulu
    static::creating(function ($order) {
        // Set default values untuk mencegah null
        $order->base_price = $order->base_price ?? 0;
        $order->total_price = $order->total_price ?? 0;
        $order->final_price = $order->final_price ?? 0;
        $order->discount_amount = $order->discount_amount ?? 0;
        $order->total_weight = $order->total_weight ?? 0;
    });

    // 2. SETELAH CREATE: Logika Reset Kupon & Record Awal
    static::created(function ($order) {
        // Jika pakai Free Service, jatah 6 kupon langsung hangus
        if ($order->is_free_service && $order->customer_id && $order->customer_type === 'member') {
            $customer = $order->customer;
            if ($customer && $customer->available_coupons >= 6) {
                // Kurangi 6 dari total yang ada
                $customer->decrement('available_coupons', 6);
                \Log::info("Free Service digunakan. Kupon Customer #{$customer->id} dikurangi 6. Sisa: {$customer->available_coupons}");
            }
            $order->updateQuietly(['coupon_earned' => true]);
        }

        // Buat record pembayaran awal (dengan amount 0 dulu)
        $order->createInitialPaymentRecord();

        // Buat tracking jika perlu
        if (in_array($order->delivery_method, ['pickup', 'delivery', 'pickup_delivery'])) {
            $order->createInitialTracking();
        }
    });

    // 3. SAAT UPDATE: Hitung ulang jika data krusial berubah
    static::updating(function ($order) {
        $fieldsToCheck = [
            'service_id', 
            'total_weight', 
            'service_speed', 
            'delivery_method', 
            'coupon_id', 
            'is_free_service'
        ];
        
        if ($order->isDirty($fieldsToCheck)) {
            $order->calculatePrices();
        }
    });

    // 4. SETELAH UPDATE: Sync Status & Cek Reward
    static::updated(function ($order) {
        $order->syncPaymentStatus();

        // Sync Tracking Status otomatis
        if ($order->wasChanged('status')) {
            $order->syncTrackingWithOrderStatus();
        }

        // Cek apakah dapet reward (Setiap 6 kali cuci berbayar)
        if ($order->wasChanged('status') && $order->status === 'completed') {
            $order->handleRewardSystem();
        }
    });
}

/**
 * Create initial tracking record for orders with pickup/delivery
 */
public function createInitialTracking()
{
    // Only create tracking for orders that need pickup/delivery
    if (!in_array($this->delivery_method, ['pickup', 'delivery', 'pickup_delivery'])) {
        return null;
    }

    try {
        // Determine initial tracking status
        $initialStatus = 'pending_pickup';

        // Create the initial tracking record
        return $this->trackings()->create([
            'status' => $initialStatus,
            'courier_id' => $this->courier_id, // Bisa null
            'location' => $this->outlet->address ?? 'Outlet',
            'notes' => 'Order created and waiting for processing',
            'updated_by' => auth()->id(),
        ]);
    } catch (\Exception $e) {
        \Log::error("Failed to create tracking for order #{$this->id}: " . $e->getMessage());
        return null;
    }
}

/**
 * Calculate prices dari order items atau service
 */
public function calculatePrices(): void
{
    // 1. Hitung Base Price dari Order Items atau Service
    $basePrice = 0;
    $totalWeight = 0;

    // Coba ambil dari order items yang sudah ada
    $orderItems = $this->orderItems()->get();

    if ($orderItems->isNotEmpty()) {
        // Hitung dari order items
        foreach ($orderItems as $item) {
            $basePrice += $item->subtotal ?? 0;
            $totalWeight += $item->weight ?? 0;
        }
    } else {
        // Fallback: hitung dari service dan total_weight
        $service = $this->service;
        if ($service && $this->total_weight > 0) {
            $pricePerKg = $service->price_per_kg ?? $service->base_price;
            $basePrice = $this->total_weight * $pricePerKg;
            $totalWeight = $this->total_weight;
        }
    }

    $this->base_price = round($basePrice, 2);
    $this->total_weight = round($totalWeight, 2);

    // 2. Hitung Total Price dengan Multiplier (Speed & Delivery)
    $subtotal = $this->base_price * $this->getSpeedMultiplier() * $this->getDeliveryMultiplier();
    $this->total_price = round($subtotal, 2);

    // 3. Logika Diskon & Free Service
    if ($this->is_free_service) {
        $this->discount_amount = $this->total_price;
        $this->final_price = 0;
        $this->discount_type = 'free_service';
    } else {
        $discountAmount = 0;
        if ($this->coupon_id && ($coupon = Coupon::find($this->coupon_id))) {
            $discountAmount = $coupon->calculateDiscount($this->total_price);
        }
        $this->discount_amount = round($discountAmount, 2);
        $this->final_price = round($this->total_price - $discountAmount, 2);
        $this->discount_type = $discountAmount > 0 ? 'coupon' : null;
    }
}

/**
 * Recalculate prices setelah order items berubah
 * Method ini dipanggil dari CreateOrder dan EditOrder
 */
public function recalculatePricesFromItems(): void
{
    // Force reload order items dari database
    $this->load('orderItems');
    
    // Calculate prices
    $this->calculatePrices();
    
    // Save ke database tanpa trigger event lagi
    $this->saveQuietly();
    
    // Update payment record jika ada dan masih pending
    $latestPayment = $this->payments()->latest()->first();
    if ($latestPayment && $latestPayment->status === 'pending') {
        $latestPayment->updateQuietly([
            'amount' => $this->final_price
        ]);
    }
    
    \Log::info("Order #{$this->id} recalculated from items", [
        'items_count' => $this->orderItems->count(),
        'base_price' => $this->base_price,
        'total_price' => $this->total_price,
        'final_price' => $this->final_price,
    ]);
}

/**
 * Menangani sistem reward (6x cuci gratis 1)
 */
protected function handleRewardSystem(): void
{
    $customer = $this->customer;
    
    // Validasi: harus member, belum dapat reward, dan bukan free service
    if (!$customer || 
        $this->customer_type !== 'member' || 
        $this->coupon_earned || 
        $this->is_free_service) {
        return;
    }
    
    // Hitung hanya order BERBAYAR yang sudah COMPLETED
    $paidOrdersCount = $customer->orders()
        ->where('status', 'completed')
        ->where('is_free_service', false)
        ->count();

    // Tandai order ini sudah dapat reward (mencegah double reward)
    $this->updateQuietly(['coupon_earned' => true]);

    // Cek apakah sudah mencapai kelipatan 6
    if ($paidOrdersCount > 0 && $paidOrdersCount % 6 === 0) {
        try {
            // Tambahkan 6 kupon ke saldo yang sudah ada (Stacking)
            $customer->increment('available_coupons', 6);

            \Log::info("Reward earned for customer #{$customer->id}", [
                'order_id' => $this->id,
                'paid_orders_count' => $paidOrdersCount,
                'new_coupon_balance' => $customer->available_coupons,
            ]);

            // Kirim notifikasi jika ada Filament
            if (class_exists('\Filament\Notifications\Notification')) {
                \Filament\Notifications\Notification::make()
                    ->success()
                    ->title('🎉 Reward 6x Cuci Tercapai!')
                    ->body("Pelanggan {$customer->name} mendapat tambahan 6 kupon! Total: {$customer->available_coupons}")
                    ->send();
            }
        } catch (\Exception $e) {
            \Log::error("Failed to give reward to customer #{$customer->id}: " . $e->getMessage());
        }
    }
}

// ==========================================
// HELPERS & SYNC
// ==========================================

public function syncPaymentStatus(): void
{
    $payment = $this->payments()->latest()->first();
    
    // Hanya sync jika ada payment dan payment_status berubah
    if (!$payment || !$this->wasChanged('payment_status')) {
        return;
    }

    $newStatus = match ($this->payment_status) {
        'paid' => 'success',
        'failed' => 'failed',
        'refunded' => 'refunded',
        default => 'pending',
    };

    $payment->updateQuietly([
        'status' => $newStatus,
        'paid_at' => ($newStatus === 'success') ? now() : null
    ]);
}

public function createInitialPaymentRecord()
{
    return $this->payments()->create([
        'amount' => $this->final_price ?? 0,
        'gateway' => $this->payment_gateway ?? 'cash',
        'status' => $this->payment_status === 'paid' ? 'success' : 'pending',
        'transaction_id' => 'TRX-' . date('Ymd') . '-' . strtoupper(uniqid()),
    ]);
}

public function syncTrackingWithOrderStatus(): void
{
    $trackingStatus = match($this->status) {
        'ready', 'picked_up' => 'picked_up',
        'in_delivery' => 'in_transit',
        'completed' => 'delivered',
        'cancelled' => 'failed',
        default => null,
    };

    if (!$trackingStatus) {
        return;
    }

    $latestTracking = $this->trackings()->latest()->first();
    
    if ($latestTracking) {
        $latestTracking->updateQuietly([
            'status' => $trackingStatus,
            'notes' => "Status berubah otomatis menjadi {$trackingStatus}",
            'updated_at' => now(),
        ]);
        
        \Log::info("Tracking synced for order #{$this->id}", [
            'order_status' => $this->status,
            'tracking_status' => $trackingStatus,
        ]);
    }
}
}