<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Favorite;
use App\Models\FlashcardSet;
use App\Models\Flashcard;
use App\Models\FlashcardProgress;
use App\Models\Progress;
use App\Models\Quiz;
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
            'class_name' => ['required', 'string', 'in:5A1,5A2,5A3,5B1,5B2,5B3'],
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
        $progress = $user->progress()->with(['quiz.teacher'])->latest()->get();
        $profile  = LearningProfile::where('user_id', $user->id)->first();
        $style    = $user->learning_style;

        $teacherIds = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->pluck('id')
            ->toArray();

        // 1. Calculate leaderboard if competitive
        $leaderboard = [];
        if ($style === 'competitive') {
            $classmates = \App\Models\User::where('role', 'student')
                ->where('class_name', $user->class_name)
                ->get();

            $leaderboardData = [];
            foreach ($classmates as $mate) {
                $mateProgress = $mate->progress()->with('quiz')->get();
                $totalPoints = 0;
                $quizzesCount = 0;

                foreach ($mateProgress as $p) {
                    if (!$p->quiz || $p->status === 'pending') {
                        continue;
                    }

                    $multiplier = match ($p->quiz->difficulty) {
                        'easy' => 1,
                        'medium' => 2,
                        'hard' => 3,
                        default => 1
                    };

                    $totalPoints += ($p->score * $multiplier);
                    $quizzesCount++;
                }

                $leaderboardData[] = [
                    'id' => $mate->id,
                    'name' => $mate->name,
                    'points' => $totalPoints,
                    'completed_count' => $quizzesCount,
                ];
            }

            usort($leaderboardData, function($a, $b) {
                if ($b['points'] !== $a['points']) {
                    return $b['points'] <=> $a['points'];
                }
                if ($b['completed_count'] !== $a['completed_count']) {
                    return $b['completed_count'] <=> $a['completed_count'];
                }
                return strcasecmp($a['name'], $b['name']);
            });

            $leaderboard = $leaderboardData;
        }

        // 2. Fetch new/recommended materials if not competitive
        $newMaterials = [];
        if ($style !== 'competitive') {
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
        }

        return response()->json([
            'user'             => $user->only(['id', 'name', 'email', 'learning_style', 'class_name']),
            'persona'          => $profile?->persona,
            'profile'          => $profile,
            'quiz_count'       => Quiz::where('is_flagged', false)->whereIn('teacher_id', $teacherIds)->count(),
            'materials_count'  => Content::where('is_flagged', false)->whereIn('teacher_id', $teacherIds)->count() + FlashcardSet::where('is_flagged', false)->whereIn('user_id', $teacherIds)->count(),
            'completed_count'  => $progress->count(),
            'best_score'       => ($style === 'competitive' && $progress->count() > 0) ? $progress->max('score') : null,
            'recent_results'   => $progress->take(5)->values(),
            'new_materials'    => $newMaterials,
            'leaderboard'      => $leaderboard,
        ]);
    }

    /**
     * All quizzes.
     */
    public function quizzes()
    {
        $user = auth()->user();
        $teacherIds = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->pluck('id')
            ->toArray();

        $quizzes = Quiz::where('is_flagged', false)
            ->whereIn('teacher_id', $teacherIds)
            ->with('teacher')
            ->withCount('questions')
            ->latest()
            ->get();
        return response()->json($quizzes);
    }

    /**
     * Single quiz with questions.
     */
    public function quizDetail(Quiz $quiz)
    {
        $quiz->load('questions');
        return response()->json($quiz);
    }

    /**
     * Submit quiz answers — auto-grade MCQ, pending for hard/essay.
     */
    public function submitQuiz(Request $request, Quiz $quiz)
    {
        $user      = $request->user();
        $answers   = $request->input('answers', []);
        $questions = $quiz->questions;

        // Auto-grade multiple choice
        $correct = 0;
        foreach ($questions as $i => $q) {
            $key = (string) $i;
            if (isset($answers[$key]) && $answers[$key] === $q->correct_answer) {
                $correct++;
            }
        }

        $score  = $questions->count() > 0
            ? (int) round(($correct / $questions->count()) * 100)
            : 0;
        $status = ($quiz->difficulty === 'hard' || $quiz->difficulty === 'medium') ? 'pending' : 'completed';

        // Upsert progress
        $progress = Progress::updateOrCreate(
            ['student_id' => $user->id, 'quiz_id' => $quiz->id],
            [
                'score'           => $score,
                'student_answers' => $answers,
                'status'          => $status,
            ]
        );

        return response()->json([
            'score'    => $score,
            'status'   => $status,
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
            ->map(function ($s) use ($favoritedIds) {
                $s->is_favorited = in_array($s->id, $favoritedIds);
                return $s;
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
        $progress = $request->user()
            ->progress()
            ->with(['quiz.teacher', 'quiz.questions'])
            ->latest()
            ->get();

        return response()->json($progress);
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
     * Daily Quran verse — mirrors web logic.
     */
    public function dailyQuran()
    {
        $dailyAyah = Cache::remember('daily_ayah_api_v1_' . now()->format('Y-m-d'), 60 * 24, function () {
            $totalVerses = 6236;
            $ayahId      = (now()->dayOfYear + now()->year) % $totalVerses + 1;

            try {
                $response = Http::get("http://api.alquran.cloud/v1/ayah/{$ayahId}/editions/quran-uthmani,en.sahih,ms.basmeih");

                if ($response->successful()) {
                    $data = $response->json('data');
                    return [
                        'arabic'      => $data[0]['text'] ?? '',
                        'verse'       => $data[1]['text'] ?? '',
                        'translation' => $data[2]['text'] ?? '',
                        'surah'       => ($data[0]['surah']['englishName'] ?? '') .
                            ' (' . ($data[0]['surah']['name'] ?? '') . ')' .
                            ' — Ayah ' . ($data[0]['numberInSurah'] ?? ''),
                        'audio'       => "https://cdn.alquran.cloud/media/audio/ayah/ar.alafasy/{$ayahId}",
                    ];
                }
            } catch (\Exception $e) {
                // fallback below
            }

            return [
                'arabic'  => 'إِنَّ مَعَ الْعُسْرِ يُسْرًا',
                'verse'   => 'Indeed, with hardship will be ease.',
                'surah'   => 'Al-Inshirah — Ayah 6',
                'audio'   => 'https://cdn.alquran.cloud/media/audio/ayah/ar.alafasy/6085',
            ];
        });

        return response()->json($dailyAyah);
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
        $answers = $request->validate([
            'q1'  => 'required|in:A,B,C',
            'q2'  => 'required|in:A,B,C',
            'q3'  => 'required|in:A,B,C',
            'q4'  => 'required|in:A,B,C',
            'q5'  => 'required|in:A,B,C',
            'q6'  => 'required|in:A,B,C',
            'q7'  => 'required|in:A,B,C',
            'q8'  => 'required|in:A,B,C',
            'q9'  => 'required|in:A,B,C',
            'q10' => 'required|in:A,B,C',
        ]);

        $result = $this->runInferenceEngine($answers);
        $style  = $result['style'];
        $persona = $this->buildPersona($style, $result['confidence'], $answers);
        $recommendations = $this->generateRecommendations($style, $result, $answers);

        $profile = LearningProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'answers'           => $answers,
                'score_read_write'  => $result['scores']['read_write'],
                'score_auditory'    => $result['scores']['auditory'],
                'score_competitive' => $result['scores']['competitive'],
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
        $scores = ['read_write' => 0, 'auditory' => 0, 'competitive' => 0];
        $totalWeight = 0;

        foreach ($knowledgeBase as $qKey => $rule) {
            $chosen = $answers[$qKey] ?? null;
            if (!$chosen || !isset($rule['answers'][$chosen])) continue;

            $weight = $rule['weight'];
            $totalWeight += $weight;

            foreach ($rule['answers'][$chosen] as $type => $points) {
                $scores[$type] += $points * $weight;
            }
        }

        $maxScore = max($scores);
        $totalEvidence = array_sum($scores);

        arsort($scores);
        $types   = array_keys($scores);
        $first   = $types[0];
        $second  = $types[1];
        $margin  = $scores[$first] - $scores[$second];

        $conflictThreshold = $totalEvidence * 0.15;

        if ($margin < $conflictThreshold) {
            $strongSignals = ['q1', 'q4', 'q9'];
            $tieScores = ['read_write' => 0, 'auditory' => 0, 'competitive' => 0];

            foreach ($strongSignals as $qKey) {
                $chosen = $answers[$qKey] ?? null;
                if (!$chosen || !isset($knowledgeBase[$qKey]['answers'][$chosen])) continue;

                $rule = $knowledgeBase[$qKey];
                foreach ($rule['answers'][$chosen] as $type => $points) {
                    $tieScores[$type] += $points * 2;
                }
            }

            foreach ($tieScores as $type => $delta) {
                $scores[$type] += $delta;
            }
            arsort($scores);
            $types = array_keys($scores);
            $first = $types[0];
        }

        $totalFinal   = max(1, array_sum($scores));
        $confidence   = round(($scores[$first] / $totalFinal) * 100, 1);

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
            'competitive' => 'Competitive Learner',
        ];

        if ($confidence >= 65) {
            $prefix = 'Strong ';
        } elseif ($confidence >= 45) {
            $prefix = '';
        } else {
            $prefix = 'Emerging ';
        }

        return $prefix . $labels[$style];
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
        } else {
            $recs[] = 'Your dashboard highlights Quizzes first — use timed quiz mode to challenge yourself and aim for a higher score each attempt.';
            $recs[] = 'Track your quiz scores in My Progress and set a personal target — beating your own record is the strongest motivator for your learning style.';
            if (($answers['q3'] ?? null) === 'C') {
                $recs[] = 'When encountering a new topic, jump straight into a short quiz to gauge your baseline — then study the gaps you discovered.';
            } else {
                $recs[] = 'Use flashcards as rapid-fire self-tests: flip through as many cards as possible in 5 minutes and measure how many you got right.';
            }
            if (($answers['q6'] ?? null) === 'B') {
                $recs[] = 'When you score below your target, immediately retake the quiz with the intention of beating it — competitive learners thrive on fast recovery cycles.';
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
                'weight'  => 3,
                'dimension' => 'memory_encoding',
                'answers' => [
                    'A' => ['read_write' => 3, 'auditory' => 0, 'competitive' => 0],
                    'B' => ['read_write' => 0, 'auditory' => 3, 'competitive' => 0],
                    'C' => ['read_write' => 1, 'auditory' => 0, 'competitive' => 2],
                ],
            ],
            'q2' => [
                'weight'  => 3,
                'dimension' => 'distraction_response',
                'answers' => [
                    'A' => ['read_write' => 2, 'auditory' => 1, 'competitive' => 0],
                    'B' => ['read_write' => 0, 'auditory' => 3, 'competitive' => 0],
                    'C' => ['read_write' => 0, 'auditory' => 0, 'competitive' => 3],
                ],
            ],
            'q3' => [
                'weight'  => 2,
                'dimension' => 'new_topic_approach',
                'answers' => [
                    'A' => ['read_write' => 2, 'auditory' => 0, 'competitive' => 1],
                    'B' => ['read_write' => 0, 'auditory' => 2, 'competitive' => 1],
                    'C' => ['read_write' => 0, 'auditory' => 0, 'competitive' => 3],
                ],
            ],
            'q4' => [
                'weight'  => 3,
                'dimension' => 'exam_preparation',
                'answers' => [
                    'A' => ['read_write' => 3, 'auditory' => 0, 'competitive' => 0],
                    'B' => ['read_write' => 0, 'auditory' => 2, 'competitive' => 1],
                    'C' => ['read_write' => 0, 'auditory' => 0, 'competitive' => 3],
                ],
            ],
            'q5' => [
                'weight'  => 2,
                'dimension' => 'group_dynamics',
                'answers' => [
                    'A' => ['read_write' => 1, 'auditory' => 2, 'competitive' => 0],
                    'B' => ['read_write' => 1, 'auditory' => 0, 'competitive' => 2],
                    'C' => ['read_write' => 2, 'auditory' => 0, 'competitive' => 1],
                ],
            ],
            'q6' => [
                'weight'  => 3,
                'dimension' => 'failure_reaction',
                'answers' => [
                    'A' => ['read_write' => 2, 'auditory' => 1, 'competitive' => 0],
                    'B' => ['read_write' => 0, 'auditory' => 0, 'competitive' => 3],
                    'C' => ['read_write' => 0, 'auditory' => 3, 'competitive' => 0],
                ],
            ],
            'q7' => [
                'weight'  => 2,
                'dimension' => 'content_preference',
                'answers' => [
                    'A' => ['read_write' => 3, 'auditory' => 0, 'competitive' => 0],
                    'B' => ['read_write' => 0, 'auditory' => 3, 'competitive' => 0],
                    'C' => ['read_write' => 0, 'auditory' => 1, 'competitive' => 2],
                ],
            ],
            'q8' => [
                'weight'  => 2,
                'dimension' => 'progress_motivation',
                'answers' => [
                    'A' => ['read_write' => 0, 'auditory' => 0, 'competitive' => 3],
                    'B' => ['read_write' => 1, 'auditory' => 2, 'competitive' => 0],
                    'C' => ['read_write' => 3, 'auditory' => 0, 'competitive' => 0],
                ],
            ],
            'q9' => [
                'weight'  => 3,
                'dimension' => 'retention_strategy',
                'answers' => [
                    'A' => ['read_write' => 0, 'auditory' => 3, 'competitive' => 0],
                    'B' => ['read_write' => 3, 'auditory' => 0, 'competitive' => 0],
                    'C' => ['read_write' => 0, 'auditory' => 0, 'competitive' => 3],
                ],
            ],
            'q10' => [
                'weight'  => 2,
                'dimension' => 'self_assessment',
                'answers' => [
                    'A' => ['read_write' => 2, 'auditory' => 1, 'competitive' => 0],
                    'B' => ['read_write' => 0, 'auditory' => 0, 'competitive' => 3],
                    'C' => ['read_write' => 1, 'auditory' => 2, 'competitive' => 0],
                ],
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

        $progress->load(['quiz.questions', 'quiz.teacher']);

        return response()->json([
            'id'              => $progress->id,
            'score'           => $progress->score,
            'status'          => $progress->status,
            'student_answers' => $progress->student_answers,
            'teacher_notes'   => $progress->teacher_notes,
            'quiz'            => [
                'id'         => $progress->quiz->id,
                'title'      => $progress->quiz->title,
                'difficulty' => $progress->quiz->difficulty,
                'teacher'    => $progress->quiz->teacher?->only(['id', 'name']),
                'questions'  => $progress->quiz->questions->map(fn($q) => [
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
}
