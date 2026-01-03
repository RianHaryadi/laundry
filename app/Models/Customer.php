<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'address',
        'is_member',
        'available_coupons',
        'member_since',
        'birthday',
        'preferred_outlet_id',
        'email_notifications',
        'sms_notifications',
        'notes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_member' => 'boolean',
        'available_coupons' => 'integer',
        'member_since' => 'date',
        'birthday' => 'date',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
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
     * Scope a query to only include members.
     */
    public function scopeMembers($query)
    {
        return $query->where('is_member', true);
    }

    /**
     * Scope a query to only include non-members.
     */
    public function scopeNonMembers($query)
    {
        return $query->where('is_member', false);
    }

    /**
     * Scope a query to only include customers with available coupons.
     */
    public function scopeWithCoupons($query, int $minCoupons = 1)
    {
        return $query->where('available_coupons', '>=', $minCoupons);
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
     * Scope a query to only include customers with web account (has password).
     */
    public function scopeWithWebAccount($query)
    {
        return $query->whereNotNull('password');
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
     * Get the membership status badge color.
     */
    public function getMembershipColorAttribute(): string
    {
        return $this->is_member ? 'success' : 'secondary';
    }

    /**
     * Get the membership label.
     */
    public function getMembershipLabelAttribute(): string
    {
        return $this->is_member ? 'Member' : 'Non-Member';
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
     * Check if customer is a member.
     */
    public function isMember(): bool
    {
        return $this->is_member === true;
    }

    /**
     * Check if customer has web account access.
     */
    public function hasWebAccount(): bool
    {
        return !empty($this->password);
    }

    /**
     * Check if customer has available coupons.
     */
    public function hasCoupons(int $requiredCoupons = 1): bool
    {
        return $this->available_coupons >= $requiredCoupons;
    }

    /**
     * Add coupon to member customer when order is completed.
     * Only members receive coupons.
     */
    public function addCoupon(int $quantity = 1): bool
    {
        if (!$this->is_member) {
            return false; // Non-members don't get coupons
        }
        
        $this->increment('available_coupons', $quantity);
        return true;
    }

    /**
     * Use/redeem customer's coupon.
     */
    public function useCoupon(int $quantity = 1): bool
    {
        if ($this->available_coupons < $quantity) {
            return false; // Not enough coupons
        }
        
        $this->decrement('available_coupons', $quantity);
        return true;
    }

    /**
     * Activate membership for customer.
     */
    public function activateMembership(): void
    {
        if (!$this->is_member) {
            $this->update([
                'is_member' => true,
                'member_since' => now(),
            ]);
        }
    }

    /**
     * Deactivate membership for customer.
     */
    public function deactivateMembership(): void
    {
        $this->update([
            'is_member' => false,
            'member_since' => null,
            'available_coupons' => 0, // Reset coupons when membership is deactivated
        ]);
    }

    /**
     * Format customer name for display.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ($this->is_member ? ' ⭐' : '');
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

    /**
     * Get formatted coupon count for display.
     */
    public function getFormattedCouponsAttribute(): string
    {
        return $this->available_coupons . ' Kupon';
    }
}