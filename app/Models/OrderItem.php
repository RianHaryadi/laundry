<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'weight'   => 'decimal:2',
        'price'    => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * LOGIKA PENENTUAN HARGA & SUBTOTAL
     * Berjalan otomatis saat save.
     */
    protected static function booted()
    {
        static::saving(function ($item) {
            // 1. Pastikan Service ID ada
            if (!$item->service_id) return;

            // 2. Ambil data master Service
            $service = Service::find($item->service_id);
            if (!$service) return;

            // 3. LOGIKA DETEKSI HARGA (KG vs SATUAN)
            
            // KASUS A: Jika inputnya BERAT (Weight diisi)
            if (!empty($item->weight) && $item->weight > 0) {
                // Ambil harga dari kolom price_per_kg milik service
                // Jika tidak ada kolom itu, fallback ke base_price
                $unitPrice = $service->price_per_kg ?? $service->base_price ?? 0;
                
                $item->price = $unitPrice;
                $item->subtotal = $unitPrice * (float) $item->weight;
            }
            
            // KASUS B: Jika inputnya QUANTITY (Qty diisi)
            elseif (!empty($item->quantity) && $item->quantity > 0) {
                // Ambil harga dari kolom price_per_unit milik service
                // Jika tidak ada, fallback ke base_price
                $unitPrice = $service->price_per_unit ?? $service->base_price ?? 0;
                
                $item->price = $unitPrice;
                $item->subtotal = $unitPrice * (int) $item->quantity;
            }
            
            // KASUS C: Jika data tidak lengkap (untuk jaga-jaga)
            else {
                $item->price = 0;
                $item->subtotal = 0;
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}