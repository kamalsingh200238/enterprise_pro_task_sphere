<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Check if user can view all the tasks. (admin, supervisor)
     */
    public function viewAll(User $user): bool
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]);
    }

    /**
     * Check if user can view the task. (admin, supervisor, assignee)
     */
    public function view(User $user, Task $task): bool
    {
        if (! $task->is_private) {
            return true;
        }

        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]) ||
            $task->assignees->contains($user->id) ||
            $task->viewers->contains($user->id);
    }

    /**
     * Check if user can create task. (admin, supervisor)
     */
    public function create(User $user): bool
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]);
    }

    /**
     * Check if user can edit task. (admin, supervisor)
     */
    public function edit(User $user): bool
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]);
    }

    /**
     * Check if user can update status of task. (admin, supervisor, assignee)
     */
    public function updateStatus(User $user, Task $task)
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]) ||
            $task->assignees->contains($user->id);
    }

    /**
     * Check if user can update status of task to done. (admin, supervisor)
     */
    public function updateStatusToDone(User $user)
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]);
    }

    /**
     * Check if user can delete task. (admin)
     */
    public function delete(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    /**
     * Check if user can create comment in task. (admin, supervisor, assignee)
     */
    public function createComment(User $user, Task $task)
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]) ||
            $task->assignees->contains($user->id);
    }
}
