<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'description', 
        'base_price',
        'price_per_kg',
        'price_per_unit', // NEW
        'pricing_type',   // NEW
        'duration_hours',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'price_per_unit' => 'decimal:2', // NEW
        'duration_hours' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get all orders using this service directly
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get all order items for this service
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get all outlet-specific prices for this service
     */
    public function prices(): HasMany
    {
        return $this->hasMany(OutletPrice::class);
    }

    /**
     * Get all orders that include this service through order items
     */
    public function ordersThrough(): HasManyThrough
    {
        return $this->hasManyThrough(
            Order::class,
            OrderItem::class,
            'service_id',
            'id',
            'id',
            'order_id'
        );
    }

    /**
     * Get the price for this service at a specific outlet
     * 
     * @param Outlet $outlet
     * @return float
     */
    public function priceAt(Outlet $outlet): float
    {
        $outletPrice = $this->prices()
            ->where('outlet_id', $outlet->id)
            ->first();

        return $outletPrice ? (float) $outletPrice->price : (float) $this->base_price;
    }

    /**
     * Get the price per kg for this service at a specific outlet
     * 
     * @param Outlet|int $outlet
     * @return float
     */
    public function pricePerKgAt($outlet): float
    {
        $outletId = $outlet instanceof Outlet ? $outlet->id : $outlet;
        
        $outletPrice = $this->prices()
            ->where('outlet_id', $outletId)
            ->first();

        if ($outletPrice && isset($outletPrice->price_per_kg)) {
            return (float) $outletPrice->price_per_kg;
        }

        return $this->price_per_kg ?? $this->base_price;
    }

    /**
     * Get the price per unit for this service at a specific outlet
     * NEW METHOD
     * 
     * @param Outlet|int $outlet
     * @return float|null
     */
    public function pricePerUnitAt($outlet): ?float
    {
        $outletId = $outlet instanceof Outlet ? $outlet->id : $outlet;
        
        $outletPrice = $this->prices()
            ->where('outlet_id', $outletId)
            ->first();

        if ($outletPrice && isset($outletPrice->price_per_unit)) {
            return (float) $outletPrice->price_per_unit;
        }

        return $this->price_per_unit;
    }

    /**
     * Get the price for this service at a specific outlet by ID
     * 
     * @param int $outletId
     * @return float
     */
    public function priceAtOutlet(int $outletId): float
    {
        $outletPrice = $this->prices()
            ->where('outlet_id', $outletId)
            ->first();

        return $outletPrice ? (float) $outletPrice->price : (float) $this->base_price;
    }

    /**
     * Check if this service has a custom price at a specific outlet
     * 
     * @param Outlet|int $outlet
     * @return bool
     */
    public function hasCustomPriceAt($outlet): bool
    {
        $outletId = $outlet instanceof Outlet ? $outlet->id : $outlet;
        
        return $this->prices()->where('outlet_id', $outletId)->exists();
    }

    /**
     * Calculate price for given weight or quantity
     * UPDATED METHOD - Now supports both kg and unit pricing
     * 
     * @param float $amount (weight in kg or quantity in units)
     * @param string $type ('kg' or 'unit')
     * @param int|null $outletId
     * @return float
     */
    public function calculatePrice(float $amount, string $type = 'kg', ?int $outletId = null): float
    {
        if ($type === 'unit') {
            $pricePerUnit = $outletId 
                ? $this->pricePerUnitAt($outletId) 
                : $this->price_per_unit;
            
            return $amount * ($pricePerUnit ?? 0);
        }
        
        // Default to kg pricing
        $pricePerKg = $outletId 
            ? $this->pricePerKgAt($outletId) 
            : ($this->price_per_kg ?? $this->base_price);

        return $amount * $pricePerKg;
    }

    /**
     * Calculate price with membership discount
     * UPDATED METHOD - Now supports both kg and unit pricing
     * 
     * @param float $amount
     * @param string $type ('kg' or 'unit')
     * @param Customer $customer
     * @param int|null $outletId
     * @return array
     */
    public function calculatePriceWithDiscount(float $amount, string $type, Customer $customer, ?int $outletId = null): array
    {
        $basePrice = $this->calculatePrice($amount, $type, $outletId);
        $discountPercentage = $customer->getMembershipDiscount();
        $discountAmount = ($basePrice * $discountPercentage) / 100;
        $finalPrice = $basePrice - $discountAmount;

        return [
            'base_price' => $basePrice,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
        ];
    }

    /**
     * Get total revenue generated by this service (from direct orders)
     * 
     * @return float
     */
    public function getTotalRevenue(): float
    {
        $directRevenue = $this->orders()
            ->whereIn('status', ['completed'])
            ->whereIn('payment_status', ['paid'])
            ->sum('final_price');

        $itemsRevenue = $this->orderItems()
            ->whereHas('order', function ($query) {
                $query->whereIn('status', ['completed', 'paid']);
            })
            ->sum(\DB::raw('quantity * price'));

        return $directRevenue + $itemsRevenue;
    }

    /**
     * Get total quantity sold
     * 
     * @return int
     */
    public function getTotalQuantitySold(): int
    {
        return $this->orderItems()
            ->whereHas('order', function ($query) {
                $query->whereIn('status', ['completed', 'paid']);
            })
            ->sum('quantity');
    }

    /**
     * Get total weight processed (from direct orders)
     * 
     * @return float
     */
    public function getTotalWeightProcessed(): float
    {
        return $this->orders()
            ->whereIn('status', ['completed'])
            ->whereIn('payment_status', ['paid'])
            ->sum('total_weight');
    }

    /**
     * Get total units processed (from direct orders)
     * NEW METHOD
     * 
     * @return int
     */
    public function getTotalUnitsProcessed(): int
    {
        return $this->orders()
            ->whereIn('status', ['completed'])
            ->whereIn('payment_status', ['paid'])
            ->sum('total_quantity');
    }

    /**
     * Check if service supports kg pricing
     * NEW METHOD
     * 
     * @return bool
     */
    public function supportsKgPricing(): bool
    {
        return in_array($this->pricing_type, ['kg', 'both']);
    }

    /**
     * Check if service supports unit pricing
     * NEW METHOD
     * 
     * @return bool
     */
    public function supportsUnitPricing(): bool
    {
        return in_array($this->pricing_type, ['unit', 'both']);
    }

    /**
     * Check if service supports both pricing types
     * NEW METHOD
     * 
     * @return bool
     */
    public function supportsBothPricing(): bool
    {
        return $this->pricing_type === 'both';
    }

    /**
     * Scope to filter active services
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter services by pricing type
     * NEW SCOPE
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type ('kg', 'unit', 'both')
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPricingType($query, string $type)
    {
        return $query->where('pricing_type', $type);
    }

    /**
     * Scope to filter services that support kg pricing
     * NEW SCOPE
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSupportsKg($query)
    {
        return $query->whereIn('pricing_type', ['kg', 'both']);
    }

    /**
     * Scope to filter services that support unit pricing
     * NEW SCOPE
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSupportsUnit($query)
    {
        return $query->whereIn('pricing_type', ['unit', 'both']);
    }

    /**
     * Scope to filter services by price range
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float $min
     * @param float $max
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePriceBetween($query, float $min, float $max)
    {
        return $query->whereBetween('base_price', [$min, $max]);
    }

    /**
     * Scope to filter services by price per kg range
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float $min
     * @param float $max
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePricePerKgBetween($query, float $min, float $max)
    {
        return $query->whereBetween('price_per_kg', [$min, $max]);
    }

    /**
     * Scope to get popular services
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePopular($query, int $limit = 10)
    {
        return $query->withCount(['orderItems', 'orders'])
            ->orderByRaw('order_items_count + orders_count DESC')
            ->limit($limit);
    }

    /**
     * Scope to get services by duration
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $hours
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDuration($query, int $hours)
    {
        return $query->where('duration_hours', '<=', $hours);
    }

    /**
     * Get formatted base price
     * 
     * @return string
     */
    public function getFormattedBasePriceAttribute(): string
    {
        return 'Rp ' . number_format($this->base_price, 0, ',', '.');
    }

    /**
     * Get formatted price (supports both kg and unit)
     * UPDATED METHOD
     * 
     * @return string
     */
    public function getFormattedPriceAttribute(): string
    {
        $parts = [];
        
        if ($this->price_per_kg) {
            $parts[] = 'Rp ' . number_format($this->price_per_kg, 0, ',', '.') . '/kg';
        }
        
        if ($this->price_per_unit) {
            $parts[] = 'Rp ' . number_format($this->price_per_unit, 0, ',', '.') . '/unit';
        }
        
        if (empty($parts)) {
            return 'Rp ' . number_format($this->base_price, 0, ',', '.');
        }
        
        return implode(' | ', $parts);
    }

    /**
     * Get formatted price per kg
     * NEW METHOD
     * 
     * @return string|null
     */
    public function getFormattedPricePerKgAttribute(): ?string
    {
        if (!$this->price_per_kg) {
            return null;
        }
        
        return 'Rp ' . number_format($this->price_per_kg, 0, ',', '.') . '/kg';
    }

    /**
     * Get formatted price per unit
     * NEW METHOD
     * 
     * @return string|null
     */
    public function getFormattedPricePerUnitAttribute(): ?string
    {
        if (!$this->price_per_unit) {
            return null;
        }
        
        return 'Rp ' . number_format($this->price_per_unit, 0, ',', '.') . '/unit';
    }

    /**
     * Get pricing type label
     * NEW METHOD
     * 
     * @return string
     */
    public function getPricingTypeLabelAttribute(): string
    {
        return match($this->pricing_type) {
            'kg' => 'Per Kilogram',
            'unit' => 'Per Unit/Item',
            'both' => 'Per Kg & Per Unit',
            default => 'Unknown',
        };
    }

    /**
     * Get formatted duration
     * 
     * @return string
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_hours) {
            return '-';
        }

        if ($this->duration_hours < 24) {
            return $this->duration_hours . ' jam';
        }

        $days = floor($this->duration_hours / 24);
        $hours = $this->duration_hours % 24;

        $result = $days . ' hari';
        if ($hours > 0) {
            $result .= ' ' . $hours . ' jam';
        }

        return $result;
    }

    /**
     * Get short description
     * 
     * @param int $length
     * @return string
     */
    public function getShortDescription(int $length = 100): string
    {
        if (!$this->description) {
            return '';
        }

        return \Illuminate\Support\Str::limit($this->description, $length);
    }

    /**
     * Get service statistics
     * UPDATED METHOD - Now includes unit statistics
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_orders' => $this->orders()->count(),
            'total_revenue' => $this->getTotalRevenue(),
            'total_weight' => $this->getTotalWeightProcessed(),
            'total_units' => $this->getTotalUnitsProcessed(), // NEW
            'average_order_value' => $this->orders()->count() > 0 
                ? $this->getTotalRevenue() / $this->orders()->count() 
                : 0,
        ];
    }

    /**
     * Check if service is express (duration <= 24 hours)
     * 
     * @return bool
     */
    public function isExpress(): bool
    {
        return $this->duration_hours <= 24;
    }

    /**
     * Check if service is premium (price > 10000)
     * UPDATED METHOD - Considers both pricing types
     * 
     * @return bool
     */
    public function isPremium(): bool
    {
        $maxPrice = max(
            $this->price_per_kg ?? 0,
            $this->price_per_unit ?? 0,
            $this->base_price ?? 0
        );
        
        return $maxPrice > 10000;
    }
}