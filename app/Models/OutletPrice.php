<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutletPrice extends Model
{
    use HasFactory;

    protected $fillable = ['outlet_id', 'service_id', 'price'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}