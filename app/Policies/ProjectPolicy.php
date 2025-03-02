<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view all the projects.
     */
    public function viewAll(User $user): bool
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]);
    }

    /**
     * Determine whether the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        if (! $project->is_private) {
            return true;
        }

        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]) ||
            $project->assignees->contains($user->id) ||
            $project->viewers->contains($user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]);
    }

    /**
     * Determine whether the user can edit the model.
     */
    public function edit(User $user): bool
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]);
    }

    /**
     * Determine whether the user can update the status and priority.
     */
    public function updateStatus(User $user, Project $project)
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]) ||
            $project->assignees->contains($user->id);
    }

    /**
     * Determine whether the user can update the status and priority.
     */
    public function updateStatusToDone(User $user)
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }
}
