<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Maintenance;

class MaintenancePolicy
{
    /**
     * Determine if user can view any maintenances
     */
    public function viewAny(User $user): bool
    {
        // Hanya Owner dan Admin, Staff TIDAK BISA
        return $user->isOwner() || $user->isAdmin();
    }

    /**
     * Determine if user can view the maintenance
     */
    public function view(User $user, Maintenance $maintenance): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        // Hanya Admin, Staff TIDAK BISA
        if ($user->isAdmin()) {
            return $maintenance->machine->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Determine if user can create maintenances
     */
    public function create(User $user): bool
    {
        // Hanya Owner dan Admin, Staff TIDAK BISA
        return $user->isOwner() || $user->isAdmin();
    }

    /**
     * Determine if user can update the maintenance
     */
    public function update(User $user, Maintenance $maintenance): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $maintenance->machine->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Determine if user can delete the maintenance
     */
    public function delete(User $user, Maintenance $maintenance): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $maintenance->machine->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Determine if user can restore the maintenance
     */
    public function restore(User $user, Maintenance $maintenance): bool
    {
        return $user->isOwner();
    }

    /**
     * Determine if user can permanently delete the maintenance
     */
    public function forceDelete(User $user, Maintenance $maintenance): bool
    {
        return $user->isOwner();
    }

    /**
     * Determine if user can complete the maintenance
     */
    public function complete(User $user, Maintenance $maintenance): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $maintenance->machine->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Determine if user can schedule the maintenance
     */
    public function schedule(User $user, Maintenance $maintenance): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $maintenance->machine->outlet_id === $user->outlet_id;
        }

        return false;
    }
}