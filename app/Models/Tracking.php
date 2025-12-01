<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'courier_id', 'latitude', 'longitude', 'status'];

    protected $casts = [
        'latitude'  => 'decimal:10,7',
        'longitude' => 'decimal:10,7',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }
}