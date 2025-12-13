<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Payment;

class PaymentPolicy
{
    /**
     * Determine if user can view any payments
     */
    public function viewAny(User $user): bool
    {
        // Owner, Admin, dan Staff bisa lihat daftar payments
        return $user->isOwner() || $user->isAdmin() || $user->isStaff();
    }

    /**
     * Determine if user can view the payment
     */
    public function view(User $user, Payment $payment): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        // Admin dan Staff can only view payments from their outlet
        if ($user->isAdmin() || $user->isStaff()) {
            // Jika payment punya outlet_id langsung
            if (isset($payment->outlet_id)) {
                return $payment->outlet_id === $user->outlet_id;
            }

            // Jika payment punya relasi ke order
            if (isset($payment->order)) {
                return $payment->order->outlet_id === $user->outlet_id;
            }
        }

        return false;
    }

    /**
     * Determine if user can create payments
     */
    public function create(User $user): bool
    {
        // Owner, Admin, dan Staff bisa create payment
        return $user->isOwner() || $user->isAdmin() || $user->isStaff();
    }

    /**
     * Determine if user can update the payment
     */
    public function update(User $user, Payment $payment): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        // Hanya Admin yang bisa update, Staff TIDAK BISA
        if ($user->isAdmin()) {
            if (isset($payment->outlet_id)) {
                return $payment->outlet_id === $user->outlet_id;
            }
            if (isset($payment->order)) {
                return $payment->order->outlet_id === $user->outlet_id;
            }
        }

        return false;
    }

    /**
     * Determine if user can delete the payment
     */
    public function delete(User $user, Payment $payment): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        // Hanya Admin yang bisa delete, Staff TIDAK BISA
        if ($user->isAdmin()) {
            if (isset($payment->outlet_id)) {
                return $payment->outlet_id === $user->outlet_id;
            }
            if (isset($payment->order)) {
                return $payment->order->outlet_id === $user->outlet_id;
            }
        }

        return false;
    }

    /**
     * Determine if user can restore the payment
     */
    public function restore(User $user, Payment $payment): bool
    {
        return $user->isOwner();
    }

    /**
     * Determine if user can permanently delete the payment
     */
    public function forceDelete(User $user, Payment $payment): bool
    {
        return $user->isOwner();
    }

    /**
     * Determine if user can verify/confirm the payment
     */
    public function verify(User $user, Payment $payment): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        // Hanya Admin yang bisa verify, Staff TIDAK BISA
        if ($user->isAdmin()) {
            if (isset($payment->outlet_id)) {
                return $payment->outlet_id === $user->outlet_id;
            }
            if (isset($payment->order)) {
                return $payment->order->outlet_id === $user->outlet_id;
            }
        }

        return false;
    }
}