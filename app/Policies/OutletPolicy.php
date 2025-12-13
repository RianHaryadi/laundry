<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OutletPrice;

class OutletPolicy
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
        return $user->canAccessOutletPrices();
    }

    /**
     * Determine if user can create outlet prices
     */
    public function create(User $user): bool
    {
        return $user->canManageOutletPrices();
    }

    /**
     * Determine if user can update the outlet price
     */
    public function update(User $user, OutletPrice $outletPrice): bool
    {
        return $user->canManageOutletPrices();
    }

    /**
     * Determine if user can delete the outlet price
     */
    public function delete(User $user, OutletPrice $outletPrice): bool
    {
        return $user->canManageOutletPrices();
    }

    /**
     * Determine if user can restore the outlet price
     */
    public function restore(User $user, OutletPrice $outletPrice): bool
    {
        return $user->isOwner();
    }

    /**
     * Determine if user can permanently delete the outlet price
     */
    public function forceDelete(User $user, OutletPrice $outletPrice): bool
    {
        return $user->isOwner();
    }
}