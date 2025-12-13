<?php

namespace App\Policies;

use App\Models\User;

class AuditLogPolicy
{
    /**
     * Determine if user can view any audit logs
     */
    public function viewAny(User $user): bool
    {
        // Hanya Owner yang bisa lihat audit log, Admin dan Staff TIDAK BISA
        return $user->isOwner();
    }

    /**
     * Determine if user can view the audit log
     */
    public function view(User $user, $auditLog): bool
    {
        // Hanya Owner yang bisa lihat audit log
        return $user->isOwner();
    }
}