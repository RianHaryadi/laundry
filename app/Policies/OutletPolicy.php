<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Outlet;

class OutletPolicy
{
    /**
     * Determine if user can view any outlets
     */
    public function viewAny(User $user): bool
    {
        return true; // Semua user yang login bisa lihat daftar outlet
    }

    /**
     * Determine if user can view the outlet
     */
    public function view(User $user, Outlet $outlet): bool
    {
        // Owner bisa lihat semua outlet
        if ($user->isOwner()) {
            return true;
        }
        
        // Admin, Staff, Courier hanya bisa lihat outlet mereka sendiri
        return $user->outlet_id === $outlet->id;
    }

    /**
     * Determine if user can create outlets
     */
    public function create(User $user): bool
    {
        // Hanya Owner yang bisa buat outlet baru
        return $user->isOwner();
    }

    /**
     * Determine if user can update the outlet
     */
    public function update(User $user, Outlet $outlet): bool
    {
        // Hanya Owner yang bisa update outlet
        return $user->isOwner();
    }

    /**
     * Determine if user can delete the outlet
     */
    public function delete(User $user, Outlet $outlet): bool
    {
        // Hanya Owner yang bisa delete outlet
        return $user->isOwner();
    }

    /**
     * Determine if user can restore the outlet
     */
    public function restore(User $user, Outlet $outlet): bool
    {
        return $user->isOwner();
    }

    /**
     * Determine if user can permanently delete the outlet
     */
    public function forceDelete(User $user, Outlet $outlet): bool
    {
        return $user->isOwner();
    }
}