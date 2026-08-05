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

        // Retrieve active quiz topics for student's teachers
        $activeQuizTopics = \App\Models\Topic::where('type', 'quiz')
            ->whereIn('user_id', $teacherIds)
            ->pluck('name')
            ->toArray();

        // Retrieve unique topic + difficulty combinations that have questions and are active
        $quizzes = \App\Models\Question::select('topic', 'difficulty')
            ->whereIn('topic', $activeQuizTopics)
            ->groupBy('topic', 'difficulty')
            ->get()
            ->map(function($q) use ($classTeacher) {
                $quiz = new \stdClass();
                $quiz->topic = $q->topic;
                $quiz->difficulty = $q->difficulty;
                $quiz->title = $q->title ?? $q->topic;
                $quiz->created_at = now();
                $quiz->teacher = $classTeacher;
                return $quiz;
            });

        // Filter progress by active quiz topics
        $progress = $user->progress()->whereIn('topic', $activeQuizTopics)->get();

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

        $hasTitleCol = \Illuminate\Support\Facades\Schema::hasColumn('questions', 'title');

        if ($hasTitleCol) {
            $quizGroups = \App\Models\Question::where('topic', $topic)
                ->select(['difficulty', 'title'])
                ->distinct()
                ->get();
        } else {
            $quizGroups = \App\Models\Question::where('topic', $topic)
                ->select('difficulty')
                ->distinct()
                ->get()
                ->map(function ($item) {
                    $item->title = null;
                    return $item;
                });
        }
        
        $allQuizzes = collect();
        foreach ($quizGroups as $group) {
            $diff = $group->difficulty;
            $titleVal = $group->title;

            $query = \App\Models\Question::where('topic', $topic)->where('difficulty', $diff);
            if ($hasTitleCol) {
                if (!empty($titleVal)) {
                    $query->where('title', $titleVal);
                } else {
                    $query->where(function ($q) {
                        $q->whereNull('title')->orWhere('title', '');
                    });
                }
            }

            $qCount = $query->count();
            if ($qCount === 0) continue;

            $quiz = new \stdClass();
            $quiz->topic = $topic;
            $quiz->difficulty = $diff;
            $quiz->title = !empty($titleVal) ? $titleVal : $topic;
            
            // Get student progress record for this specific quiz title
            $progressQuery = Progress::where('student_id', $user->id)
                ->where('topic', $topic)
                ->where('difficulty', $diff);
            
            if (\Illuminate\Support\Facades\Schema::hasColumn('progress', 'title')) {
                if (!empty($titleVal)) {
                    $progressQuery->where('title', $titleVal);
                } else {
                    $progressQuery->where(function ($q) {
                        $q->whereNull('title')->orWhere('title', '');
                    });
                }
            }
            
            // Strictly filter the results in memory to prevent any SQL collation/caching mismatches
            $quiz->progress = $progressQuery->get()->filter(function ($p) use ($titleVal, $topic) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('progress', 'title')) {
                    $pTitle = trim($p->title ?? '');
                    if (empty($titleVal)) {
                        return empty($pTitle) || strcasecmp($pTitle, trim($topic)) === 0;
                    }
                    return strcasecmp($pTitle, trim($titleVal)) === 0;
                }
                return empty($titleVal);
            })->values();
            
            $quiz->questions_count = $qCount;
            $quiz->teacher = $classTeacher;

            $allQuizzes->push($quiz);
        }

        // Return a LengthAwarePaginator to support views using pagination
        $quizzes = new \Illuminate\Pagination\LengthAwarePaginator(
            $allQuizzes,
            $allQuizzes->count(),
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

        return view('student.quiz_folder', compact('topic', 'quizzes', 'favoritedQuizMap'));
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
                [
                    'arabic' => 'رَبِّ زِدْنِي عِلْمًا',
                    'english' => 'O my Lord, increase me in knowledge.',
                    'malay' => 'Ya Tuhanku, tambahkanlah kepadaku ilmu pengetahuan.',
                    'title' => 'Doa Before Studying 1'
                ],
                [
                    'arabic' => 'اللَّهُمَّ انْفَعْنِي بِمَا عَلَّمْتَنِي وَعَلِّمْنِي مَا يَنْفَعُنِي وَزِدْنِي عِلْمًا',
                    'english' => 'O Allah, benefit me from that which You taught me, and teach me that which will benefit me, and increase me in knowledge.',
                    'malay' => 'Ya Allah, manfaatkanlah aku dengan apa yang telah Engkau ajarkan kepadaku, dan ajarilah aku apa yang bermanfaat bagiku, dan tambahkanlah ilmuku.',
                    'title' => 'Doa Before Studying 2'
                ],
                [
                    'arabic' => 'اللَّهُمَّ أَخْرِجْنِي مِنْ ظُلُمَاتِ الْوَهْمِ وَأَكْرِمْنِي بِنُورِ الْفَهْمِ',
                    'english' => 'O Allah, bring me out of the darkness of illusion and honor me with the light of understanding.',
                    'malay' => 'Ya Allah, keluarkanlah aku dari kegelapan keraguan dan muliakanlah aku dengan cahaya kefahaman.',
                    'title' => 'Doa Before Studying 3'
                ],
                [
                    'arabic' => 'اللَّهُمَّ افْتَحْ عَلَيْنَا حِكْمَتَكَ وَانْشُرْ عَلَيْنَا مِنْ خَزَائِنِ رَحْمَتِكَ',
                    'english' => 'O Allah, open for us Your wisdom and spread upon us from the treasures of Your mercy.',
                    'malay' => 'Ya Allah, bukakanlah ke atas kami hikmah-Mu dan limpahkanlah ke atas kami dari khazanah rahmat-Mu.',
                    'title' => 'Doa Before Studying 4'
                ],
                [
                    'arabic' => 'اللَّهُمَّ يَسِّرْ وَلَا تُعَسِّرْ',
                    'english' => 'O Allah, make it easy and do not make it difficult.',
                    'malay' => 'Ya Allah, mudahkanlah dan janganlah Engkau persulit.',
                    'title' => 'Doa Before Studying 5'
                ],
            ],
            'exam' => [
                [
                    'arabic' => 'رَبِّ اشْرَحْ لِي صَدْرِي وَيَسِّرْ لِي أَمْرِي وَاحْلُلْ عُقْدَةً مِّن لِّسَانِي يَفْقَهُوا قَوْلِي',
                    'english' => 'O my Lord! Open for me my chest; Ease my task for me; And remove the impediment from my speech.',
                    'malay' => 'Ya Tuhanku, lapangkanlah dadaku, mudahkanlah urusanku, dan lepaskanlah kekakuan dari lidahku.',
                    'title' => 'Doa For Exams & Clarity 1'
                ],
                [
                    'arabic' => 'اللَّهُمَّ لَا سَهْلَ إِلَّا مَا جَعَلْتَهُ سَهْلًا وَأَنْتَ تَجْعَلُ الْحَزَنَ إِذَا شِئْتَ سَهْلًا',
                    'english' => 'O Allah, there is no ease except in that which You have made easy, and You make the difficulty, if You wish, easy.',
                    'malay' => 'Ya Allah, tiada kemudahan melainkan apa yang Engkau jadikan mudah, dan Engkau jadikan kesusahan itu mudah jika Engkau kehendaki.',
                    'title' => 'Doa For Exams & Clarity 2'
                ],
                [
                    'arabic' => 'يَا حَيُّ يَا قَيُّومُ بِرَحْمَتِكَ أَسْتَغِيثُ',
                    'english' => 'O Living, O Sustaining, in Your Mercy I seek relief.',
                    'malay' => 'Wahai Tuhan Yang Maha Hidup, Wahai Tuhan Yang Maha Berdiri Sendiri, dengan rahmat-Mu aku memohon pertolongan.',
                    'title' => 'Doa For Exams & Clarity 3'
                ],
                [
                    'arabic' => 'رَبِّ يَسِّرْ وَأَعِنْ',
                    'english' => 'O Lord, make it easy and help me.',
                    'malay' => 'Ya Tuhanku, mudahkanlah dan bantulah aku.',
                    'title' => 'Doa For Exams & Clarity 4'
                ],
                [
                    'arabic' => 'اللَّهُمَّ ارْزُقْنَا النَّجَاحَ وَالتَّوْفِيقَ فِى الْاِمْتِحَانِ',
                    'english' => 'O Allah, grant us success and guidance in the examination.',
                    'malay' => 'Ya Allah, kurniakanlah kami kejayaan dan taufiq dalam peperiksaan.',
                    'title' => 'Doa For Exams & Clarity 5'
                ],
            ],
            'memory' => [
                [
                    'arabic' => 'اللَّهُمَّ إِنِّي أَسْتَوْدِعُكَ مَا قَرَأْتُ وَمَا حَفِظْتُ وَمَا تَعَلَّمْتُ',
                    'english' => 'O Allah, I entrust You with what I have read, memorized and learned.',
                    'malay' => 'Ya Allah, sesungguhnya aku menitipkan kepada-Mu apa yang telah aku baca, hafal, dan pelajari.',
                    'title' => 'Doa For Memory Retention 1'
                ],
                [
                    'arabic' => 'فَرُدَّهُ إِلَيَّ عِنْدَ حَاجَتِي إِلَيْهِ',
                    'english' => 'Bring it back to me when I am in need of it.',
                    'malay' => 'Kembalikanlah ia kepadaku ketika aku memerlukannya.',
                    'title' => 'Doa For Memory Retention 2'
                ],
                [
                    'arabic' => 'اللَّهُمَّ نَوِّرْ قَلْبِي بِنُورِ هِدَايَتِكَ كَمَا نَوَّرْتَ الْأَرْضَ بِنُورِ شَمْسِكَ',
                    'english' => 'O Allah, illuminate my heart with the light of Your guidance, just as You illuminated the earth with the light of Your sun.',
                    'malay' => 'Ya Allah, terangilah hatiku dengan cahaya petunjuk-Mu, sebagaimana Engkau menerangi bumi dengan cahaya matahari-Mu.',
                    'title' => 'Doa For Memory Retention 3'
                ],
                [
                    'arabic' => 'رَبِّ هَبْ لِي حُكْمًا وَأَلْحِقْنِي بِالصَّالِحِينَ',
                    'english' => 'My Lord, grant me wisdom and join me with the righteous.',
                    'malay' => 'Ya Tuhanku, berikanlah aku hikmah dan pertemukanlah aku dengan orang-orang yang soleh.',
                    'title' => 'Doa For Memory Retention 4'
                ],
                [
                    'arabic' => 'اللَّهُمَّ اجْعَلْ نَفْسِي مُطْمَئِنَّةً، تُؤْمِنُ بِلِقَائِكَ، وَتَرْضَى بِقَضَائِكَ',
                    'english' => 'O Allah, make my soul tranquil, believing in meeting You, and content with Your decree.',
                    'malay' => 'Ya Allah, jadikanlah jiwaku tenang, beriman dengan pertemuan dengan-Mu, dan redha dengan ketentuan-Mu.',
                    'title' => 'Doa For Memory Retention 5'
                ],
            ],
            'anxious' => [
                [
                    'arabic' => 'اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنَ الْهَمِّ وَالْحَزَنِ',
                    'english' => 'O Allah, I seek refuge in You from anxiety and sorrow.',
                    'malay' => 'Ya Allah, aku berlindung kepada-Mu dari kebimbangan dan kesedihan.',
                    'title' => 'Doa When Feeling Anxious 1'
                ],
                [
                    'arabic' => 'حَسْبِيَ اللَّهُ لَا إِلَهَ إِلَّا هُوَ عَلَيْهِ تَوَكَّلْتُ وَهُوَ رَبُّ الْعَرْشِ الْعَظِيمِ',
                    'english' => 'Allah is sufficient for me. There is no deity except Him. On Him I have relied, and He is the Lord of the Great Throne.',
                    'malay' => 'Cukuplah Allah bagiku. Tiada Tuhan selain Dia. Kepada-Nya aku bertawakkal, dan Dia adalah Tuhan Arasy yang Maha Agung.',
                    'title' => 'Doa When Feeling Anxious 2'
                ],
                [
                    'arabic' => 'لَا إِلَهَ إِلَّا أَنْتَ سُبْحَانَكَ إِنِّي كُنْتُ مِنَ الظَّالِمِينَ',
                    'english' => 'There is no deity except You; exalted are You. Indeed, I have been of the wrongdoers.',
                    'malay' => 'Tiada Tuhan melainkan Engkau, Maha Suci Engkau. Sesungguhnya aku adalah dari golongan yang menzalimi diri sendiri.',
                    'title' => 'Doa When Feeling Anxious 3'
                ],
                [
                    'arabic' => 'اللَّهُمَّ رَحْمَتَكَ أَرْجُو فَلَا تَكِلْنِي إِلَى نَفْسِي طَرْفَةَ عَيْنٍ',
                    'english' => 'O Allah, it is Your mercy that I hope for, so do not leave me in charge of my affairs even for a blink of an eye.',
                    'malay' => 'Ya Allah, rahmat-Mu yang aku harapkan, maka janganlah Engkau serahkan urusanku kepada diriku sendiri walau sekelip mata.',
                    'title' => 'Doa When Feeling Anxious 4'
                ],
                [
                    'arabic' => 'وَأُفَوِّضُ أَمْرِي إِلَى اللَّهِ ۚ إِنَّ اللَّهَ بَصِيرٌ بِالْعِبَادِ',
                    'english' => 'And I entrust my affair to Allah. Indeed, Allah is Seeing of [His] servants.',
                    'malay' => 'Dan aku menyerahkan urusanku kepada Allah. Sesungguhnya Allah Maha Melihat akan hamba-hamba-Nya.',
                    'title' => 'Doa When Feeling Anxious 5'
                ],
            ],
            'unmotivated' => [
                [
                    'arabic' => 'اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنَ الْعَجْزِ وَالْكَسَلِ',
                    'english' => 'O Allah, I seek refuge in You from weakness and laziness.',
                    'malay' => 'Ya Allah, aku berlindung kepada-Mu dari kelemahan dan kemalasan.',
                    'title' => 'Doa For Motivation 1'
                ],
                [
                    'arabic' => 'اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنَ الْبُخْلِ وَالْجُبْنِ',
                    'english' => 'O Allah, I seek refuge in You from miserliness and cowardice.',
                    'malay' => 'Ya Allah, aku berlindung kepada-Mu dari kebakhilan dan sifat penakut.',
                    'title' => 'Doa For Motivation 2'
                ],
                [
                    'arabic' => 'يَا مُقَلِّبَ الْقُلُوبِ ثَبِّتْ قَلْبِي عَلَى دِينِكَ',
                    'english' => 'O Turner of hearts, keep my heart steadfast on Your religion.',
                    'malay' => 'Wahai Tuhan yang membolak-balikkan hati, tetapkanlah hatiku di atas agama-Mu.',
                    'title' => 'Doa For Motivation 3'
                ],
                [
                    'arabic' => 'اللَّهُمَّ أَعِنِّي عَلَى ذِكْرِكَ وَشُكْرِكَ وَحُسْنِ عِبَادَتِكَ',
                    'english' => 'O Allah, help me to remember You, to give You thanks, and to perform Your worship in the best manner.',
                    'malay' => 'Ya Allah, bantulah aku untuk mengingat-Mu, bersyukur kepada-Mu, dan beribadah kepada-Mu dengan sebaik-baiknya.',
                    'title' => 'Doa For Motivation 4'
                ],
                [
                    'arabic' => 'رَبَّنَا آتِنَا مِن لَّدُنكَ رَحْمَةً وَهَيِّئْ لَنَا مِنْ أَمْرِنَا رَشَدًا',
                    'english' => 'Our Lord, grant us from Yourself mercy and prepare for us from our affair right guidance.',
                    'malay' => 'Wahai Tuhan kami, kurniakanlah rahmat dari sisi-Mu dan sediakanlah petunjuk dalam urusan kami.',
                    'title' => 'Doa For Motivation 5'
                ],
            ],
        ];
    }

    /**
     * Display Daily Doa page.
     */
    public function dailyDoa(Request $request)
    {
        $doas = $this->getStudentDoas();
        
        // If a situation is passed in URL, use it. Otherwise, default to the first one ('study')
        $requestedSituation = $request->query('situation');
        
        if ($requestedSituation && array_key_exists($requestedSituation, $doas)) {
            $situation = $requestedSituation;
        } else {
            $situation = 'study';
        }

        $situationDoas = $doas[$situation];

        // Attach the audio path and situation/index to each Doa
        foreach ($situationDoas as $index => &$doa) {
            $doa['audio'] = asset('audio/doas/' . $situation . '_' . ($index + 1) . '.mp3');
        }

        return view('student.daily_doa', compact('situationDoas', 'situation'));
    }

    /**
     * Fetch Doas by situation (deprecated, redirecting to dailyDoa with query param).
     */
    public function doaSituation(Request $request)
    {
        $situation = $request->query('situation', 'study');
        return redirect()->route('student.daily_doa', ['situation' => $situation]);
    }
}
