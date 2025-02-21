<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\UserRole;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        if (! $task->is_private) {
            return true;
        }

        return $user->hasRole([UserRole::ADMIN, UserRole::SUPERVISOR]) ||
            $task->assignees->contains($user->id) ||
            $task->viewers->contains($user->id);
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
    public function updateStatusAndPriority(User $user, Task $task)
    {
        return $user->hasRole([UserRole::ADMIN, UserRole::SUPERVISOR]) ||
            $task->assignees->contains($user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $user->hasRole(UserRole::ADMIN);
    }
}
