<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'owner';
    }

    public function view(User $user, User $model): bool
    {
        return $user->role === 'owner';
    }

    public function create(User $user): bool
    {
        return $user->role === 'owner';
    }

    public function update(User $user, User $model): bool
    {
        return $user->role === 'owner';
    }

    public function delete(User $user, User $model): bool
    {
        // Owner cannot delete themselves
        if ($user->id === $model->id) {
            return false;
        }
        
        return $user->role === 'owner';
    }
}