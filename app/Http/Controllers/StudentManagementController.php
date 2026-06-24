<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class StudentManagementController extends Controller
{
    /**
     * List all students with their progress
     */
    public function index()
    {
        $teacher = auth()->user();
        
        $query = User::where('role', 'student');
        
        // If teacher is assigned to a specific class, strictly filter students
        if (!empty($teacher->class_name)) {
            $query->where('class_name', $teacher->class_name);
        }
        
        $students = $query->paginate(10);
        
        return view('teacher.students.index', compact('students', 'teacher'));
    }

    /**
     * Show form to create new student
     */
    public function create()
    {
        return view('teacher.students.create');
    }

    /**
     * Store new student
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'class_name' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['role'] = 'student';
        $student = User::create($validated);

        return redirect()->route('teacher.students.show', $student)
            ->with('success', 'Student added successfully!');
    }

    /**
     * Show student details and progress
     */
    public function show(Request $request, User $student)
    {
        abort_if($student->role !== 'student', 403, 'This user is not a student');

        $teacher = auth()->user();
        $selectedType = $request->query('type');
        $selectedTopic = $request->query('topic');

        // Fetch Quiz Progress
        $quizzesProgress = $student->progress()->with(['quiz.teacher', 'quiz.questions'])->get();

        // Fetch Flashcard Progress
        $attemptedSetIds = \App\Models\FlashcardProgress::where('user_id', $student->id)
            ->join('flashcards', 'flashcard_progress.flashcard_id', '=', 'flashcards.id')
            ->pluck('flashcard_set_id')
            ->unique();
        $flashcardSets = \App\Models\FlashcardSet::whereIn('id', $attemptedSetIds)->with(['user', 'flashcards'])->get();

        $unified = collect();

        // Add Quiz Progress
        foreach ($quizzesProgress as $qp) {
            if (!$qp->quiz) continue;
            
            // Apply topic filter
            if ($selectedTopic && $qp->quiz->topic !== $selectedTopic) {
                continue;
            }
            // Apply type filter
            if ($selectedType && $selectedType !== 'quiz') {
                continue;
            }

            $unified->push((object)[
                'id' => $qp->id,
                'type' => 'Quiz',
                'topic' => $qp->quiz->topic ?? 'General',
                'title' => $qp->quiz->title ?? 'Deleted Quiz',
                'teacher' => $qp->quiz->teacher->name ?? 'Unknown',
                'date' => $qp->updated_at,
                'status' => $qp->status, 
                'score' => $qp->quiz->difficulty === 'hard' && $qp->status === 'pending' ? 'Pending' : $qp->score . '%',
                'score_num' => $qp->score,
                'difficulty' => $qp->quiz->difficulty ?? 'easy',
                'raw_progress' => $qp
            ]);
        }

        // Add Flashcard Progress
        foreach ($flashcardSets as $set) {
            // Apply topic filter
            if ($selectedTopic && $set->topic !== $selectedTopic) {
                continue;
            }
            // Apply type filter
            if ($selectedType && $selectedType !== 'flashcards') {
                continue;
            }

            $total = $set->flashcards->count();
            $cardIds = $set->flashcards->pluck('id')->toArray();
            $progressRecords = \App\Models\FlashcardProgress::where('user_id', $student->id)
                ->whereIn('flashcard_id', $cardIds)
                ->get();
                
            $mastered = $progressRecords->where('status', 'mastered')->count();
            $review = $progressRecords->where('status', 'review')->count();
            $learning = $progressRecords->where('status', 'learning')->count();
            
            $latestProgress = $progressRecords->sortByDesc('updated_at')->first();
            $date = $latestProgress ? $latestProgress->updated_at : $set->updated_at;
            
            $percentage = $total > 0 ? round(($mastered / $total) * 100) : 0;
            
            $status = 'Not Started';
            if ($mastered === $total && $total > 0) {
                $status = 'Mastered';
            } elseif ($progressRecords->count() > 0) {
                $status = 'Learning';
            }

            $unified->push((object)[
                'id' => $set->id,
                'type' => 'Flashcards',
                'topic' => $set->topic ?? 'General',
                'title' => $set->title,
                'teacher' => $set->user->name ?? 'Unknown',
                'date' => $date,
                'status' => $status,
                'score' => $mastered . '/' . $total . ' Mastered (' . $percentage . '%)',
                'score_num' => $percentage,
                'difficulty' => 'N/A',
                'raw_progress' => null
            ]);
        }

        // Sort by date descending
        $unified = $unified->sortByDesc('date');

        // Extract unique topics for filter dropdown
        $topics = \App\Models\Topic::where('user_id', $teacher->id)->pluck('name')->unique()->sort()->values();

        // Paginate collection manually
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 5;
        $sliced = $unified->slice(($page - 1) * $perPage, $perPage)->all();
        $progress = new \Illuminate\Pagination\LengthAwarePaginator(
            $sliced, 
            $unified->count(), 
            $perPage, 
            $page, 
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('teacher.students.show', compact('student', 'teacher', 'progress', 'topics', 'selectedType', 'selectedTopic'));
    }

    /**
     * Show form to edit student
     */
    public function edit(User $student)
    {
        abort_if($student->role !== 'student', 403, 'This user is not a student');

        return view('teacher.students.edit', compact('student'));
    }

    /**
     * Update student
     */
    public function update(Request $request, User $student)
    {
        abort_if($student->role !== 'student', 403, 'This user is not a student');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $student->id,
            'phone' => 'nullable|string|max:20',
            'class_name' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        $student->update($validated);

        return redirect()->route('teacher.students.index')
            ->with('success', 'Student updated successfully!');
    }

    /**
     * Delete student
     */
    public function destroy(User $student)
    {
        abort_if($student->role !== 'student', 403, 'This user is not a student');

        $studentName = $student->name;
        $student->delete();

        return redirect()->route('teacher.students.index')
            ->with('success', "Student '$studentName' deleted successfully!");
    }

    /**
     * Grade a KBAT quiz
     */
    public function grade(Request $request, \App\Models\Progress $progress)
    {
        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'overall_comment' => 'nullable|string|max:1000',
        ]);

        $progress->score = $validated['score'];
        $progress->status = 'graded';
        
        $notes = is_array($progress->teacher_notes) ? $progress->teacher_notes : [];
        $notes['overall_comment'] = $validated['overall_comment'];
        $progress->teacher_notes = $notes;
        
        $progress->save();

        return redirect()->back()->with('success', 'Quiz graded successfully!');
    }
}
