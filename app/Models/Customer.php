<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'points',
        'total_points_earned', // New field
        'membership_level',
        'member_since',
        'birthday',
        'preferred_outlet_id',
        'email_notifications',
        'sms_notifications',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'points' => 'integer',
        'total_points_earned' => 'integer', // New field
        'member_since' => 'date',
        'birthday' => 'date',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
    ];

    /**
     * Get the preferred outlet of the customer.
     */
    public function preferredOutlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class, 'preferred_outlet_id');
    }

    /**
     * Get all orders for the customer.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Scope a query to only include customers of a specific membership level.
     */
    public function scopeOfMembershipLevel($query, string $level)
    {
        return $query->where('membership_level', $level);
    }

    /**
     * Scope a query to only include VIP customers.
     */
    public function scopeVip($query)
    {
        return $query->where('membership_level', 'vip');
    }

    /**
     * Scope a query to only include customers with high points.
     */
    public function scopeHighValue($query, int $minPoints = 1000)
    {
        return $query->where('points', '>=', $minPoints);
    }

    /**
     * Scope a query to only include customers with birthdays this month.
     */
    public function scopeBirthdayThisMonth($query)
    {
        return $query->whereMonth('birthday', now()->month);
    }

    /**
     * Scope a query to only include customers with birthdays today.
     */
    public function scopeBirthdayToday($query)
    {
        return $query->whereMonth('birthday', now()->month)
            ->whereDay('birthday', now()->day);
    }

    /**
     * Scope a query to only include customers who opted in for email notifications.
     */
    public function scopeEmailOptedIn($query)
    {
        return $query->where('email_notifications', true)
            ->whereNotNull('email');
    }

    /**
     * Scope a query to only include customers who opted in for SMS notifications.
     */
    public function scopeSmsOptedIn($query)
    {
        return $query->where('sms_notifications', true);
    }

    /**
     * Get the total amount spent by the customer.
     */
    public function getTotalSpentAttribute(): float
    {
        return $this->orders()->sum('total_amount') ?? 0;
    }

    /**
     * Get the formatted total spent.
     */
    public function getFormattedTotalSpentAttribute(): string
    {
        return 'Rp ' . number_format($this->total_spent, 0, ',', '.');
    }

    /**
     * Get the total number of orders.
     */
    public function getTotalOrdersAttribute(): int
    {
        return $this->orders()->count();
    }

    /**
     * Get the membership color for display.
     */
    public function getMembershipColorAttribute(): string
    {
        return match ($this->membership_level) {
            'bronze' => 'secondary',
            'silver' => 'info',
            'gold' => 'warning',
            'platinum' => 'success',
            'vip' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get the membership label.
     */
    public function getMembershipLabelAttribute(): string
    {
        return ucfirst($this->membership_level);
    }

    /**
     * Get the customer's age.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->birthday ? $this->birthday->age : null;
    }

    /**
     * Check if customer's birthday is today.
     */
    public function isBirthdayToday(): bool
    {
        if (!$this->birthday) {
            return false;
        }

        return $this->birthday->month === now()->month 
            && $this->birthday->day === now()->day;
    }

    /**
     * Check if customer's birthday is this month.
     */
    public function isBirthdayThisMonth(): bool
    {
        if (!$this->birthday) {
            return false;
        }

        return $this->birthday->month === now()->month;
    }

    /**
     * Check if customer is VIP.
     */
    public function isVip(): bool
    {
        return $this->membership_level === 'vip';
    }

    /**
     * Check if customer is a high value customer.
     */
    public function isHighValue(int $minPoints = 1000): bool
    {
        return $this->points >= $minPoints;
    }

    /**
     * Check if customer has active membership (not bronze).
     */
    public function hasActiveMembership(): bool
    {
        return $this->membership_level && $this->membership_level !== 'bronze';
    }

    /**
     * Get membership discount percentage.
     */
    public function getMembershipDiscount(): int
    {
        return match($this->membership_level) {
            'vip' => 15,
            'platinum' => 12,
            'gold' => 10,
            'silver' => 5,
            default => 0,
        };
    }

    /**
     * Check if customer has free pickup/delivery benefit.
     */
    public function hasFreePickupDelivery(): bool
    {
        return in_array($this->membership_level, ['gold', 'platinum', 'vip']);
    }

    /**
     * Add points to customer (for earning points from orders).
     * This updates both current points and total points earned.
     */
    public function addPoints(int $points): void
    {
        $this->increment('points', $points);
        $this->increment('total_points_earned', $points);
        $this->updateMembershipLevel();
    }

    /**
     * Redeem/deduct points from the customer (for using points as payment).
     * Note: This only affects current points, not total_points_earned.
     */
    public function redeemPoints(int $points): bool
    {
        if ($this->points < $points) {
            return false; // Not enough points
        }
        
        $this->decrement('points', $points);
        // Note: total_points_earned stays the same!
        return true;
    }

    /**
     * Deduct points from the customer (legacy method).
     * @deprecated Use redeemPoints() instead for better semantics
     */
    public function deductPoints(int $points): void
    {
        $newPoints = max(0, $this->points - $points);
        $this->update(['points' => $newPoints]);
    }

    /**
     * Auto-update membership level based on total_points_earned.
     * This ensures membership is determined by lifetime earnings, not current balance.
     */
    public function updateMembershipLevel(): void
    {
        $totalPoints = $this->total_points_earned;
        
        $newLevel = match (true) {
            $totalPoints >= 4000 => 'vip',
            $totalPoints >= 2000 => 'platinum',
            $totalPoints >= 1000 => 'gold',
            $totalPoints >= 500 => 'silver',
            default => 'bronze',
        };

        if ($this->membership_level !== $newLevel) {
            $this->update(['membership_level' => $newLevel]);
        }
    }

    /**
     * Automatically upgrade membership based on points.
     * @deprecated Use updateMembershipLevel() instead
     */
    public function checkAndUpgradeMembership(): void
    {
        $this->updateMembershipLevel();
    }

    /**
     * Get membership requirements based on total points earned.
     */
    public static function getMembershipRequirements(): array
    {
        return [
            'bronze' => 0,
            'silver' => 500,
            'gold' => 1000,
            'platinum' => 2000,
            'vip' => 4000,
        ];
    }

    /**
     * Get points needed for next membership level.
     */
    public function getPointsToNextLevelAttribute(): ?int
    {
        $requirements = self::getMembershipRequirements();
        $levels = array_keys($requirements);
        $currentIndex = array_search($this->membership_level, $levels);

        if ($currentIndex === false || $currentIndex === count($levels) - 1) {
            return null; // Already at max level
        }

        $nextLevel = $levels[$currentIndex + 1];
        $pointsNeeded = $requirements[$nextLevel] - $this->total_points_earned;

        return max(0, $pointsNeeded);
    }

    /**
     * Get the next membership level.
     */
    public function getNextMembershipLevelAttribute(): ?string
    {
        $levels = ['bronze', 'silver', 'gold', 'platinum', 'vip'];
        $currentIndex = array_search($this->membership_level, $levels);

        if ($currentIndex === false || $currentIndex === count($levels) - 1) {
            return null; // Already at max level
        }

        return $levels[$currentIndex + 1];
    }

    /**
     * Get membership tier label based on total points earned.
     */
    public function getMembershipTierAttribute(): string
    {
        return match (true) {
            $this->total_points_earned >= 4000 => 'VIP',
            $this->total_points_earned >= 2000 => 'Platinum',
            $this->total_points_earned >= 1000 => 'Gold',
            $this->total_points_earned >= 500 => 'Silver',
            default => 'Bronze',
        };
    }

    /**
     * Format customer name for display.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ($this->isVip() ? ' 👑' : '');
    }

    /**
     * Check if customer has email.
     */
    public function hasEmail(): bool
    {
        return !empty($this->email);
    }

    /**
     * Check if customer can receive email notifications.
     */
    public function canReceiveEmailNotifications(): bool
    {
        return $this->email_notifications && $this->hasEmail();
    }

    /**
     * Check if customer can receive SMS notifications.
     */
    public function canReceiveSmsNotifications(): bool
    {
        return $this->sms_notifications;
    }

    /**
     * Get customer membership duration in days.
     */
    public function getMembershipDurationAttribute(): ?int
    {
        return $this->member_since ? $this->member_since->diffInDays(now()) : null;
    }

    /**
     * Get customer membership duration in human readable format.
     */
    public function getMembershipDurationHumanAttribute(): ?string
    {
        return $this->member_since ? $this->member_since->diffForHumans(null, true) : null;
    }
}