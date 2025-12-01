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
        'base_price',  // Added for calculation
        'total_price',
        'discount_amount',
        'final_price',
        'discount_type',
        'pickup_delivery_fee',
        'pickup_time',
        'delivery_time',
        'payment_gateway',
        'notes',
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

    // Changed from items() to orderItems() for consistency
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Keep items() for backward compatibility
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // FIXED: Changed from HasOne to HasMany
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Keep singular payment() for backward compatibility (returns latest payment)
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    // Get the latest successful payment
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

    /**
     * Calculate total savings from membership
     * 
     * @return float
     */
    public function getSavingsAmount(): float
    {
        $savingsFromDiscount = $this->discount_amount;
        
        // Add savings from free pickup/delivery
        $savingsFromDelivery = 0;
        if ($this->customer && method_exists($this->customer, 'hasFreePickupDelivery') && $this->customer->hasFreePickupDelivery()) {
            if (in_array($this->delivery_method, ['pickup', 'delivery', 'pickup_delivery'])) {
                $savingsFromDelivery = 10000; // Standard pickup/delivery fee
            }
        }

        return $savingsFromDiscount + $savingsFromDelivery;
    }

    /**
     * Check if order has membership benefits applied
     * 
     * @return bool
     */
    public function hasMembershipBenefits(): bool
    {
        return $this->discount_amount > 0 || 
               ($this->pickup_delivery_fee == 0 && in_array($this->delivery_method, ['pickup', 'delivery', 'pickup_delivery']));
    }

    /**
     * Get membership discount percentage applied
     * 
     * @return float
     */
    public function getDiscountPercentage(): float
    {
        if ($this->total_price <= 0) {
            return 0;
        }

        return ($this->discount_amount / $this->total_price) * 100;
    }

    /**
     * Calculate and update order prices based on weight, service, and customer
     * 
     * @return void
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

        // Calculate base price
        $pricePerKg = $service->price_per_kg ?? $service->base_price;
        $basePrice = $this->total_weight * $pricePerKg;

        // Store base price
        $this->base_price = round($basePrice, 2);

        // Apply service speed multiplier
        $speedMultiplier = $this->getSpeedMultiplier();
        
        // Apply delivery method multiplier
        $deliveryMultiplier = $this->getDeliveryMultiplier();
        
        $subtotal = $basePrice * $speedMultiplier * $deliveryMultiplier;

        // Calculate pickup/delivery fee (optional - jika ingin biaya terpisah)
        $pickupDeliveryFee = 0;
        // Biaya sudah termasuk dalam multiplier, jadi set 0
        // Atau bisa tambahkan biaya flat jika diperlukan

        // Apply membership discount
        $discountAmount = 0;
        if ($this->customer && method_exists($this->customer, 'hasActiveMembership') && $this->customer->hasActiveMembership()) {
            $discountPercentage = $this->customer->getMembershipDiscount();
            $discountAmount = ($subtotal * $discountPercentage) / 100;
            
            // Free pickup/delivery for premium members (optional)
            if (method_exists($this->customer, 'hasFreePickupDelivery') && $this->customer->hasFreePickupDelivery()) {
                $pickupDeliveryFee = 0;
            }
        }

        // Update prices
        $this->total_price = round($subtotal, 2);
        $this->discount_amount = round($discountAmount, 2);
        $this->pickup_delivery_fee = round($pickupDeliveryFee, 2);
        $this->final_price = round($subtotal - $discountAmount + $pickupDeliveryFee, 2);
        $this->discount_type = $discountAmount > 0 ? 'membership' : null;
    }

    /**
     * Get service speed multiplier for pricing
     * 
     * @return float
     */
    public function getSpeedMultiplier(): float
    {
        return match($this->service_speed) {
            'express' => 1.5,      // +50%
            'same_day' => 2.0,     // +100%
            default => 1.0,        // Regular
        };
    }

    /**
     * Get delivery method multiplier for pricing
     * 
     * @return float
     */
    public function getDeliveryMultiplier(): float
    {
        return match($this->delivery_method) {
            'pickup' => 1.2,           // +20%
            'delivery' => 1.2,         // +20%
            'pickup_delivery' => 1.4,  // +40%
            default => 1.0,            // Walk-in
        };
    }

    /**
     * Get speed multiplier as percentage
     * 
     * @return int
     */
    public function getSpeedMultiplierPercentage(): int
    {
        $multiplier = $this->getSpeedMultiplier();
        return (int) (($multiplier - 1) * 100);
    }

    /**
     * Get delivery multiplier as percentage
     * 
     * @return int
     */
    public function getDeliveryMultiplierPercentage(): int
    {
        $multiplier = $this->getDeliveryMultiplier();
        return (int) (($multiplier - 1) * 100);
    }

    /**
     * Check if order is paid
     * 
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if order is completed
     * 
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if order is cancelled
     * 
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if order can be cancelled
     * 
     * @return bool
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) && 
               !$this->isPaid();
    }

    /**
     * Check if order needs pickup
     * 
     * @return bool
     */
    public function needsPickup(): bool
    {
        return in_array($this->delivery_method, ['pickup', 'pickup_delivery']);
    }

    /**
     * Check if order needs delivery
     * 
     * @return bool
     */
    public function needsDelivery(): bool
    {
        return in_array($this->delivery_method, ['delivery', 'pickup_delivery']);
    }

    /**
     * Check if order is overdue
     * 
     * @return bool
     */
    public function isOverdue(): bool
    {
        if (!$this->delivery_time) {
            return false;
        }

        return $this->delivery_time->isPast() && 
               !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Get total paid amount from all successful payments
     * 
     * @return float
     */
    public function getTotalPaidAmount(): float
    {
        return $this->payments()
                    ->where('status', 'success')
                    ->sum('amount');
    }

    /**
     * Get remaining payment amount
     * 
     * @return float
     */
    public function getRemainingAmount(): float
    {
        $totalPaid = $this->getTotalPaidAmount();
        $remaining = $this->final_price - $totalPaid;
        
        return max(0, $remaining);
    }

    /**
     * Check if order has any successful payment
     * 
     * @return bool
     */
    public function hasSuccessfulPayment(): bool
    {
        return $this->payments()->where('status', 'success')->exists();
    }

    /**
     * Check if order is fully paid
     * 
     * @return bool
     */
    public function isFullyPaid(): bool
    {
        return $this->getTotalPaidAmount() >= $this->final_price;
    }

    public function order_items()
{
    return $this->hasMany(OrderItem::class);
}

    /**
     * Get order status badge color
     * 
     * @return string
     */
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

    /**
     * Get payment status badge color
     * 
     * @return string
     */
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

    /**
     * Get service speed badge color
     * 
     * @return string
     */
    public function getSpeedColor(): string
    {
        return match($this->service_speed) {
            'regular' => 'secondary',
            'express' => 'warning',
            'same_day' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Get delivery method badge color
     * 
     * @return string
     */
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

    /**
     * Scopes
     */

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', '!=', 'paid');
    }

    public function scopeBySpeed($query, $speed)
    {
        return $query->where('service_speed', $speed);
    }

    public function scopeByDeliveryMethod($query, $method)
    {
        return $query->where('delivery_method', $method);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByOutlet($query, $outletId)
    {
        return $query->where('outlet_id', $outletId);
    }

    public function scopeByService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

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

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    public function scopeWithMembershipBenefits($query)
    {
        return $query->where(function($q) {
            $q->where('discount_amount', '>', 0)
              ->orWhere(function($q2) {
                  $q2->whereIn('delivery_method', ['pickup', 'delivery', 'pickup_delivery'])
                     ->where('pickup_delivery_fee', 0);
              });
        });
    }

    /**
     * Sinkronisasi status pembayaran Order ke tabel Payment
     */
    public function syncPaymentStatus(): void
    {
        // Ambil payment terakhir yang terkait
        $payment = $this->payments()->latest()->first();

        // Jika tidak ada payment, stop
        if (!$payment) return;

        // 1. Sinkronisasi Amount (Jika payment masih pending)
        // Cek apakah final_price berubah (gunakan wasChanged karena dipanggil di event updated)
        if ($this->wasChanged('final_price') && $payment->status === 'pending') {
            $payment->amount = $this->final_price ?? $this->total_price;
        }

        // 2. Sinkronisasi Status
        if ($this->wasChanged('payment_status')) {
            $newStatus = match ($this->payment_status) {
                'paid' => 'success',
                'failed' => 'failed',
                'refunded' => 'refunded',
                default => 'pending',
            };

            $payment->status = $newStatus;

            // Update tanggal bayar jika sukses
            if ($newStatus === 'success' && !$payment->paid_at) {
                $payment->paid_at = now();
            }
            // Kosongkan tanggal bayar jika berubah jadi pending/failed (opsional)
            else if ($newStatus !== 'success') {
                $payment->paid_at = null;
            }
        }

        // Simpan perubahan pada payment jika ada yang berubah
        if ($payment->isDirty()) {
            $payment->save();
        }
    }

    /**
     * Boot method
     */
    /**
     * Boot method
     */
   protected static function boot()
    {
        parent::boot();

        // 1. Auto-calculate prices saat creating
        static::creating(function ($order) {
            if (!$order->base_price && $order->service_id && $order->total_weight) {
                $order->calculatePrices();
            }
        });

        // 2. Auto-create Payment saat created
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
                // Gunakan final_price, fallback ke total_price, fallback ke 0
                'amount' => $order->final_price ?? $order->total_price ?? 0,
                'gateway' => $gateway,
                'status' => $paymentStatus,
                'transaction_id' => 'TRX-' . date('Ymd') . '-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'paid_at' => ($paymentStatus === 'success') ? now() : null,
                'notes' => 'Auto-generated from Order creation',
            ]);
        });

        // 3. Recalculate & Sync Payment saat updating
        static::updating(function ($order) {
            if ($order->isDirty(['service_id', 'total_weight', 'service_speed', 'delivery_method', 'customer_id'])) {
                $order->calculatePrices();
            }
        });

        // 4. Panggil syncPaymentStatus SETELAH update tersimpan (updated)
        static::updated(function ($order) {
            // Panggil method baru yang kita buat tadi
            $order->syncPaymentStatus();
        });
    }
}