<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Flashcard;
use App\Models\FlashcardSet;
use App\Models\FlashcardProgress;
use App\Models\Progress;
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

        $teacherIds = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->pluck('id')
            ->toArray();

        // Get class teacher
        $classTeacher = \App\Models\User::where('role', 'teacher')->where('class_name', $user->class_name)->first();
        $teacherName = $classTeacher ? $classTeacher->name : 'Unknown';

        // Retrieve unique topic + difficulty combinations that have questions
        $quizzes = \App\Models\Question::select('topic', 'difficulty')
            ->groupBy('topic', 'difficulty')
            ->get()
            ->map(function($q) use ($classTeacher) {
                $quiz = new \stdClass();
                $quiz->topic = $q->topic;
                $quiz->difficulty = $q->difficulty;
                $quiz->title = $q->topic . ' (' . ucfirst($q->difficulty) . ')';
                $quiz->created_at = now();
                $quiz->teacher = $classTeacher;
                return $quiz;
            });

        $progress = $user->progress()->get();

        return view('student.dashboard', compact('quizzes', 'progress', 'teacherIds', 'teacherName'));
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
        
        $classTeacher = \App\Models\User::where('role', 'teacher')
            ->where('class_name', $user->class_name)
            ->first();

        $difficulties = ['easy', 'medium', 'hard'];
        $allQuizzes = collect();
        foreach ($difficulties as $diff) {
            $quiz = new \stdClass();
            $quiz->topic = $topic;
            $quiz->difficulty = $diff;
            $quiz->title = $topic . ' (' . ucfirst($diff) . ')';
            
            // Get student progress record
            $quiz->progress = Progress::where('student_id', $user->id)
                ->where('topic', $topic)
                ->where('difficulty', $diff)
                ->get();
            
            $quiz->questions_count = \App\Models\Question::where('topic', $topic)
                ->where('difficulty', $diff)
                ->count();

            $quiz->teacher = $classTeacher;

            $allQuizzes->push($quiz);
        }

        $easyQuizzes = $allQuizzes->where('difficulty', 'easy');
        $mediumQuizzes = $allQuizzes->where('difficulty', 'medium');

        $easyAllPassed = true;
        if ($easyQuizzes->count() > 0) {
            foreach ($easyQuizzes as $eq) {
                $prog = $eq->progress->first();
                if (!$prog || $prog->score < 80 || $prog->status === 'pending') {
                    $easyAllPassed = false;
                    break;
                }
            }
        }

        $mediumAllPassed = true;
        if ($mediumQuizzes->count() > 0) {
            foreach ($mediumQuizzes as $mq) {
                $prog = $mq->progress->first();
                if (!$prog || $prog->score < 80 || $prog->status === 'pending') {
                    $mediumAllPassed = false;
                    break;
                }
            }
        }

        $mediumLocked = !$easyAllPassed;
        $hardLocked = !$mediumAllPassed;

        // Return a LengthAwarePaginator to support views using pagination
        $quizzes = new \Illuminate\Pagination\LengthAwarePaginator(
            $allQuizzes,
            3,
            12,
            1,
            ['path' => request()->url()]
        );

        $favoritedQuizMap = Favorite::where('student_id', $user->id)
            ->whereNotNull('quiz_topic')
            ->get()
            ->map(function($f) {
                return $f->quiz_topic . '-' . $f->quiz_difficulty;
            })
            ->toArray();

        return view('student.quiz_folder', compact('topic', 'quizzes', 'mediumLocked', 'hardLocked', 'favoritedQuizMap'));
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

        $progressMap = \App\Models\FlashcardProgress::where('user_id', auth()->id())
            ->whereIn('flashcard_id', $allCards->pluck('id'))
            ->pluck('status', 'flashcard_id')
            ->toArray();

        foreach ($allCards as $card) {
            $card->status = $progressMap[$card->id] ?? 'new';
        }

        return view('student.flashcards.study', [
            'flashcardSet' => $set,
            'dueCards' => $allCards
        ]);
    }

    /**
     * Reset flashcard progress for a set.
     */
    public function resetFlashcardSet(FlashcardSet $set)
    {
        $cardIds = $set->flashcards()->pluck('id')->toArray();
        FlashcardProgress::whereIn('flashcard_id', $cardIds)
            ->delete();

        return redirect()->back()->with('success', 'Progress reset successfully for this flashcard set for all users.');
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

    /**
     * Get a list of common student Doas.
     */
    private function getStudentDoas()
    {
        return [
            'study' => [
                'arabic' => 'رَبِّ زِدْنِي عِلْمًا',
                'english' => 'O my Lord, increase me in knowledge.',
                'malay' => 'Ya Tuhanku, tambahkanlah kepadaku ilmu pengetahuan.',
                'title' => 'Doa Before Studying'
            ],
            'exam' => [
                'arabic' => 'رَبِّ اشْرَحْ لِي صَدْرِي وَيَسِّرْ لِي أَمْرِي وَاحْلُلْ عُقْدَةً مِّن لِّسَانِي يَفْقَهُوا قَوْلِي',
                'english' => 'O my Lord! Open for me my chest (grant me self-confidence, contentment, and boldness); Ease my task for me; And remove the impediment from my speech, so they may understand what I say.',
                'malay' => 'Ya Tuhanku, lapangkanlah dadaku, mudahkanlah urusanku, dan lepaskanlah kekakuan dari lidahku, agar mereka mengerti perkataanku.',
                'title' => 'Doa For Exams & Clarity'
            ],
            'memory' => [
                'arabic' => 'اللَّهُمَّ إِنِّي أَسْتَوْدِعُكَ مَا قَرَأْتُ وَمَا حَفِظْتُ وَمَا تَعَلَّمْتُ، فَرُدَّهُ لِي إِلَيَّ عِنْدَ حَاجَتِي إِلَيْهِ',
                'english' => 'O Allah, I entrust You with what I have read, memorized and learned. Bring it back to me when I am in need of it.',
                'malay' => 'Ya Allah, sesungguhnya aku menitipkan kepada-Mu apa yang telah aku baca, hafal, dan pelajari. Kembalikanlah ia kepadaku ketika aku memerlukannya.',
                'title' => 'Doa For Memory Retention'
            ],
            'anxious' => [
                'arabic' => 'اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنَ الْهَمِّ وَالْحَزَنِ، وَالْعَجْزِ وَالْكَسَلِ',
                'english' => 'O Allah, I seek refuge in You from anxiety and sorrow, weakness and laziness.',
                'malay' => 'Ya Allah, aku berlindung kepada-Mu dari kebimbangan dan kesedihan, dari kelemahan dan kemalasan.',
                'title' => 'Doa When Feeling Anxious'
            ],
            'unmotivated' => [
                'arabic' => 'اللَّهُمَّ لَا سَهْلَ إِلَّا مَا جَعَلْتَهُ سَهْلًا، وَأَنْتَ تَجْعَلُ الْحَزَنَ إِذَا شِئْتَ سَهْلًا',
                'english' => 'O Allah, nothing is easy except what You make easy, and You can make what is difficult easy if You wish.',
                'malay' => 'Ya Allah, tiada kemudahan melainkan apa yang Engkau jadikan mudah, dan Engkau jadikan kesusahan (yang aku alami) itu mudah jika Engkau kehendaki.',
                'title' => 'Doa For Motivation'
            ],
        ];
    }

    /**
     * Display Daily Doa page.
     */
    public function dailyDoa()
    {
        $doas = $this->getStudentDoas();
        $doaKeys = array_keys($doas);
        
        // Pick a random Doa based on the day of the year
        $dayIndex = (now()->dayOfYear + now()->year) % count($doaKeys);
        $dailyDoa = $doas[$doaKeys[$dayIndex]];

        // If there's a session doaSituation, override the dailyDoa
        if (session('doaSituation')) {
            $dailyDoa = session('doaSituation')['doa'];
        }

        return view('student.daily_doa', compact('dailyDoa'));
    }

    /**
     * Fetch Doa by situation.
     */
    public function doaSituation(Request $request)
    {
        $situation = $request->query('situation', 'study');
        $doas = $this->getStudentDoas();
        
        $dailyDoa = $doas[$situation] ?? $doas['study'];

        return redirect()->route('student.daily_doa')->with('doaSituation', [
            'situation' => ucfirst($situation),
            'doa' => $dailyDoa
        ]);
    }
}
