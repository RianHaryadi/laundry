<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'courier_id',
        'type',
        'status',
        'latitude',
        'longitude',
        'scheduled_time',
        'actual_time',
        'pickup_address',
        'delivery_address',
        'notes',
        'photo',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'scheduled_time' => 'datetime',
        'actual_time' => 'datetime',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    // Type helpers
    public function isPickup(): bool
    {
        return $this->type === 'pickup';
    }

    public function isDelivery(): bool
    {
        return $this->type === 'delivery';
    }

    // Status helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['delivered', 'picked_up']);
    }
}