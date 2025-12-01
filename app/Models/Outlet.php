<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'phone'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function machines()
    {
        return $this->hasMany(Machine::class);
    }

    public function prices()
    {
        return $this->hasMany(OutletPrice::class);
    }

    public function coupons()
{
    return $this->belongsToMany(Coupon::class, 'coupon_outlet');
}
}