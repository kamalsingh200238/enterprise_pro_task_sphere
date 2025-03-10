<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Check if user can view all user page
     */
    public function viewAll(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    /**
     * Check if user can view the user
     */
    public function view(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    /**
     * Check if user can create a user
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    /**
     * Check if user can edit a user
     */
    public function edit(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    /**
     * Check if user can delete a user
     */
    public function delete(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }
}
