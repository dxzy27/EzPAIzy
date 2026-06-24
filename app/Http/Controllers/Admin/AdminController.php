<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Content;
use App\Models\FlashcardSet;
use App\Models\Quiz;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function dashboard()
    {
        $stats = [
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_students' => User::where('role', 'student')->count(),
            'suspended_users' => User::where('is_suspended', true)->count(),
            'flagged_contents' => Content::where('is_flagged', true)->count(),
            'flagged_flashcards' => FlashcardSet::where('is_flagged', true)->count(),
            'flagged_quizzes' => Quiz::where('is_flagged', true)->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
