<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'weight'        => 'decimal:2',
        'price'         => 'decimal:2',
        'subtotal'      => 'decimal:2',
        'quantity'      => 'integer',
        'price_per_kg'  => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'pricing_type'  => 'string',
        'storage_location' => 'string',
        'photo_proof'   => 'array',
    ];

    /**
     * ✅ LOGIKA AUTO-CALCULATE saat save
     * Menghitung price dan subtotal berdasarkan pricing_type
     */
    protected static function booted()
    {
        static::saving(function ($item) {
            // Skip jika service_id tidak ada
            if (!$item->service_id) return;

            // Ambil data service
            $service = Service::find($item->service_id);
            if (!$service) return;

            // ✅ KASUS 1: Pricing by KG (weight-based)
            if ($item->pricing_type === 'kg' && !empty($item->weight) && $item->weight > 0) {
                $unitPrice = $item->price_per_kg ?? $service->price_per_kg ?? 0;
                $item->price = $unitPrice;
                $item->subtotal = $unitPrice * (float) $item->weight;
            }
            
            // ✅ KASUS 2: Pricing by UNIT (quantity-based)
            elseif ($item->pricing_type === 'unit' && !empty($item->quantity) && $item->quantity > 0) {
                $unitPrice = $item->price_per_unit ?? $service->price_per_unit ?? 0;
                $item->price = $unitPrice;
                $item->subtotal = $unitPrice * (int) $item->quantity;
            }
            
            // ✅ KASUS 3: Data belum lengkap (default 0)
            else {
                $item->price = 0;
                $item->subtotal = 0;
            }
        });
    }

    // ===================================
    // RELATIONSHIPS
    // ===================================

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // ===================================
    // ACCESSORS
    // ===================================

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal ?? 0, 0, ',', '.');
    }

    /**
     * Get pricing type label
     */
    public function getPricingTypeLabelAttribute(): string
    {
        return match($this->pricing_type) {
            'kg' => 'Per Kilogram',
            'unit' => 'Per Unit/Satuan',
            default => 'Unknown',
        };
    }
}