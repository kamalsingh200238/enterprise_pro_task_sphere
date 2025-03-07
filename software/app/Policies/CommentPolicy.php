<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Check if user can delete comment. (admin)
     */
    public function delete(User $user): bool
    {
        return $user->hasRole(UserRole::Admin);
    }
}
