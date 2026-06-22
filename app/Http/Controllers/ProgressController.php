<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display student progress page.
     */
    public function index()
    {
        $user = auth()->user();
        $progress = $user->progress()->with(['quiz.teacher', 'quiz.questions'])->latest()->paginate(10);

        return view('student.progress', compact('progress'));
    }
}
