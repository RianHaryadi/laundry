<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = ['outlet_id', 'name', 'type', 'status', 'last_maintenance'];

    protected $casts = ['last_maintenance' => 'date'];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }
}