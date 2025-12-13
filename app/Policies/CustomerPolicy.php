<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Customer;

class CustomerPolicy
{
    /**
     * Determine if user can view any customers
     */
    public function viewAny(User $user): bool
    {
        return $user->canAccessCustomers();
    }

    /**
     * Determine if user can view the customer
     */
    public function view(User $user, Customer $customer): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        // Admin and Staff can only view customers from their outlet
        return $customer->outlet_id === $user->outlet_id;
    }

    /**
     * Determine if user can create customers
     */
    public function create(User $user): bool
    {
        return $user->canManageCustomers();
    }

    /**
     * Determine if user can update the customer
     */
    public function update(User $user, Customer $customer): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin() || $user->isStaff()) {
            return $customer->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Determine if user can delete the customer
     */
    public function delete(User $user, Customer $customer): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $customer->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Determine if user can restore the customer
     */
    public function restore(User $user, Customer $customer): bool
    {
        return $user->isOwner();
    }

    /**
     * Determine if user can permanently delete the customer
     */
    public function forceDelete(User $user, Customer $customer): bool
    {
        return $user->isOwner();
    }
}