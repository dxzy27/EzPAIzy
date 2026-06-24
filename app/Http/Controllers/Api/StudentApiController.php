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
     * Dashboard stats.
     */
    public function dashboard(Request $request)
    {
        $user     = $request->user();
        $progress = $user->progress()->with(['quiz.teacher'])->latest()->get();
        $profile  = LearningProfile::where('user_id', $user->id)->first();

        return response()->json([
            'user'             => $user->only(['id', 'name', 'email']),
            'persona'          => $profile?->persona,
            'profile'          => $profile,
            'quiz_count'       => Quiz::count(),
            'materials_count'  => Content::count() + FlashcardSet::count(),
            'completed_count'  => $progress->count(),
            'recent_results'   => $progress->take(5)->values(),
        ]);
    }

    /**
     * All quizzes.
     */
    public function quizzes()
    {
        $quizzes = Quiz::with('teacher')->withCount('questions')->latest()->get();
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
        $status = $quiz->difficulty === 'hard' ? 'pending' : 'completed';

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
        $favoritedIds     = Favorite::where('student_id', $user->id)
            ->whereNotNull('content_id')
            ->pluck('content_id')
            ->toArray();

        $contents = Content::with('teacher')->latest()->get()
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
        $favoritedIds     = Favorite::where('student_id', $user->id)
            ->whereNotNull('flashcard_set_id')
            ->pluck('flashcard_set_id')
            ->toArray();

        $sets = FlashcardSet::with('flashcards')->latest()->get()
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
        $dailyAyah = Cache::remember('daily_ayah_v2_' . now()->format('Y-m-d'), 60 * 24, function () {
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
                    ];
                }
            } catch (\Exception $e) {
                // fallback below
            }

            return [
                'arabic'  => 'إِنَّ مَعَ الْعُسْرِ يُسْرًا',
                'verse'   => 'Indeed, with hardship will be ease.',
                'surah'   => 'Al-Inshirah — Ayah 6',
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
     * Store Diagnosis result.
     */
    public function storeDiagnosis(Request $request)
    {
        $facts = [
            'q1' => $request->input('q1', 'visual'),
            'q2' => $request->input('q2', 'visual'),
            'q3' => $request->input('q3', 'visual'),
            'q4' => $request->input('q4', 'slow'),
            'q5' => $request->input('q5', 'slow'),
        ];

        // 1. Determine Primary Type (Auditory, Visual, Competitive)
        $primaryScores = ['auditory' => 0, 'visual' => 0, 'competitive' => 0];
        $primaryScores[$facts['q1']]++;
        $primaryScores[$facts['q2']]++;
        $primaryScores[$facts['q3']]++;
        $maxPrimaryScore = max($primaryScores);
        $dominantType = array_search($maxPrimaryScore, $primaryScores);

        // 2. Determine Subtype (Slow vs Fast)
        $subTypeScores = ['slow' => 0, 'fast' => 0];
        $subTypeScores[$facts['q4']]++;
        $subTypeScores[$facts['q5']]++;
        $maxSubTypeScore = max($subTypeScores);
        $subType = array_search($maxSubTypeScore, $subTypeScores);

        // 3. Construct Persona Label
        $persona = ucfirst($subType) . ' ' . ucfirst($dominantType) . ' Learner';
        $recommendations = [];

        // 4. Generate Recommendations
        if ($dominantType === 'visual') {
            $recommendations[] = 'Focus heavily on Flashcards to visually map concepts and characters.';
            $recommendations[] = 'Use color-coding and try to visualize the shape of texts when learning.';
        } elseif ($dominantType === 'auditory') {
            $recommendations[] = 'Read the Jawi texts or flashcards out loud to yourself.';
            $recommendations[] = 'Discuss topics with peers to reinforce your memory verbally.';
        } else {
            $recommendations[] = 'Your primary tool should be interactive Quizzes with timers.';
            $recommendations[] = 'Set personal high scores and try to beat them consistently.';
        }

        if ($subType === 'slow') {
            $recommendations[] = 'Do not rush. Take your time to absorb the material in Reading Mode first.';
            $recommendations[] = 'Use Spaced Repetition daily for short, steady periods.';
        } else {
            $recommendations[] = 'Jump straight into Revision Mode to test your quick recall.';
            $recommendations[] = 'Keep your study sessions intense but short to maintain high engagement.';
        }

        // Save Profile
        $profile = LearningProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            array_merge($facts, [
                'persona' => $persona,
                'recommendations' => $recommendations
            ])
        );

        return response()->json($profile);
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
}
