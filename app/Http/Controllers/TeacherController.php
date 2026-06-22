<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * Show the teacher dashboard.
     */
    public function dashboard()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $quizzes = $user->quizzes()->orderBy('updated_at', 'desc')->take(5)->get();
        
        $contents = $user->contents()->get();
        $flashcardSets = $user->flashcardSets()->get();
        
        // Merge contents and flashcardSets for recent list
        $recentContents = $contents->concat($flashcardSets)->sortByDesc('updated_at')->take(5);
        
        // Total count including flashcard sets
        $totalContentsCount = $contents->count() + $flashcardSets->count();

        // Count students (mocked/placeholder logic usually, assuming 'students' is managed differently or count is passed separately?)
        // The original code passed 'quizzes' and 'contents' collections. 
        // I should stick to passing what the view needs.
        // View likely does count($contents).
        // I will pass 'recentContents' and 'totalContentsCount'.
        
        // Count students
        $studentsQuery = \App\Models\User::where('role', 'student');
        
        // If teacher is assigned to a specific class, strictly filter students count
        if (!empty($user->class_name)) {
            $studentsQuery->where('class_name', $user->class_name);
        }
        
        $studentsCount = $studentsQuery->count();
        
        return view('teacher.dashboard', [
            'quizzes' => $quizzes,
            'recentContents' => $recentContents,
            'totalContentsCount' => $totalContentsCount,
            'quizzesCount' => $user->quizzes()->count(),
            'studentsCount' => $studentsCount,
        ]);
    }
}
