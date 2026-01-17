<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'outlet_id', 
        'name', 
        'serial_number',
        'type', 
        'status', 
        'manufacturer',
        'model',
        'purchase_date',
        'purchase_price',
        'warranty_until',
        'supplier',
        'last_maintenance',
        'maintenance_interval',
        'specifications',
        'notes'
    ];
    
    protected $casts = [
        'last_maintenance' => 'date',
        'purchase_date' => 'date',
        'warranty_until' => 'date',
        'maintenance_interval' => 'integer',
        'purchase_price' => 'decimal:2',
    ];
    
    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
    
    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }
}