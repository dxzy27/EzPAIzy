<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Quiz;

class QuizPolicy
{
    /**
     * Determine whether the user can view the quiz.
     */
    public function view(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->teacher_id;
    }

    /**
     * Determine whether the user can update the quiz.
     */
    public function update(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->teacher_id;
    }

    /**
     * Determine whether the user can delete the quiz.
     */
    public function delete(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->teacher_id;
    }
}
