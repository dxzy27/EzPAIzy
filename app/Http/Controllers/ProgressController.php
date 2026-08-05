<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Topic;
use App\Models\Progress;
use App\Models\FlashcardSet;
use App\Models\FlashcardProgress;

class ProgressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display student progress page.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $selectedType = $request->query('type');
        $selectedTopic = $request->query('topic');

        // Fetch Quiz Progress
        $quizzesProgress = $user->progress()->get();

        // Fetch Flashcard Progress
        $attemptedSetIds = FlashcardProgress::where('user_id', $user->id)
            ->join('flashcards', 'flashcard_progress.flashcard_id', '=', 'flashcards.id')
            ->pluck('flashcard_set_id')
            ->unique();
        $flashcardSets = FlashcardSet::whereIn('id', $attemptedSetIds)->with(['user', 'flashcards'])->get();

        // Get class teacher
        $classTeacher = User::where('role', 'teacher')->where('class_name', $user->class_name)->first();
        $teacherName = $classTeacher ? $classTeacher->name : 'Unknown';

        $unified = collect();

        // Add Quiz Progress
        foreach ($quizzesProgress as $qp) {
            // Apply topic filter
            if ($selectedTopic && $qp->topic !== $selectedTopic) {
                continue;
            }
            // Apply type filter
            if ($selectedType && $selectedType !== 'quiz') {
                continue;
            }

            $unified->push((object)[
                'id' => $qp->id,
                'type' => 'Quiz',
                'topic' => $qp->topic ?? 'General',
                'title' => !empty($qp->title) ? $qp->title : ($qp->topic ?? 'General'),
                'teacher' => $teacherName,
                'date' => $qp->updated_at,
                'status' => $qp->status, 
                'score' => (($qp->difficulty === 'hard' || $qp->difficulty === 'medium') && $qp->status === 'pending') ? 'Pending' : $qp->score . '%',
                'score_num' => $qp->score,
                'difficulty' => $qp->difficulty ?? 'easy',
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
            $progressRecords = FlashcardProgress::where('user_id', $user->id)
                ->whereIn('flashcard_id', $cardIds)
                ->get();
                
            $mastered = $progressRecords->where('status', 'mastered')->count();
            $review = $progressRecords->where('status', 'review')->count();
            $learning = $progressRecords->where('status', 'learning')->count();
            
            $latestProgress = $progressRecords->sortByDesc('updated_at')->first();
            $date = $latestProgress ? $latestProgress->updated_at : $set->updated_at;
            
            $masteredCount = $mastered + $review;
            $percentage = $total > 0 ? round(($masteredCount / $total) * 100) : 0;
            
            $status = 'Not Started';
            if ($masteredCount === $total && $total > 0) {
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
                'score' => $masteredCount . '/' . $total . ' Mastered (' . $percentage . '%)',
                'score_num' => $percentage,
                'difficulty' => 'N/A',
                'raw_progress' => null
            ]);
        }

        // Sort by date descending
        $unified = $unified->sortByDesc('date');

        // Extract unique topics for filter dropdown
        $teacherIds = User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->pluck('id')
            ->toArray();
        $topics = Topic::whereIn('user_id', $teacherIds)->pluck('name')->unique()->sort()->values();

        // Paginate collection manually
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 10;
        $sliced = $unified->slice(($page - 1) * $perPage, $perPage)->all();
        $progress = new \Illuminate\Pagination\LengthAwarePaginator(
            $sliced, 
            $unified->count(), 
            $perPage, 
            $page, 
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        // Calculate statistics on the entire unified list of quizzes (not just the paginated page)
        $quizzesOnly = $unified->filter(function($p) {
            return $p->type === 'Quiz';
        });
        $totalQuizzes = $quizzesOnly->count();
        
        $gradedQuizzes = $quizzesOnly->filter(function($p) {
            return ($p->difficulty !== 'hard' && $p->difficulty !== 'medium') || ($p->status === 'graded');
        });
        $averageScore = $gradedQuizzes->count() > 0 ? round($gradedQuizzes->avg('score_num'), 1) : 0;
        $highestScore = $gradedQuizzes->count() > 0 ? $gradedQuizzes->max('score_num') : 0;

        return view('student.progress', compact(
            'progress', 
            'topics', 
            'selectedType', 
            'selectedTopic',
            'totalQuizzes',
            'averageScore',
            'highestScore',
            'unified'
        ));
    }
}
