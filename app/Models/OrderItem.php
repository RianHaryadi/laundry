<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Models\Service;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'service_id',
        'pricing_type',      // ✅ Sekarang ada di database
        'price_per_kg',
        'price_per_unit',
        'quantity',
        'weight',
        'price',
        'subtotal',
        'storage_location',
        'photo_proof',
    ];

    protected $casts = [
        'weight'          => 'decimal:2',
        'price'           => 'integer',
        'subtotal'        => 'decimal:2',
        'quantity'        => 'integer',
        'price_per_kg'    => 'decimal:2',
        'price_per_unit'  => 'decimal:2',
        'pricing_type'    => 'string',
        'storage_location' => 'string',
        'photo_proof'     => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($item) {
            Log::info('🆕 OrderItem::creating', [
                'order_id' => $item->order_id,
                'service_id' => $item->service_id,
            ]);

            // Validasi service_id
            if (!$item->service_id) {
                Log::error('❌ service_id is NULL!');
                throw new \Exception('service_id tidak boleh kosong');
            }

            // Load service
            $service = Service::find($item->service_id);
            
            if (!$service) {
                Log::error('❌ Service not found', ['service_id' => $item->service_id]);
                throw new \Exception("Service dengan ID {$item->service_id} tidak ditemukan");
            }

            Log::info('✅ Service loaded', [
                'id' => $service->id,
                'name' => $service->name,
                'pricing_type' => $service->pricing_type,
            ]);

            // Set metadata service (untuk history)
            $item->pricing_type = $service->pricing_type;
            $item->price_per_kg = $service->price_per_kg ?? 0;
            $item->price_per_unit = $service->price_per_unit ?? 0;

            // Hitung harga & subtotal
            if ($service->pricing_type === 'kg') {
                $unitPrice = $service->price_per_kg ?? 0;
                $item->price = $unitPrice;
                $item->subtotal = $unitPrice * max(0, (float) ($item->weight ?? 0));
            } else {
                $unitPrice = $service->price_per_unit ?? 0;
                $item->price = $unitPrice;
                $item->subtotal = $unitPrice * max(0, (int) ($item->quantity ?? 0));
            }

            Log::info('💰 Price calculated', [
                'pricing_type' => $item->pricing_type,
                'price' => $item->price,
                'weight' => $item->weight,
                'quantity' => $item->quantity,
                'subtotal' => $item->subtotal,
            ]);
        });

        static::created(function ($item) {
            Log::info('✅ OrderItem created successfully!', [
                'id' => $item->id,
                'order_id' => $item->order_id,
                'service_id' => $item->service_id,
                'pricing_type' => $item->pricing_type,
            ]);
        });

        static::updating(function ($item) {
            // Recalculate jika ada perubahan
            if ($item->isDirty(['service_id', 'weight', 'quantity'])) {
                $service = Service::find($item->service_id);
                
                if ($service) {
                    $item->pricing_type = $service->pricing_type;
                    $item->price_per_kg = $service->price_per_kg ?? 0;
                    $item->price_per_unit = $service->price_per_unit ?? 0;

                    if ($service->pricing_type === 'kg') {
                        $unitPrice = $service->price_per_kg ?? 0;
                        $item->price = $unitPrice;
                        $item->subtotal = $unitPrice * max(0, (float) ($item->weight ?? 0));
                    } else {
                        $unitPrice = $service->price_per_unit ?? 0;
                        $item->price = $unitPrice;
                        $item->subtotal = $unitPrice * max(0, (int) ($item->quantity ?? 0));
                    }
                }
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