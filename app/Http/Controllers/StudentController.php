<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Flashcard;
use App\Models\FlashcardSet;
use App\Models\FlashcardProgress;
use App\Models\Progress;
use App\Models\Quiz;
use App\Models\Topic;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display student dashboard.
     * Also handles the "Maybe later" dismissal of the diagnosis banner.
     */
    public function dashboard(Request $request)
    {
        $user = auth()->user();

        // Handle "Maybe later" — dismiss the banner for 24 hours via session
        if ($request->query('dismiss_diag') == 1) {
            session(['diag_banner_dismissed' => true]);
            return redirect()->route('student.dashboard');
        }

        $quizzes  = Quiz::where('is_flagged', false)->with('teacher')->get();
        $progress = $user->progress()->with('quiz.teacher')->get();

        return view('student.dashboard', compact('quizzes', 'progress'));
    }

    /**
     * Display quizzes list / folders.
     */
    public function quizzes()
    {
        $user = auth()->user();
        
        $teacherIds = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->pluck('id')
            ->toArray();

        $topics = Topic::where('type', 'quiz')
            ->whereIn('user_id', $teacherIds)
            ->get();

        return view('student.quizzes', compact('topics'));
    }

    /**
     * Display quizzes under a specific topic folder.
     */
    public function quizFolder($topic)
    {
        $user = auth()->user();
        
        $teacherIds = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->pluck('id')
            ->toArray();

        $quizzes = Quiz::where('is_flagged', false)
            ->where('topic', $topic)
            ->whereIn('teacher_id', $teacherIds)
            ->with('teacher')
            ->withCount('questions')
            ->latest()
            ->paginate(12);

        return view('student.quiz_folder', compact('topic', 'quizzes'));
    }

    /**
     * Display learning materials topics/folders.
     */
    public function contents(Request $request)
    {
        $user = auth()->user();
        
        $teacherIds = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->pluck('id')
            ->toArray();

        $topics = Topic::where('type', 'material')
            ->whereIn('user_id', $teacherIds)
            ->get();

        return view('student.contents.index', compact('topics'));
    }

    /**
     * Display materials under a specific topic folder.
     */
    public function contentFolder(Request $request, $topic)
    {
        $user = auth()->user();
        
        $teacherIds = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->pluck('id')
            ->toArray();

        $contents = Content::where('is_flagged', false)
            ->where('topic', $topic)
            ->whereIn('teacher_id', $teacherIds)
            ->with('teacher')
            ->latest()
            ->paginate(6);
        
        $favoritedContentIds = Favorite::where('student_id', $user->id)
            ->whereNotNull('content_id')
            ->pluck('content_id')
            ->toArray();

        return view('student.contents.folder', compact('topic', 'contents', 'favoritedContentIds'));
    }

    /**
     * Display single content item.
     */
    public function showContent(Content $content)
    {
        return view('teacher.contents.show', compact('content')); // Reuses show template
    }

    /**
     * Display flashcards folders / sets.
     */
    public function flashcards(Request $request)
    {
        $user = auth()->user();
        $selectedTopic = $request->query('topic');
        
        $teacherIds = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->pluck('id')
            ->toArray();

        $topics = Topic::where('type', 'flashcard')
            ->whereIn('user_id', $teacherIds)
            ->get();

        $flashcardSets = collect();
        if ($selectedTopic) {
            $flashcardSets = FlashcardSet::where('is_flagged', false)
                ->where('topic', $selectedTopic)
                ->whereIn('user_id', $teacherIds)
                ->latest()
                ->paginate(6);
            
            // Calculate stats for each set for this user
            foreach ($flashcardSets as $set) {
                $total = $set->flashcards()->count();
                $cardIds = $set->flashcards()->pluck('id')->toArray();
                
                $progressRecords = FlashcardProgress::where('user_id', $user->id)
                    ->whereIn('flashcard_id', $cardIds)
                    ->get();
                    
                $mastered = $progressRecords->where('status', 'mastered')->count();
                $review = $progressRecords->where('status', 'review')->count();
                $learning = $progressRecords->where('status', 'learning')->count();
                
                // Unseen or status 'new' are counted as new
                $recordedCount = $progressRecords->count();
                $new = ($total - $recordedCount) + $progressRecords->where('status', 'new')->count();

                $set->stats = (object)[
                    'total' => $total,
                    'mastered' => $mastered,
                    'review' => $review,
                    'learning' => $learning,
                    'new' => $new
                ];
            }
        }

        $favoritedFlashcardIds = Favorite::where('student_id', $user->id)
            ->whereNotNull('flashcard_set_id')
            ->pluck('flashcard_set_id')
            ->toArray();

        return view('student.flashcards.index', compact('topics', 'flashcardSets', 'favoritedFlashcardIds'));
    }

    /**
     * Show a single flashcard set for practice.
     */
    public function showFlashcardSet(FlashcardSet $set)
    {
        $allCards = $set->flashcards()->get(['id', 'term', 'definition']);
        return view('student.flashcards.show', [
            'flashcardSet' => $set,
            'allCards' => $allCards
        ]);
    }

    /**
     * Reset flashcard progress for a set.
     */
    public function resetFlashcardSet(FlashcardSet $set)
    {
        $cardIds = $set->flashcards()->pluck('id')->toArray();
        FlashcardProgress::where('user_id', auth()->id())
            ->whereIn('flashcard_id', $cardIds)
            ->delete();

        return redirect()->back()->with('success', 'Progress reset successfully for this flashcard set.');
    }

    /**
     * Submit flashcard rating using SM-2 algorithm.
     */
    public function reviewFlashcard(Request $request, Flashcard $flashcard)
    {
        $request->validate([
            'quality' => 'required|integer|min:0|max:5',
        ]);

        $quality = $request->input('quality');
        $progress = FlashcardProgress::firstOrCreate(
            ['user_id' => auth()->id(), 'flashcard_id' => $flashcard->id]
        );

        $status = $progress->status ?? 'new';
        $repetitions = $progress->repetitions;
        $interval = $progress->interval;
        $easeFactor = $progress->ease_factor ?? 2.5;

        if ($quality < 3) {
            $status = 'learning';
            $repetitions = 0;
            $interval = 0;
        } else {
            if ($status === 'new' || $status === 'learning') {
                if ($quality === 3) {
                    $interval = 1;
                    $status = 'review';
                } else {
                    $interval = 3;
                    $status = 'review';
                }
                $repetitions = 1;
            } else {
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
     * Display Daily Quran page.
     */
    public function dailyQuran()
    {
        $dailyAyah = Cache::remember('daily_ayah_v2_' . now()->format('Y-m-d'), 60 * 24, function () {
            $totalVerses = 6236;
            $ayahId = (now()->dayOfYear + now()->year) % $totalVerses + 1;

            try {
                $response = Http::get("http://api.alquran.cloud/v1/ayah/{$ayahId}/editions/quran-uthmani,en.sahih,ms.basmeih");

                if ($response->successful()) {
                    $data = $response->json('data');
                    return [
                        'arabic' => [
                            'text' => $data[0]['text'] ?? ''
                        ],
                        'english' => [
                            'text' => $data[1]['text'] ?? ''
                        ],
                        'malay' => [
                            'text' => $data[2]['text'] ?? ''
                        ],
                        'surah' => [
                            'englishName' => $data[0]['surah']['englishName'] ?? '',
                            'name' => $data[0]['surah']['name'] ?? '',
                        ],
                        'numberInSurah' => $data[0]['numberInSurah'] ?? 1,
                        'audio' => [
                            'audio' => "https://cdn.alquran.cloud/media/audio/ayah/ar.alafasy/{$ayahId}"
                        ]
                    ];
                }
            } catch (\Exception $e) {
                // Fallback below
            }

            return [
                'arabic' => ['text' => 'إِنَّ مَعَ الْعُسْرِ يُسْرًا'],
                'english' => ['text' => 'Indeed, with hardship will be ease.'],
                'malay' => ['text' => 'Sesungguhnya bersama kesulitan ada kemudahan.'],
                'surah' => ['englishName' => 'Al-Inshirah', 'name' => 'الشرح'],
                'numberInSurah' => 6,
                'audio' => ['audio' => 'https://cdn.alquran.cloud/media/audio/ayah/ar.alafasy/6085']
            ];
        });

        return view('student.daily_quran', compact('dailyAyah'));
    }

    /**
     * Fetch verse by mood.
     */
    public function quranMood(Request $request)
    {
        $mood = $request->query('mood', 'happy');

        // Mapping moods to specific Ayah IDs (based on thematic relevance)
        $moodMap = [
            'happy' => 6074,        // Ad-Duha 93:5
            'sad' => 6072,          // Ad-Duha 93:3
            'anxious' => 1735,      // Al-Ra'd 13:28
            'unmotivated' => 432,   // Al-Imran 3:139
            'lost' => 6076,         // Ad-Duha 93:7
        ];

        $ayahId = $moodMap[$mood] ?? 6074;

        try {
            $response = Http::get("http://api.alquran.cloud/v1/ayah/{$ayahId}/editions/quran-uthmani,en.sahih,ms.basmeih");

            if ($response->successful()) {
                $data = $response->json('data');
                return response()->json([
                    'success' => true,
                    'arabic' => $data[0]['text'] ?? '',
                    'translation_en' => $data[1]['text'] ?? '',
                    'translation_ms' => $data[2]['text'] ?? '',
                    'surah' => $data[0]['surah']['englishName'] ?? '',
                    'numberInSurah' => $data[0]['numberInSurah'] ?? 1,
                    'audio' => "https://cdn.alquran.cloud/media/audio/ayah/ar.alafasy/{$ayahId}"
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

    /**
     * Display a folder's flashcards for students.
     */
    public function flashcardFolder(Request $request, $topic)
    {
        $user = auth()->user();
        
        $teacherIds = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->pluck('id')
            ->toArray();

        $flashcardSets = FlashcardSet::where('is_flagged', false)
            ->where('topic', $topic)
            ->whereIn('user_id', $teacherIds)
            ->latest()
            ->paginate(12);
        
        // Calculate stats for each set for this user
        foreach ($flashcardSets as $set) {
            $total = $set->flashcards()->count();
            $cardIds = $set->flashcards()->pluck('id')->toArray();
            
            $progressRecords = FlashcardProgress::where('user_id', $user->id)
                ->whereIn('flashcard_id', $cardIds)
                ->get();
                
            $mastered = $progressRecords->where('status', 'mastered')->count();
            $review = $progressRecords->where('status', 'review')->count();
            $learning = $progressRecords->where('status', 'learning')->count();
            
            $recordedCount = $progressRecords->count();
            $new = ($total - $recordedCount) + $progressRecords->where('status', 'new')->count();

            $set->stats = (object)[
                'total' => $total,
                'mastered' => $mastered,
                'review' => $review,
                'learning' => $learning,
                'new' => $new
            ];
        }

        $favoritedFlashcardIds = Favorite::where('student_id', $user->id)
            ->whereNotNull('flashcard_set_id')
            ->pluck('flashcard_set_id')
            ->toArray();

        return view('student.flashcards.folder', compact('topic', 'flashcardSets', 'favoritedFlashcardIds'));
    }
}
