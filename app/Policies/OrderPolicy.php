<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Order;

class OrderPolicy
{
    /**
     * Determine if user can view any orders
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['owner', 'admin', 'staff', 'courier']);
    }

    /**
     * Determine if user can view the order
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->role === 'owner') {
            return true;
        }

        // Admin, Staff, Courier can only view orders from their outlet
        return $order->outlet_id === $user->outlet_id;
    }

    /**
     * Determine if user can create orders
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['owner', 'admin', 'staff']);
    }

    /**
     * Determine if user can update the order
     */
    public function update(User $user, Order $order): bool
    {
        if ($user->role === 'owner') {
            return true;
        }

        if (in_array($user->role, ['admin', 'staff'])) {
            return $order->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Determine if user can delete the order
     */
    public function delete(User $user, Order $order): bool
    {
        if ($user->role === 'owner') {
            return true;
        }

        if ($user->role === 'admin') {
            return $order->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Determine if user can restore the order
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->role === 'owner';
    }

    /**
     * Determine if user can permanently delete the order
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->role === 'owner';
    }
}