<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'gateway',
        'transaction_id',
        'amount',
        'status',
        'paid_at',
        'notes',
        'coupon_used', // TAMBAHAN: Apakah pakai kupon untuk pembayaran
        'coupon_count', // TAMBAHAN: Berapa kupon yang digunakan
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'coupon_used' => 'boolean',
        'coupon_count' => 'integer',
    ];

    /**
     * Relationships
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Accessors
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu Pembayaran',
            'success' => 'Berhasil',
            'failed' => 'Gagal',
            'refunded' => 'Dikembalikan',
            default => 'Unknown',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'secondary',
            'success' => 'success',
            'failed' => 'danger',
            'refunded' => 'info',
            default => 'gray',
        };
    }

    public function getGatewayLabelAttribute(): string
    {
        return match($this->gateway) {
            'cash' => 'Tunai',
            'bank_transfer' => 'Transfer Bank',
            'e_wallet' => 'E-Wallet',
            'qris' => 'QRIS',
            'credit_card' => 'Kartu Kredit',
            'debit_card' => 'Kartu Debit',
            default => ucfirst(str_replace('_', ' ', $this->gateway)),
        };
    }

    /**
     * Status Check Methods
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * ============================================
     * KUPON PAYMENT METHODS - TAMBAHAN BARU
     * ============================================
     */

    /**
     * Process payment with coupon redemption
     * Digunakan ketika customer ingin pakai kupon untuk bayar/diskon
     * 
     * @param Customer $customer
     * @param int $couponCount Jumlah kupon yang ingin digunakan
     * @param float $couponValue Nilai per kupon (misal: 10000)
     * @return bool
     */
    public function processWithCoupon($customer, int $couponCount = 1, float $couponValue = 10000): bool
    {
        // Validasi: Customer harus member
        if (!$customer->isMember()) {
            return false;
        }

        // Validasi: Customer harus punya cukup kupon
        if (!$customer->hasCoupons($couponCount)) {
            return false;
        }

        // Hitung total nilai kupon
        $totalCouponValue = $couponCount * $couponValue;

        // Validasi: Nilai kupon tidak boleh melebihi amount payment
        if ($totalCouponValue > $this->amount) {
            return false;
        }

        // Gunakan kupon dari customer
        if ($customer->useCoupon($couponCount)) {
            
            // Update payment: kurangi amount dengan nilai kupon
            $this->update([
                'amount' => $this->amount - $totalCouponValue,
                'coupon_used' => true,
                'coupon_count' => $couponCount,
                'notes' => "Menggunakan {$couponCount} kupon (Rp " . number_format($totalCouponValue, 0, ',', '.') . ")",
            ]);

            // Jika amount jadi 0, otomatis mark as success
            if ($this->amount <= 0) {
                $this->markAsSuccess();
            }

            return true;
        }

        return false;
    }

    /**
     * Check if this payment used coupon
     */
    public function usedCoupon(): bool
    {
        return $this->coupon_used === true;
    }

    /**
     * Get coupon discount amount
     */
    public function getCouponDiscountAmount(float $couponValue = 10000): float
    {
        if (!$this->coupon_used) {
            return 0;
        }

        return $this->coupon_count * $couponValue;
    }

    /**
     * Get formatted coupon discount
     */
    public function getFormattedCouponDiscountAttribute(): string
    {
        $discount = $this->getCouponDiscountAmount();
        return 'Rp ' . number_format($discount, 0, ',', '.');
    }

    /**
     * Mark payment as success
     */
    public function markAsSuccess(): void
    {
        $this->update([
            'status' => 'success',
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason ? "Failed: {$reason}" : 'Payment failed',
        ]);
    }

    /**
     * Mark payment as refunded
     */
    public function markAsRefunded(string $reason = null): void
    {
        $this->update([
            'status' => 'refunded',
            'notes' => $reason ? "Refunded: {$reason}" : 'Payment refunded',
        ]);
    }

    /**
     * Cancel payment (only if pending)
     */
    public function cancel(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->update([
            'status' => 'failed',
            'notes' => 'Payment cancelled',
        ]);

        return true;
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopeWithCoupon($query)
    {
        return $query->where('coupon_used', true);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate transaction ID if not provided
        static::creating(function ($payment) {
            if (!$payment->transaction_id) {
                $payment->transaction_id = 'TRX-' . date('Ymd') . '-' . strtoupper(uniqid());
            }
        });

        // Sync order payment status when payment status changes
        static::updated(function ($payment) {
            if ($payment->wasChanged('status') && $payment->order) {
                $order = $payment->order;
                
                $newPaymentStatus = match ($payment->status) {
                    'success' => 'paid',
                    'failed' => 'failed',
                    'refunded' => 'refunded',
                    default => 'pending',
                };

                // Only update if different
                if ($order->payment_status !== $newPaymentStatus) {
                    $order->update(['payment_status' => $newPaymentStatus]);
                }
            }
        });
    }
}