<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Service;

class ServicePolicy
{
    /**
     * Determine if user can view any services
     */
    public function viewAny(User $user): bool
    {
        // Hanya Owner dan Admin, Staff TIDAK BISA
        return $user->isOwner() || $user->isAdmin();
    }

    /**
     * Determine if user can view the service
     */
    public function view(User $user, Service $service): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        // Hanya Admin, Staff TIDAK BISA
        if ($user->isAdmin()) {
            // Jika service global (tidak punya outlet_id)
            if (!$service->outlet_id) {
                return true;
            }
            return $service->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Determine if user can create services
     */
    public function create(User $user): bool
    {
        // Hanya Owner dan Admin, Staff TIDAK BISA
        return $user->isOwner() || $user->isAdmin();
    }

    /**
     * Determine if user can update the service
     */
    public function update(User $user, Service $service): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            // Admin tidak bisa update global service
            if (!$service->outlet_id) {
                return false;
            }
            return $service->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Determine if user can delete the service
     */
    public function delete(User $user, Service $service): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            // Admin tidak bisa delete global service
            if (!$service->outlet_id) {
                return false;
            }
            return $service->outlet_id === $user->outlet_id;
        }

        return false;
    }

    /**
     * Determine if user can restore the service
     */
    public function restore(User $user, Service $service): bool
    {
        return $user->isOwner();
    }

    /**
     * Determine if user can permanently delete the service
     */
    public function forceDelete(User $user, Service $service): bool
    {
        return $user->isOwner();
    }
}