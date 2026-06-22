<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Content;

class ContentPolicy
{
    /**
     * Determine whether the user can view the content.
     */
    public function view(User $user, Content $content): bool
    {
        return $user->id === $content->teacher_id;
    }

    /**
     * Determine whether the user can update the content.
     */
    public function update(User $user, Content $content): bool
    {
        return $user->id === $content->teacher_id;
    }

    /**
     * Determine whether the user can delete the content.
     */
    public function delete(User $user, Content $content): bool
    {
        return $user->id === $content->teacher_id;
    }
}
