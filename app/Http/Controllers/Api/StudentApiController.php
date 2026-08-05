<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Favorite;
use App\Models\FlashcardSet;
use App\Models\Flashcard;
use App\Models\FlashcardProgress;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Models\LearningProfile;

class StudentApiController extends Controller
{
    /**
     * Login and return a Sanctum token.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        $user = Auth::user();

        if ($user->role !== 'student') {
            Auth::logout();
            return response()->json(['message' => 'Access denied. Students only.'], 403);
        }

        // Revoke old tokens and create a fresh one
        $user->tokens()->delete();
        $token = $user->createToken('ezpaizy-mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user->only(['id', 'name', 'email', 'role']),
        ]);
    }

    /**
     * Logout — revoke token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Register a new student user.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone_number' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'class_name' => ['required', 'string', 'exists:school_classes,name'],
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role' => 'student',
            'phone_number' => $validated['phone_number'],
            'address' => $validated['address'],
            'class_name' => $validated['class_name'],
            'is_approved' => true, // Students do not require admin approval
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully! You can now log in.',
            'user' => $user->only(['id', 'name', 'email', 'role']),
        ]);
    }

    /**
     * Dashboard stats.
     */
    public function dashboard(Request $request)
    {
        $user     = $request->user();
        $progress = $user->progress()->latest()->get();
        $profile  = LearningProfile::where('user_id', $user->id)->first();
        $style    = $user->learning_style;

        $teacherIds = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->pluck('id')
            ->toArray();

        $classTeacher = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->first();
        $teacherName = $classTeacher ? $classTeacher->name : 'Teacher';

        $mappedProgress = $progress->map(function ($p) use ($teacherName, $classTeacher) {
            return [
                'id'              => $p->id,
                'student_id'      => $p->student_id,
                'topic'           => $p->topic,
                'difficulty'      => $p->difficulty,
                'score'           => $p->score,
                'status'          => $p->status,
                'student_answers' => $p->student_answers,
                'teacher_notes'   => $p->teacher_notes,
                'created_at'      => $p->created_at->toIso8601String(),
                'updated_at'      => $p->updated_at->toIso8601String(),
                'quiz'            => [
                    'id'         => $p->id,
                    'title'      => ($p->topic ?? 'General') . ' (' . ucfirst($p->difficulty ?? 'easy') . ')',
                    'difficulty' => $p->difficulty ?? 'easy',
                    'teacher'    => [
                        'id'   => $classTeacher?->id,
                        'name' => $teacherName,
                    ],
                ]
            ];
        });

        $newMaterials = [];
        $recentContents   = Content::where('is_flagged', false)->whereIn('teacher_id', $teacherIds)->latest()->take(5)->get();
        $recentFlashcards = FlashcardSet::where('is_flagged', false)->whereIn('user_id', $teacherIds)->latest()->take(5)->get();

        $mappedContents = $recentContents->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'topic' => $item->topic ?? 'General',
                'type' => 'Content',
                'action' => 'View',
                'created_at' => $item->created_at->toIso8601String(),
            ];
        });

        $mappedFlashcards = $recentFlashcards->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'topic' => $item->topic ?? 'General',
                'type' => 'Flashcard',
                'action' => 'Practice',
                'created_at' => $item->created_at->toIso8601String(),
            ];
        });

        if ($style === 'read_write') {
            $newMaterials = $mappedFlashcards->concat($mappedContents)->sortByDesc('created_at')->take(5)->values()->all();
        } else {
            $newMaterials = $mappedContents->concat($mappedFlashcards)->sortByDesc('created_at')->take(5)->values()->all();
        }

        return response()->json([
            'user'             => $user->only(['id', 'name', 'email', 'learning_style', 'class_name']),
            'persona'          => $profile?->persona,
            'profile'          => $profile,
            'quiz_count'       => \App\Models\Question::select('topic', 'difficulty')->groupBy('topic', 'difficulty')->get()->count(),
            'materials_count'  => Content::where('is_flagged', false)->whereIn('teacher_id', $teacherIds)->count() + FlashcardSet::where('is_flagged', false)->whereIn('user_id', $teacherIds)->count(),
            'completed_count'  => $progress->count(),
            'best_score'       => ($style === 'competitive' && $progress->count() > 0) ? $progress->max('score') : null,
            'recent_results'   => $mappedProgress->take(5)->values(),
            'new_materials'    => $newMaterials,
            'leaderboard'      => [],
        ]);
    }

    /**
     * All quizzes generated dynamically from questions and progress.
     */
    public function quizzes(Request $request)
    {
        $user = $request->user();
        $teacherIds = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->pluck('id')
            ->toArray();

        $topics = \App\Models\Topic::where('type', 'quiz')
            ->whereIn('user_id', $teacherIds)
            ->pluck('name')
            ->toArray();

        $allQuizzes = collect();

        foreach ($topics as $topic) {
            $difficulties = \App\Models\Question::where('topic', $topic)
                ->select('difficulty')
                ->distinct()
                ->pluck('difficulty')
                ->toArray();

            $pDiffs = \App\Models\Progress::where('student_id', $user->id)
                ->where('topic', $topic)
                ->select('difficulty')
                ->distinct()
                ->pluck('difficulty')
                ->toArray();

            $difficulties = array_values(array_unique(array_filter(array_merge($difficulties, $pDiffs))));
            $progressRecords = \App\Models\Progress::where('student_id', $user->id)
                ->where('topic', $topic)
                ->get();

            $classTeacher = \App\Models\User::where('role', 'teacher')
                ->where('class_name', $user->class_name)
                ->first();

            foreach ($difficulties as $diff) {
                $quizIdStr = $topic . '_' . $diff;
                $quizIdInt = crc32($quizIdStr) & 0x7FFFFFFF;

                $qCount = \App\Models\Question::where('topic', $topic)
                    ->where('difficulty', $diff)
                    ->count();

                $quizProgress = $progressRecords->where('difficulty', $diff)->values()->toArray();

                $quiz = [
                    'id' => $quizIdInt,
                    'topic' => $topic,
                    'difficulty' => $diff,
                    'title' => $topic . ' (' . ucfirst($diff) . ')',
                    'questions_count' => $qCount,
                    'teacher' => $classTeacher ? [
                        'id' => $classTeacher->id,
                        'name' => $classTeacher->name,
                    ] : null,
                    'progress' => $quizProgress,
                    'is_locked' => false,
                ];

                $allQuizzes->push($quiz);
            }
        }
        return response()->json($allQuizzes);
    }

    /**
     * Get all folder topics dynamically by type (matches 'quiz', 'material', or 'flashcard').
     */
    public function getTopicsByType(Request $request, $type)
    {
        $user = $request->user();
        $teacherIds = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->pluck('id')
            ->toArray();

        $topics = \App\Models\Topic::where('type', $type)
            ->whereIn('user_id', $teacherIds)
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();

        return response()->json($topics);
    }

    /**
     * Single quiz detail lookup via dynamically computed CRC32 ID.
     */
    public function quizDetail(Request $request, $id)
    {
        $user = $request->user();
        $teacherIds = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->pluck('id')
            ->toArray();

        $topics = \App\Models\Topic::where('type', 'quiz')
            ->whereIn('user_id', $teacherIds)
            ->pluck('name')
            ->toArray();

        $matchedTopic = null;
        $matchedDifficulty = null;

        foreach ($topics as $topic) {
            $difficulties = \App\Models\Question::where('topic', $topic)
                ->select('difficulty')
                ->distinct()
                ->pluck('difficulty')
                ->toArray();

            $pDiffs = \App\Models\Progress::where('student_id', $user->id)
                ->where('topic', $topic)
                ->select('difficulty')
                ->distinct()
                ->pluck('difficulty')
                ->toArray();

            $difficulties = array_values(array_unique(array_filter(array_merge($difficulties, $pDiffs))));

            foreach ($difficulties as $diff) {
                $quizIdStr = $topic . '_' . $diff;
                $quizIdInt = crc32($quizIdStr) & 0x7FFFFFFF;

                if ($quizIdInt == $id) {
                    $matchedTopic = $topic;
                    $matchedDifficulty = $diff;
                    break 2;
                }
            }
        }

        if (!$matchedTopic || !$matchedDifficulty) {
            return response()->json(['error' => 'Quiz not found'], 404);
        }

        $questions = \App\Models\Question::where('topic', $matchedTopic)
            ->where('difficulty', $matchedDifficulty)
            ->get();

        $classTeacher = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->first();

        $quizProgress = \App\Models\Progress::where('student_id', $user->id)
            ->where('topic', $matchedTopic)
            ->where('difficulty', $matchedDifficulty)
            ->get();

        return response()->json([
            'id' => intval($id),
            'topic' => $matchedTopic,
            'difficulty' => $matchedDifficulty,
            'title' => $matchedTopic . ' (' . ucfirst($matchedDifficulty) . ')',
            'questions' => $questions,
            'teacher' => $classTeacher ? [
                'id' => $classTeacher->id,
                'name' => $classTeacher->name,
            ] : null,
            'progress' => $quizProgress,
        ]);
    }

    /**
     * Submit quiz answers — dynamically graded and saved.
     */
    public function submitQuiz(Request $request, $id)
    {
        $user = $request->user();
        $answers = $request->input('answers', []);
        
        $teacherIds = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->pluck('id')
            ->toArray();

        $topics = \App\Models\Topic::where('type', 'quiz')
            ->whereIn('user_id', $teacherIds)
            ->pluck('name')
            ->toArray();

        $matchedTopic = null;
        $matchedDifficulty = null;

        foreach ($topics as $topic) {
            $difficulties = \App\Models\Question::where('topic', $topic)
                ->select('difficulty')
                ->distinct()
                ->pluck('difficulty')
                ->toArray();

            $pDiffs = \App\Models\Progress::where('student_id', $user->id)
                ->where('topic', $topic)
                ->select('difficulty')
                ->distinct()
                ->pluck('difficulty')
                ->toArray();

            $difficulties = array_values(array_unique(array_filter(array_merge($difficulties, $pDiffs))));

            foreach ($difficulties as $diff) {
                $quizIdStr = $topic . '_' . $diff;
                $quizIdInt = crc32($quizIdStr) & 0x7FFFFFFF;

                if ($quizIdInt == $id) {
                    $matchedTopic = $topic;
                    $matchedDifficulty = $diff;
                    break 2;
                }
            }
        }

        if (!$matchedTopic || !$matchedDifficulty) {
            return response()->json(['error' => 'Quiz not found'], 404);
        }

        $questions = \App\Models\Question::where('topic', $matchedTopic)
            ->where('difficulty', $matchedDifficulty)
            ->get();

        $correct = 0;
        foreach ($questions as $i => $q) {
            $key = (string) $i;
            if (isset($answers[$key]) && $answers[$key] === $q->correct_answer) {
                $correct++;
            }
        }

        $score = $questions->count() > 0
            ? (int) round(($correct / $questions->count()) * 100)
            : 0;
            
        $status = ($matchedDifficulty === 'hard' || $matchedDifficulty === 'medium') ? 'pending' : 'completed';

        $progress = \App\Models\Progress::updateOrCreate(
            [
                'student_id' => $user->id,
                'topic' => $matchedTopic,
                'difficulty' => $matchedDifficulty,
            ],
            [
                'score' => $score,
                'student_answers' => $answers,
                'status' => $status,
            ]
        );

        return response()->json([
            'score' => $score,
            'status' => $status,
            'progress' => $progress,
        ]);
    }

    /**
     * All learning materials (with teacher + favorite flag).
     */
    public function contents(Request $request)
    {
        $user             = $request->user();
        $teacherIds = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->pluck('id')
            ->toArray();

        $favoritedIds     = Favorite::where('student_id', $user->id)
            ->whereNotNull('content_id')
            ->pluck('content_id')
            ->toArray();

        $contents = Content::where('is_flagged', false)
            ->whereIn('teacher_id', $teacherIds)
            ->with('teacher')
            ->latest()
            ->get()
            ->map(function ($c) use ($favoritedIds) {
                $c->is_favorited = in_array($c->id, $favoritedIds);
                return $c;
            });

        return response()->json($contents);
    }

    /**
     * Single content item.
     */
    public function contentDetail(Content $content)
    {
        return response()->json($content->load('teacher'));
    }

    /**
     * All flashcard sets (with cards + favorite flag).
     */
    public function flashcards(Request $request)
    {
        $user             = $request->user();
        $teacherIds = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->pluck('id')
            ->toArray();

        $favoritedIds     = Favorite::where('student_id', $user->id)
            ->whereNotNull('flashcard_set_id')
            ->pluck('flashcard_set_id')
            ->toArray();

        $sets = FlashcardSet::where('is_flagged', false)
            ->whereIn('user_id', $teacherIds)
            ->with('flashcards')
            ->latest()
            ->get()
            ->map(function ($set) use ($user, $favoritedIds) {
                $set->is_favorited = in_array($set->id, $favoritedIds);

                $total = $set->flashcards->count();
                $cardIds = $set->flashcards->pluck('id')->toArray();
                
                $progressRecords = FlashcardProgress::where('user_id', $user->id)
                    ->whereIn('flashcard_id', $cardIds)
                    ->get();
                    
                $mastered = $progressRecords->where('status', 'mastered')->count();
                $review = $progressRecords->where('status', 'review')->count();
                $learning = $progressRecords->where('status', 'learning')->count();
                
                $recordedCount = $progressRecords->count();
                $new = $progressRecords->where('status', 'new')->count() + ($total - $recordedCount);
                
                $set->stats = [
                    'total' => $total,
                    'mastered' => $mastered,
                    'review' => $review,
                    'learning' => $learning,
                    'new' => $new,
                ];

                return $set;
            });

        return response()->json($sets);
    }



    /**
     * Single flashcard set with cards.
     */
    public function flashcardDetail(FlashcardSet $set)
    {
        return response()->json($set->load('flashcards'));
    }

    /**
     * Student progress history.
     */
    public function progress(Request $request)
    {
        $user = $request->user();
        $classTeacher = \App\Models\User::where('role', 'teacher')->where('class_name', $user->class_name)->first();
        $teacherName = $classTeacher ? $classTeacher->name : 'Teacher';

        $unified = collect();

        // 1. Fetch Quiz Progress
        $quizzesProgress = $user->progress()->latest()->get();
        foreach ($quizzesProgress as $p) {
            $unified->push([
                'id'              => $p->id,
                'type'            => 'Quiz',
                'student_id'      => $p->student_id,
                'topic'           => $p->topic ?? 'General',
                'title'           => ($p->topic ?? 'General') . ' (' . ucfirst($p->difficulty ?? 'easy') . ')',
                'difficulty'      => $p->difficulty ?? 'easy',
                'score'           => $p->score,
                'status'          => $p->status,
                'teacher'         => $teacherName,
                'student_answers' => $p->student_answers,
                'teacher_notes'   => $p->teacher_notes,
                'created_at'      => $p->created_at->toIso8601String(),
                'updated_at'      => $p->updated_at->toIso8601String(),
                'quiz'            => [
                    'id'         => $p->id,
                    'title'      => ($p->topic ?? 'General') . ' (' . ucfirst($p->difficulty ?? 'easy') . ')',
                    'difficulty' => $p->difficulty ?? 'easy',
                    'teacher'    => [
                        'id'   => $classTeacher?->id,
                        'name' => $teacherName,
                    ],
                ]
            ]);
        }

        // 2. Fetch Flashcard Progress
        $attemptedSetIds = \App\Models\FlashcardProgress::where('user_id', $user->id)
            ->join('flashcards', 'flashcard_progress.flashcard_id', '=', 'flashcards.id')
            ->pluck('flashcard_set_id')
            ->unique();

        $flashcardSets = \App\Models\FlashcardSet::whereIn('id', $attemptedSetIds)->with(['user', 'flashcards'])->get();

        foreach ($flashcardSets as $set) {
            $total = $set->flashcards->count();
            $cardIds = $set->flashcards->pluck('id')->toArray();
            $progressRecords = \App\Models\FlashcardProgress::where('user_id', $user->id)
                ->whereIn('flashcard_id', $cardIds)
                ->get();

            $mastered = $progressRecords->where('status', 'mastered')->count();
            $review = $progressRecords->where('status', 'review')->count();

            $latestProgress = $progressRecords->sortByDesc('updated_at')->first();
            $date = $latestProgress ? $latestProgress->updated_at : $set->updated_at;

            $masteredCount = $mastered + $review;
            $percentage = $total > 0 ? round(($masteredCount / $total) * 100) : 0;

            $status = 'Learning';
            if ($masteredCount === $total && $total > 0) {
                $status = 'Mastered';
            }

            $unified->push([
                'id'               => $set->id,
                'flashcard_set_id' => $set->id,
                'type'             => 'Flashcard',
                'student_id'       => $user->id,
                'topic'            => $set->topic ?? 'General',
                'title'            => $set->title ?? 'Untitled Flashcards',
                'teacher'          => $set->user?->name ?? $teacherName,
                'score'            => $percentage,
                'status'           => $status,
                'created_at'       => $date->toIso8601String(),
                'updated_at'       => $date->toIso8601String(),
            ]);
        }

        // Sort by updated_at descending
        $sorted = $unified->sortByDesc('updated_at')->values();

        return response()->json($sorted);
    }

    /**
     * Revision list (saved favorites).
     */
    public function revision(Request $request)
    {
        $favorites = Favorite::where('student_id', $request->user()->id)
            ->with(['content.teacher', 'flashcardSet'])
            ->latest()
            ->get();

        return response()->json($favorites);
    }

    /**
     * Add content to favorites.
     */
    public function addFavorite(Request $request, Content $content)
    {
        Favorite::firstOrCreate([
            'student_id' => $request->user()->id,
            'content_id' => $content->id,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Remove content from favorites.
     */
    public function removeFavorite(Request $request, Content $content)
    {
        Favorite::where('student_id', $request->user()->id)
            ->where('content_id', $content->id)
            ->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Add flashcard set to favorites.
     */
    public function addFlashcardFavorite(Request $request, FlashcardSet $flashcardSet)
    {
        Favorite::firstOrCreate([
            'student_id' => $request->user()->id,
            'flashcard_set_id' => $flashcardSet->id,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Remove flashcard set from favorites.
     */
    public function removeFlashcardFavorite(Request $request, FlashcardSet $flashcardSet)
    {
        Favorite::where('student_id', $request->user()->id)
            ->where('flashcard_set_id', $flashcardSet->id)
            ->delete();

        return response()->json(['success' => true]);
    }



    /**
     * Fetch flashcards from a set that are DUE for review.
     */
    public function studyFlashcards(Request $request, FlashcardSet $set)
    {
        $userId = $request->user()->id;
        $now = now();

        $flashcards = $set->flashcards()->get()->map(function ($card) use ($userId, $now) {
            $progress = FlashcardProgress::firstOrCreate(
                ['user_id' => $userId, 'flashcard_id' => $card->id],
                ['ease_factor' => 2.5, 'interval' => 0, 'repetitions' => 0, 'next_review_date' => $now]
            );

            $card->progress = $progress;
            $card->is_due = $progress->next_review_date <= $now;
            return $card;
        })->filter(function ($card) {
            return $card->is_due;
        })->values();

        return response()->json([
            'flashcard_set' => $set,
            'due_cards' => $flashcards
        ]);
    }

    /**
     * Submit a review rating (0-5) for a flashcard using SM-2 algorithm.
     */
    public function reviewFlashcard(Request $request, Flashcard $flashcard)
    {
        $request->validate([
            'quality' => 'required|integer|min:0|max:5',
        ]);

        $quality = $request->input('quality');
        $progress = FlashcardProgress::firstOrCreate(
            ['user_id' => $request->user()->id, 'flashcard_id' => $flashcard->id]
        );

        // SM-2 Algorithm Calculation
        $status = $progress->status ?? 'new';
        $repetitions = $progress->repetitions;
        $interval = $progress->interval;
        $easeFactor = $progress->ease_factor;

        // FSRS / Learning Steps approximation
        // If quality < 3 (Again/Hard on new card), it stays in learning.
        if ($quality < 3) {
            $status = 'learning';
            $repetitions = 0;
            $interval = 0; // Due today again
        } else {
            if ($status === 'new' || $status === 'learning') {
                if ($quality === 3) {
                    $interval = 1; // 1 day
                    $status = 'review';
                } else { // 4 or 5
                    $interval = 3; // 3 days
                    $status = 'review';
                }
                $repetitions = 1;
            } else {
                // Already in review phase
                if ($repetitions == 0) {
                    $interval = 1;
                } elseif ($repetitions == 1) {
                    $interval = 6;
                } else {
                    $interval = (int) round($interval * $easeFactor);
                }
                $repetitions++;
            }
        }

        if ($interval > 21) {
            $status = 'mastered';
        }

        $easeFactor = $easeFactor + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        if ($easeFactor < 1.3) $easeFactor = 1.3;

        $progress->update([
            'status' => $status,
            'repetitions' => $repetitions,
            'interval' => $interval,
            'ease_factor' => $easeFactor,
            'next_review_date' => $interval == 0 ? now()->addMinutes(10) : now()->addDays($interval),
        ]);

        return response()->json([
            'success' => true,
            'progress' => $progress,
        ]);
    }

    /**
     * Get Learning Profile (Diagnosis).
     */
    public function getDiagnosis(Request $request)
    {
        $profile = LearningProfile::where('user_id', $request->user()->id)->first();
        return response()->json($profile);
    }

    /**
     * Store Diagnosis result using 10-question expert system.
     */
    public function storeDiagnosis(Request $request)
    {
        $validationRules = [];
        $keys = [];
        for ($i = 1; $i <= 16; $i++) {
            $validationRules["q$i"] = 'nullable|array';
            $validationRules["q$i.*"] = 'in:A,B,C,D';
            $keys[] = "q$i";
        }
        $request->validate($validationRules);

        $answers = $request->only($keys);

        // Filter out empty arrays or nulls to store clean answers
        $answers = array_filter($answers, function($val) {
            return is_array($val) && count($val) > 0;
        });

        if (empty($answers)) {
            return response()->json(['message' => 'Please answer at least one question.'], 422);
        }

        $result = $this->runInferenceEngine($answers);
        $style  = $result['style'];
        $persona = $this->buildPersona($style, $result['confidence'], $answers);
        $recommendations = $this->generateRecommendations($style, $result, $answers);

        $profile = LearningProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'answers'           => $answers,
                'score_read_write'  => $result['scores']['read_write'] ?? 0,
                'score_auditory'    => $result['scores']['auditory'] ?? 0,
                'score_visual'      => $result['scores']['visual'] ?? 0,
                'score_kinesthetic' => $result['scores']['kinesthetic'] ?? 0,
                'confidence'        => $result['confidence'],
                'learning_style'    => $style,
                'persona'           => $persona,
                'recommendations'   => $recommendations,
            ]
        );

        // Also persist on the user record for fast access
        $request->user()->update(['learning_style' => $style]);

        return response()->json($profile);
    }

    /**
     * Reset Learning Profile (Diagnosis).
     */
    public function resetDiagnosis(Request $request)
    {
        $user = $request->user();
        $user->update(['learning_style' => null]);
        LearningProfile::where('user_id', $user->id)->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Get Quran verse by mood.
     */
    public function quranMood(Request $request)
    {
        $mood = $request->query('mood', 'happy');

        $moodMap = [
            'happy' => 6074,
            'sad' => 6072,
            'anxious' => 1735,
            'unmotivated' => 432,
            'lost' => 6076,
        ];

        $ayahId = $moodMap[$mood] ?? 6074;

        try {
            $response = Http::get("http://api.alquran.cloud/v1/ayah/{$ayahId}/editions/quran-uthmani,en.sahih,ms.basmeih");

            if ($response->successful()) {
                $data = $response->json('data');
                return response()->json([
                    'success' => true,
                    'arabic' => $data[0]['text'] ?? '',
                    'verse' => $data[1]['text'] ?? '',
                    'translation' => $data[2]['text'] ?? '',
                    'surah' => ($data[0]['surah']['englishName'] ?? '') .
                        ' (' . ($data[0]['surah']['name'] ?? '') . ')' .
                        ' — Ayah ' . ($data[0]['numberInSurah'] ?? ''),
                ]);
            }
        } catch (\Exception $e) {
            // handle error below
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to fetch verse'
        ], 500);
    }

    private function runInferenceEngine(array $answers): array
    {
        $knowledgeBase = $this->getKnowledgeBase();
        $scores = ['read_write' => 0, 'auditory' => 0, 'visual' => 0, 'kinesthetic' => 0];

        // ── PASS 1: VARK Scoring Method (Simple Selection Count) ─────────────
        foreach ($knowledgeBase as $qKey => $rule) {
            $chosenList = $answers[$qKey] ?? [];
            if (!is_array($chosenList)) {
                $chosenList = $chosenList ? [$chosenList] : [];
            }

            foreach ($chosenList as $chosen) {
                if (!$chosen || !isset($rule['answers'][$chosen])) continue;

                foreach ($rule['answers'][$chosen] as $type => $points) {
                    // Under the standard VARK scoring chart, each chosen option counts as 1 point
                    $scores[$type] += 1;
                }
            }
        }

        // Sort scores descending to find the dominant style
        arsort($scores);
        $types = array_keys($scores);
        $first = $types[0];

        // ── PASS 2: Confidence Calculation ───────────────────────────────────
        $totalFinal = max(1, array_sum($scores)); // avoid division by zero
        $confidence = round(($scores[$first] / $totalFinal) * 100, 1);

        return [
            'style'      => $first,
            'scores'     => $scores,
            'confidence' => $confidence,
            'is_mixed'   => $confidence < 45,
        ];
    }

    private function buildPersona(string $style, float $confidence, array $answers): string
    {
        $labels = [
            'read_write'  => 'Read/Write Learner',
            'auditory'    => 'Auditory Learner',
            'visual'      => 'Visual Learner',
            'kinesthetic' => 'Kinaesthetic Learner',
        ];

        return $labels[$style];
    }

    private function generateRecommendations(string $style, array $result, array $answers): array
    {
        $recs = [];
        $isMixed = $result['is_mixed'];

        if ($style === 'read_write') {
            $recs[] = 'Your dashboard highlights Materials first — use the sidebar Notepad to write custom notes and acronyms to reinforce the concepts.';
            $recs[] = 'When reading or taking quizzes, actively summarize the key points in the notepad on the right. Rewriting information helps your memory.';
            if (($answers['q5'] ?? null) === 'C') {
                $recs[] = 'You prefer working independently — organize your study notes into custom topic folders using the "My Folders" sidebar section.';
            } else {
                $recs[] = 'Try forming a study group where you can compare summaries and share acronyms with classmates.';
            }
            if (($answers['q8'] ?? null) === 'C') {
                $recs[] = 'Organize your study room into categorized note folders. Having neat, structured summaries keeps you motivated.';
            }
        } elseif ($style === 'auditory') {
            $recs[] = 'Your dashboard highlights Other Materials (e-books, notes) first — read them out loud or mouth the words silently to engage your auditory memory.';
            $recs[] = 'After reading a flashcard term, say it aloud and use it in a sentence. Verbal repetition is your strongest memory tool.';
            if (($answers['q2'] ?? null) === 'B') {
                $recs[] = 'You focus best with background sound — light instrumental or white noise while studying with flashcards can improve your retention.';
            } else {
                $recs[] = 'Try recording yourself reading key definitions and replaying them during rest periods for passive reinforcement.';
            }
            if (($answers['q6'] ?? null) === 'C') {
                $recs[] = 'When you get a quiz question wrong, say the correct answer out loud three times — verbal repetition helps auditory learners correct mistakes faster.';
            }
        } elseif ($style === 'visual') {
            $recs[] = 'Your dashboard highlights Diagrams and Infographics first — make sure to study visual aids to lock in concepts.';
            $recs[] = 'Use mental pictures of postures (Rukuk, Sujud) and Wudhu sequences. Visualizing these processes is your strongest memory tool.';
            if (($answers['q5'] ?? null) === 'C') {
                $recs[] = 'Try organizing your notes into highly visual mind maps or color-coded folders.';
            }
        } else { // kinesthetic
            $recs[] = 'Your dashboard highlights Flashcard Review Mode and Interactive matching — study by physically interacting with cards.';
            $recs[] = 'Practice using swipe flashcards and timed challenges — kinaesthetic learners learn best through active, physical actions.';
            if (($answers['q3'] ?? null) === 'D') {
                $recs[] = 'Jump straight into active self-testing first, then check the reading materials to fill in what you missed.';
            }
        }

        if ($isMixed) {
            $recs[] = 'Your learning style shows a blend of more than one type — experiment with different study approaches across Materials, Flashcards, and Quizzes to discover what works best for you each week.';
        }

        return $recs;
    }

    private function getKnowledgeBase(): array
    {
        return [
            'q1' => [
                'weight' => 1,
                'answers' => [
                    'A' => ['kinesthetic' => 3],
                    'B' => ['auditory' => 3],
                    'C' => ['read_write' => 3],
                    'D' => ['visual' => 3],
                ]
            ],
            'q2' => [
                'weight' => 1,
                'answers' => [
                    'A' => ['visual' => 3],
                    'B' => ['auditory' => 3],
                    'C' => ['read_write' => 3],
                    'D' => ['kinesthetic' => 3],
                ]
            ],
            'q3' => [
                'weight' => 1,
                'answers' => [
                    'A' => ['kinesthetic' => 3],
                    'B' => ['visual' => 3],
                    'C' => ['read_write' => 3],
                    'D' => ['auditory' => 3],
                ]
            ],
            'q4' => [
                'weight' => 1,
                'answers' => [
                    'A' => ['kinesthetic' => 3],
                    'B' => ['auditory' => 3],
                    'C' => ['visual' => 3],
                    'D' => ['read_write' => 3],
                ]
            ],
            'q5' => [
                'weight' => 1,
                'answers' => [
                    'A' => ['auditory' => 3],
                    'B' => ['visual' => 3],
                    'C' => ['kinesthetic' => 3],
                    'D' => ['read_write' => 3],
                ]
            ],
            'q6' => [
                'weight' => 1,
                'answers' => [
                    'A' => ['kinesthetic' => 3],
                    'B' => ['read_write' => 3],
                    'C' => ['visual' => 3],
                    'D' => ['auditory' => 3],
                ]
            ],
            'q7' => [
                'weight' => 1,
                'answers' => [
                    'A' => ['kinesthetic' => 3],
                    'B' => ['auditory' => 3],
                    'C' => ['visual' => 3],
                    'D' => ['read_write' => 3],
                ]
            ],
            'q8' => [
                'weight' => 1,
                'answers' => [
                    'A' => ['read_write' => 3],
                    'B' => ['kinesthetic' => 3],
                    'C' => ['auditory' => 3],
                    'D' => ['visual' => 3],
                ]
            ],
            'q9' => [
                'weight' => 1,
                'answers' => [
                    'A' => ['read_write' => 3],
                    'B' => ['auditory' => 3],
                    'C' => ['kinesthetic' => 3],
                    'D' => ['visual' => 3],
                ]
            ],
            'q10' => [
                'weight' => 1,
                'answers' => [
                    'A' => ['kinesthetic' => 3],
                    'B' => ['visual' => 3],
                    'C' => ['read_write' => 3],
                    'D' => ['auditory' => 3],
                ]
            ],
            'q11' => [
                'weight' => 1,
                'answers' => [
                    'A' => ['visual' => 3],
                    'B' => ['read_write' => 3],
                    'C' => ['auditory' => 3],
                    'D' => ['kinesthetic' => 3],
                ]
            ],
            'q12' => [
                'weight' => 1,
                'answers' => [
                    'A' => ['auditory' => 3],
                    'B' => ['read_write' => 3],
                    'C' => ['visual' => 3],
                    'D' => ['kinesthetic' => 3],
                ]
            ],
            'q13' => [
                'weight' => 1,
                'answers' => [
                    'A' => ['kinesthetic' => 3],
                    'B' => ['auditory' => 3],
                    'C' => ['read_write' => 3],
                    'D' => ['visual' => 3],
                ]
            ],
            'q14' => [
                'weight' => 1,
                'answers' => [
                    'A' => ['kinesthetic' => 3],
                    'B' => ['read_write' => 3],
                    'C' => ['auditory' => 3],
                    'D' => ['visual' => 3],
                ]
            ],
            'q15' => [
                'weight' => 1,
                'answers' => [
                    'A' => ['kinesthetic' => 3],
                    'B' => ['auditory' => 3],
                    'C' => ['read_write' => 3],
                    'D' => ['visual' => 3],
                ]
            ],
            'q16' => [
                'weight' => 1,
                'answers' => [
                    'A' => ['visual' => 3],
                    'B' => ['auditory' => 3],
                    'C' => ['read_write' => 3],
                    'D' => ['kinesthetic' => 3],
                ]
            ],
        ];
    }
    /**
     * Single progress record with full details (for mobile app detail view).
     */
    public function progressDetail(Request $request, Progress $progress)
    {
        // Ensure the progress belongs to the requesting user
        if ($progress->student_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user = $request->user();
        $classTeacher = \App\Models\User::where('role', 'teacher')->where('class_name', $user->class_name)->first();
        $teacherName = $classTeacher ? $classTeacher->name : 'Teacher';

        $questions = \App\Models\Question::where('topic', $progress->topic)
            ->where('difficulty', $progress->difficulty)
            ->get();

        return response()->json([
            'id'              => $progress->id,
            'score'           => $progress->score,
            'status'          => $progress->status,
            'student_answers' => $progress->student_answers,
            'teacher_notes'   => $progress->teacher_notes,
            'quiz'            => [
                'id'         => $progress->id,
                'title'      => ($progress->topic ?? 'General') . ' (' . ucfirst($progress->difficulty ?? 'easy') . ')',
                'difficulty' => $progress->difficulty,
                'teacher'    => $classTeacher ? $classTeacher->only(['id', 'name']) : ['id' => null, 'name' => $teacherName],
                'questions'  => $questions->map(fn($q) => [
                    'question_text'  => $q->question_text,
                    'options'        => $q->options,
                    'correct_answer' => $q->correct_answer,
                ]),
            ],
        ]);
    }

    /**
     * Get unique note folders (topics) for read/write learners.
     */
    public function getNoteFolders(Request $request)
    {
        $topics = \App\Models\StudentNote::where('user_id', $request->user()->id)
            ->select('topic')
            ->distinct()
            ->orderBy('topic')
            ->pluck('topic');

        return response()->json($topics);
    }

    /**
     * Get all notes inside a specific folder/topic.
     */
    public function getFolderNotes(Request $request, $topic)
    {
        $notes = \App\Models\StudentNote::where('user_id', $request->user()->id)
            ->where('topic', $topic)
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($notes);
    }

    /**
     * Save/update a student note (MCQ/material notepad).
     */
    public function saveNote(Request $request)
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:100',
            'difficulty' => 'nullable|string|max:50',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'resource_type' => 'nullable|string|max:50',
            'resource_id' => 'nullable|integer',
        ]);

        $note = \App\Models\StudentNote::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'resource_type' => $validated['resource_type'] ?? null,
                'resource_id' => $validated['resource_id'] ?? null,
                'topic' => $validated['topic'],
            ],
            [
                'difficulty' => $validated['difficulty'] ?? null,
                'title' => $validated['title'],
                'content' => $validated['content'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Note saved successfully!',
            'note' => $note,
        ]);
    }

    /**
     * Get a specific existing note by resource type and ID.
     */
    public function getResourceNote(Request $request)
    {
        $resourceType = $request->query('resource_type');
        $resourceId = $request->query('resource_id');

        if (!$resourceType || !$resourceId) {
            return response()->json(null);
        }

        $note = \App\Models\StudentNote::where('user_id', $request->user()->id)
            ->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->first();

        return response()->json($note);
    }

    /**
     * Delete a note.
     */
    public function deleteNote(Request $request, \App\Models\StudentNote $note)
    {
        if ($note->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $note->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Reset flashcard progress for a set (for authenticated student).
     */
    public function resetFlashcardSet(Request $request, \App\Models\FlashcardSet $set)
    {
        $cardIds = $set->flashcards()->pluck('id')->toArray();
        \App\Models\FlashcardProgress::where('user_id', $request->user()->id)
            ->whereIn('flashcard_id', $cardIds)
            ->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get Daily Doas by situation.
     */
    public function dailyDoa(Request $request)
    {
        $webController = new \App\Http\Controllers\StudentController();
        $reflection = new \ReflectionClass($webController);
        $method = $reflection->getMethod('getStudentDoas');
        $method->setAccessible(true);
        $doas = $method->invoke($webController);

        $requestedSituation = $request->query('situation', 'study');
        if (!array_key_exists($requestedSituation, $doas)) {
            $requestedSituation = 'study';
        }

        $situationDoas = $doas[$requestedSituation];
        foreach ($situationDoas as $index => &$doa) {
            $doa['audio'] = asset('audio/doas/' . $requestedSituation . '_' . ($index + 1) . '.mp3');
        }

        return response()->json([
            'situation' => $requestedSituation,
            'doas' => $situationDoas,
            'all_situations' => array_keys($doas)
        ]);
    }
}
