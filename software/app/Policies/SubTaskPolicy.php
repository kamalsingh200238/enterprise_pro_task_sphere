<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\SubTask;
use App\Models\User;

class SubTaskPolicy
{
    /**
     * Check if user can view all the sub-tasks. (admin, supervisor)
     */
    public function viewAll(User $user): bool
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]);
    }

    /**
     * Check if user can view the sub-task. (admin, supervisor, assignee)
     */
    public function view(User $user, SubTask $subTask): bool
    {
        if (! $subTask->is_private) {
            return true;
        }

        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]) ||
            $subTask->assignees->contains($user->id) ||
            $subTask->viewers->contains($user->id);
    }

    /**
     * Check if user can create sub-task. (admin, supervisor)
     */
    public function create(User $user): bool
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]);
    }

    /**
     * Check if user can edit sub-task. (admin, supervisor)
     */
    public function edit(User $user): bool
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]);
    }

    /**
     * Check if user can update status of sub-task. (admin, supervisor, assignee)
     */
    public function updateStatus(User $user, SubTask $subTask)
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]) ||
            $subTask->assignees->contains($user->id);
    }

    /**
     * Check if user can update status of sub-task to done. (admin, supervisor)
     */
    public function updateStatusToDone(User $user)
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]);
    }

    /**
     * Check if user can delete sub-task. (admin)
     */
    public function delete(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    /**
     * Check if user can create comment in sub-task. (admin, supervisor, assignee)
     */
    public function createComment(User $user, SubTask $subTask)
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]) ||
            $subTask->assignees->contains($user->id);
    }
}
