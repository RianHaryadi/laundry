<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Coupon;

class CouponPolicy
{
    /**
     * Determine if user can view any coupons
     */
    public function viewAny(User $user): bool
    {
        return $user->canAccessCoupons();
    }

    /**
     * Determine if user can view the coupon
     */
    public function view(User $user, Coupon $coupon): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        // Admin and Staff can only view coupons from their outlet
        if ($coupon->outlet_id) {
            return $coupon->outlet_id === $user->outlet_id;
        }

        // Global coupons can be viewed by all
        return true;
    }

    /**
     * Determine if user can create coupons
     */
    public function create(User $user): bool
    {
        // Only Owner can create coupons
        return $user->isOwner();
    }

    /**
     * Determine if user can update the coupon
     */
    public function update(User $user, Coupon $coupon): bool
    {
        // Only Owner can update coupons
        return $user->isOwner();
    }

    /**
     * Determine if user can delete the coupon
     */
    public function delete(User $user, Coupon $coupon): bool
    {
        // Only Owner can delete coupons
        return $user->isOwner();
    }

    /**
     * Determine if user can restore the coupon
     */
    public function restore(User $user, Coupon $coupon): bool
    {
        return $user->isOwner();
    }

    /**
     * Determine if user can permanently delete the coupon
     */
    public function forceDelete(User $user, Coupon $coupon): bool
    {
        return $user->isOwner();
    }
}