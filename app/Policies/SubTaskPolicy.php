<?php

namespace App\Policies;

use App\Models\SubTask;
use App\Models\User;
use App\UserRole;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubTaskPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SubTask $subTask): bool
    {
        if (! $subTask->is_private) {
            return true;
        }

        return $user->hasRole([UserRole::ADMIN, UserRole::SUPERVISOR]) ||
            $subTask->assignees->contains($user->id) ||
            $subTask->viewers->contains($user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole([UserRole::ADMIN, UserRole::SUPERVISOR]);
    }

    /**
     * Determine whether the user can edit the model.
     */
    public function edit(User $user): bool
    {
        return $user->hasRole([UserRole::ADMIN, UserRole::SUPERVISOR]);
    }

    /**
     * Determine whether the user can update the status and priority.
     */
    public function updateStatusAndPriority(User $user, SubTask $subTask)
    {
        return $user->hasRole([UserRole::ADMIN, UserRole::SUPERVISOR]) ||
            $subTask->assignees->contains($user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }
}
