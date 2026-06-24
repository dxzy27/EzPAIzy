<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Quiz;
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
        $quizzesProgress = $user->progress()->with(['quiz.teacher', 'quiz.questions'])->get();

        // Fetch Flashcard Progress
        $attemptedSetIds = FlashcardProgress::where('user_id', $user->id)
            ->join('flashcards', 'flashcard_progress.flashcard_id', '=', 'flashcards.id')
            ->pluck('flashcard_set_id')
            ->unique();
        $flashcardSets = FlashcardSet::whereIn('id', $attemptedSetIds)->with(['user', 'flashcards'])->get();

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
            $progressRecords = FlashcardProgress::where('user_id', $user->id)
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
            return $p->difficulty !== 'hard';
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
            'highestScore'
        ));
    }
}
