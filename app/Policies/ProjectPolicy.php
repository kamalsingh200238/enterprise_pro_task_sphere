<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Check if user can view all the projects. (admin, supervisor)
     */
    public function viewAll(User $user): bool
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]);
    }

    /**
     * Check if user can view the project. (admin, supervisor, assignee)
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
     * Check if user can create project. (admin, supervisor)
     */
    public function create(User $user): bool
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]);
    }

    /**
     * Check if user can edit project. (admin, supervisor)
     */
    public function edit(User $user): bool
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]);
    }

    /**
     * Check if user can update status of project. (admin, supervisor, assignee)
     */
    public function updateStatus(User $user, Project $project)
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]) ||
            $project->assignees->contains($user->id);
    }

    /**
     * Check if user can update status of project to done. (admin, supervisor)
     */
    public function updateStatusToDone(User $user)
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]);
    }

    /**
     * Check if user can delete project. (admin)
     */
    public function delete(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }

    /**
     * Check if user can create comment in project. (admin, supervisor, assignee)
     */
    public function createComment(User $user, Project $project)
    {
        return $user->hasRole([UserRole::Admin, UserRole::Supervisor]) ||
            $project->assignees->contains($user->id);
    }
}
