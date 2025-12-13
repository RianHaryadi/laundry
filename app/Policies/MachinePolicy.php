<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Machine;

class MachinePolicy
{
    /**
     * Determine if user can view any machines
     */
    public function viewAny(User $user): bool
    {
        // Hanya Owner dan Admin, Staff TIDAK BISA
        return $user->isOwner() || $user->isAdmin();
    }

    /**
     * Determine if user can view the machine
     */
    public function view(User $user, Machine $machine): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        // Hanya Admin, Staff TIDAK BISA
        if ($user->isAdmin()) {
            return $machine->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Determine if user can create machines
     */
    public function create(User $user): bool
    {
        // Hanya Owner dan Admin, Staff TIDAK BISA
        return $user->isOwner() || $user->isAdmin();
    }

    /**
     * Determine if user can update the machine
     */
    public function update(User $user, Machine $machine): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $machine->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Determine if user can delete the machine
     */
    public function delete(User $user, Machine $machine): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $machine->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Determine if user can restore the machine
     */
    public function restore(User $user, Machine $machine): bool
    {
        return $user->isOwner();
    }

    /**
     * Determine if user can permanently delete the machine
     */
    public function forceDelete(User $user, Machine $machine): bool
    {
        return $user->isOwner();
    }
}