<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OutletPrice;

class OutletPricePolicy
{
    /**
     * Determine if user can view any outlet prices
     */
    public function viewAny(User $user): bool
    {
        return $user->canAccessOutletPrices();
    }

    /**
     * Determine if user can view the outlet price
     */
    public function view(User $user, OutletPrice $outletPrice): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        // Admin and Staff can only view prices from their outlet
        return $outletPrice->outlet_id === $user->outlet_id;
    }

    /**
     * Determine if user can create outlet prices
     */
    public function create(User $user): bool
    {
        // Only Owner and Admin can create outlet prices
        return $user->isOwner() || $user->isAdmin();
    }

    /**
     * Determine if user can update the outlet price
     */
    public function update(User $user, OutletPrice $outletPrice): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $outletPrice->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Determine if user can delete the outlet price
     */
    public function delete(User $user, OutletPrice $outletPrice): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $outletPrice->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Determine if user can restore the outlet price
     */
    public function restore(User $user, OutletPrice $outletPrice): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $outletPrice->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Determine if user can permanently delete the outlet price
     */
    public function forceDelete(User $user, OutletPrice $outletPrice): bool
    {
        return $user->isOwner();
    }
}