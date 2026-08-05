<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Topic;
use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser as PdfParser;

class QuizController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display teacher's quizzes.
     */
    public function index(Request $request)
    {
        $topics = Topic::where('user_id', auth()->id())->where('type', 'quiz')->get();

        return view('teacher.quizzes.index', compact('topics'));
    }

    public function folder($topic)
    {
        $difficulties = Question::where('topic', $topic)
            ->select('difficulty')
            ->distinct()
            ->pluck('difficulty')
            ->toArray();

        $quizzes = collect();

        $totalQuestionsCount = 0;
        $allAttemptsCount = 0;
        $totalScoresSum = 0;
        $latestUpdated = null;

        foreach ($difficulties as $diff) {
            $quizQuestions = Question::where('topic', $topic)->where('difficulty', $diff);
            $qCount = $quizQuestions->count();
            $totalQuestionsCount += $qCount;

            $lastQ = Question::where('topic', $topic)->where('difficulty', $diff)->latest('updated_at')->first();
            if ($lastQ && (!$latestUpdated || $lastQ->updated_at > $latestUpdated)) {
                $latestUpdated = $lastQ->updated_at;
            }

            // Calculate progress attempts for this topic & difficulty
            $progressRecords = Progress::where('topic', $topic)->where('difficulty', $diff)->get();
            $attemptsCount = $progressRecords->count();
            $avgScore = $attemptsCount > 0 ? round($progressRecords->avg('score')) : 0;

            $quiz = new \stdClass();
            $quiz->topic = $topic;
            $quiz->difficulty = $diff;
            $quiz->title = $topic . ' (' . ucfirst($diff) . ')';
            $quiz->questions_count = $qCount;
            $quiz->attempts_count = $attemptsCount;
            $quiz->avg_score = $avgScore;
            $quiz->created_at = $lastQ ? $lastQ->created_at : now();
            $quiz->updated_at = $lastQ ? $lastQ->updated_at : now();

            $quizzes->push($quiz);
        }

        $formattedLastUpdated = $latestUpdated ? $latestUpdated->format('M j, Y') : date('M j, Y');

        // Return a LengthAwarePaginator to support views using pagination
        $quizzes = new \Illuminate\Pagination\LengthAwarePaginator(
            $quizzes,
            $quizzes->count(),
            12,
            1,
            ['path' => request()->url()]
        );

        return view('teacher.quizzes.folder', compact('quizzes', 'topic', 'totalQuestionsCount', 'formattedLastUpdated'));
    }

    /**
     * Show quiz creation form.
     */
    public function create(Request $request)
    {
        $difficulty = $request->query('difficulty', 'easy');
        $topics = Topic::where('user_id', auth()->id())->where('type', 'quiz')->get();

        return view('teacher.quizzes.create', compact('difficulty', 'topics'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'topic' => 'required|string',
            'difficulty' => 'required|string|in:easy,medium,hard',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string',
            'questions.*.type' => 'required|string',
            'questions.*.correct' => 'required|string',
            'questions.*.options' => 'nullable|array',
        ]);

        foreach ($validated['questions'] as $q) {
            Question::create([
                'question_text' => $q['text'],
                'type' => $q['type'],
                'options' => $q['options'] ?? null,
                'correct_answer' => $q['correct'],
                'points' => 10,
                'topic' => $validated['topic'],
                'difficulty' => $validated['difficulty'],
            ]);
        }

        // Clear generated questions session
        session()->forget('generated_questions');

        return redirect()->route('teacher.quizzes.folder', $validated['topic'])
            ->with('success', 'Questions added to Question Bank successfully!');
    }

    /**
     * Show quiz details.
     */
    public function show(string $topic, string $difficulty)
    {
        $quiz = new \stdClass();
        $quiz->topic = $topic;
        $quiz->difficulty = $difficulty;
        $quiz->title = $topic . ' (' . ucfirst($difficulty) . ')';
        $quiz->questions = Question::where('topic', $topic)
            ->where('difficulty', $difficulty)
            ->get();
        return view('teacher.quizzes.show', compact('quiz'));
    }

    /**
     * Show quiz edit form.
     */
    public function edit(string $topic, string $difficulty)
    {
        $quiz = new \stdClass();
        $quiz->topic = $topic;
        $quiz->difficulty = $difficulty;
        $quiz->title = $topic . ' (' . ucfirst($difficulty) . ')';
        $quiz->questions = Question::where('topic', $topic)
            ->where('difficulty', $difficulty)
            ->get();

        $topics = Topic::where('user_id', auth()->id())->where('type', 'quiz')->get();

        return view('teacher.quizzes.edit', compact('quiz', 'topics'));
    }

    /**
     * Update quiz.
     */
    public function update(Request $request, string $topic, string $difficulty)
    {
        $validated = $request->validate([
            'topic' => 'required|string',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string',
            'questions.*.type' => 'required|string',
            'questions.*.correct' => 'required|string',
            'questions.*.options' => 'nullable|array',
        ]);

        // Delete existing questions in this topic + difficulty
        Question::where('topic', $topic)->where('difficulty', $difficulty)->delete();

        foreach ($validated['questions'] as $q) {
            Question::create([
                'question_text' => $q['text'],
                'type' => $q['type'],
                'options' => $q['options'] ?? null,
                'correct_answer' => $q['correct'],
                'points' => 10,
                'topic' => $validated['topic'],
                'difficulty' => $difficulty,
            ]);
        }

        return redirect()->route('teacher.quizzes.folder', $validated['topic'])
            ->with('success', 'Questions updated successfully!');
    }

    /**
     * Delete quiz.
     */
    public function destroy(string $topic, string $difficulty)
    {
        Question::where('topic', $topic)->where('difficulty', $difficulty)->delete();

        return redirect()->route('teacher.quizzes.folder', $topic)
            ->with('success', 'Questions deleted successfully!');
    }

    /**
     * Render AI generation form.
     */
    public function generate()
    {
        $topics = Topic::where('user_id', auth()->id())->where('type', 'quiz')->get();

        return view('teacher.quizzes.generate', compact('topics'));
    }

    /**
     * Process AI quiz generation (single model).
     */
    public function processGenerate(Request $request)
    {
        $request->validate([
            'topic' => 'required|string',
            'difficulty' => 'required|string|in:easy,medium,hard',
            'question_count' => 'required|integer|min:1|max:20',
            'context' => 'nullable|string',
            'instructions' => 'nullable|string',
            'file' => 'nullable|file|max:204800', // 200MB
        ]);

        $textContext = $request->input('context', '');

        // Extract PDF text if file uploaded
        if ($request->hasFile('file')) {
            try {
                $pdfParser = new PdfParser();
                $pdf = $pdfParser->parseFile($request->file('file')->path());
                $textContext .= "\n\n" . $pdf->getText();
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Failed to extract text from PDF: ' . $e->getMessage());
            }
        }

        $prompt = $this->buildQuizPrompt(
            $request->topic,
            $request->difficulty,
            $request->question_count,
            $textContext,
            $request->input('instructions', '')
        );

        $result = $this->callAI($prompt, 'openai/gpt-4o-mini', 0.85);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', 'AI generation failed: ' . $result['error']);
        }

        session(['generated_questions' => $result['questions']]);

        return redirect()->route('teacher.quizzes.create', [
            'difficulty' => $request->difficulty,
            'topic' => $request->topic
        ]);
    }

    /**
     * Process AI side-by-side comparison.
     */
    public function processCompare(Request $request)
    {
        $request->validate([
            'topic' => 'required|string',
            'difficulty' => 'required|string|in:easy,medium,hard',
            'question_count' => 'required|integer|min:1|max:20',
            'context' => 'nullable|string',
            'instructions' => 'nullable|string',
            'file' => 'nullable|file|max:204800',
        ]);

        $textContext = $request->input('context', '');

        if ($request->hasFile('file')) {
            try {
                $pdfParser = new PdfParser();
                $pdf = $pdfParser->parseFile($request->file('file')->path());
                $parsedText = $pdf->getText();
                $parsedText = mb_convert_encoding($parsedText, 'UTF-8', 'UTF-8');
                $textContext .= "\n\n" . $parsedText;
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Failed to extract text from PDF: ' . $e->getMessage());
            }
        }

        $prompt = $this->buildQuizPrompt(
            $request->topic,
            $request->difficulty,
            $request->question_count,
            $textContext,
            $request->input('instructions', '')
        );

        // Fetch Gemini questions
        $gemini = $this->callAI($prompt, 'google/gemini-2.5-flash', 0.85); // OpenRouter supports this

        // Fetch GPT questions using actual OpenAI model via OpenRouter
        $gpt = $this->callAI($prompt, 'openai/gpt-4o-mini', 0.9);

        return view('teacher.quizzes.compare', [
            'gemini' => $gemini,
            'gpt' => $gpt,
            'topic' => $request->topic,
            'difficulty' => $request->difficulty
        ]);
    }

    /**
     * Save selected questions from compare screen.
     */
    public function saveSelected(Request $request)
    {
        $validated = $request->validate([
            'topic' => 'required|string',
            'difficulty' => 'required|string',
            'questions' => 'required|string', // JSON string
        ]);

        $selectedQuestions = json_decode($validated['questions'], true);

        if (is_array($selectedQuestions)) {
            foreach ($selectedQuestions as $q) {
                Question::create([
                    'question_text' => $q['text'] ?? $q['question_text'],
                    'type' => $q['type'] ?? (($validated['difficulty'] === 'easy') ? 'mcq' : 'short_answer'),
                    'options' => $q['options'] ?? null,
                    'correct_answer' => $q['correct_answer'] ?? '',
                    'points' => 10,
                    'topic' => $validated['topic'],
                    'difficulty' => $validated['difficulty'],
                ]);
            }
        }

        return redirect()->route('teacher.quizzes.folder', $validated['topic'])
            ->with('success', 'Selected questions saved to Question Bank!');
    }

    /**
     * Show student quiz taking page.
     */
    public function take(string $topic, string $difficulty)
    {
        $quiz = new \stdClass();
        $quiz->topic = $topic;
        $quiz->difficulty = $difficulty;
        $quiz->title = $topic . ' (' . ucfirst($difficulty) . ')';
        $quiz->questions = Question::where('topic', $topic)
            ->where('difficulty', $difficulty)
            ->get();
        return view('student.quiz.take', compact('quiz'));
    }

    /**
     * Submit student quiz results.
     */
    public function submit(Request $request, string $topic, string $difficulty)
    {
        $validated = $request->validate([
            'score' => 'required|integer',
            'answers' => 'required|string', // JSON string
        ]);

        $status = ($difficulty === 'hard' || $difficulty === 'medium') ? 'pending' : 'completed';

        Progress::updateOrCreate(
            [
                'student_id' => auth()->id(), 
                'topic' => $topic,
                'difficulty' => $difficulty
            ],
            [
                'score' => $validated['score'],
                'student_answers' => json_decode($validated['answers'], true),
                'status' => $status,
            ]
        );

        return redirect()->route('student.quizzes.folder', ['topic' => $topic])->with('success', 'Quiz results submitted!');
    }

    /**
     * Search global Questions from existing quizzes by topic and difficulty.
     */
    public function searchQuestions(Request $request)
    {
        $topic = $request->input('topic');
        $difficulty = $request->input('difficulty');

        if (!$topic || !$difficulty) {
            return response()->json([]);
        }

        // Fetch questions matching the topic and difficulty directly
        $questions = \App\Models\Question::where('topic', $topic)
            ->where('difficulty', $difficulty)
            ->get()
            ->unique('question_text')
            ->values();

        return response()->json($questions);
    }

    /**
     * Build prompt for Quiz Generation.
     */
    private function buildQuizPrompt($topic, $difficulty, $count, $context = '', $instructions = '')
    {
        $typeInstruction = ($difficulty === 'easy') 
            ? "MCQ questions with exactly 4 options (a, b, c, d) and a single correct option. You MUST populate the 'options' field with a JSON object containing keys 'a', 'b', 'c', and 'd' with their respective option values. Never return null or empty for options when difficulty is easy."
            : "Short answer questions requiring textual verification.";

        if ($difficulty === 'hard') {
            $typeInstruction .= " For these KBAT questions, the suggested correct_answer MUST strictly follow the Malaysian SPM MRSM KBAT format, consisting of exactly 4 sentences:
            1. First sentence: Isi (marked with '(I)' at the end of the sentence)
            2. Second sentence: Huraian (marked with '(H)' at the end of the sentence)
            3. Third sentence: Huraian Lengkap (Contoh / Kesan, marked with '(HL)' at the end of the sentence)
            4. Fourth sentence: Kesimpulan (marked with '(K)' at the end of the sentence)
            
            Example format of correct_answer:
            'Wanita yang tidak menutup aurat dengan sempurna akan menarik perhatian lelaki. (I) Hal ini kerana pakaian seksi tersebut akan mengundang pandangan rakus mata-mata lelaki berhidung belang. (H) Kesannya akan berlaku kes-kes jenayah seperti rogol dan bunuh. (HL) Kesimpulannya kita hendaklah mengamalkan cara hidup Islam supaya dilindungi Allah di dunia dan di akhirat. (K)'";
        }

        $prompt = "You are an AI specialized in Pendidikan Agama Islam (PAI). Generate exactly {$count} fresh, unique, and newly formulated quiz questions for the topic: '{$topic}' at a '{$difficulty}' difficulty level.
        The questions must be {$typeInstruction}
        IMPORTANT: Avoid repeating standard/generic textbook questions. Ensure they are creative, highly diverse, and fully written in Bahasa Melayu.
        
        Enforce output strictly as a JSON object matching this schema:
        {
            \"questions\": [
                {
                    \"text\": \"The question text\",
                    \"type\": \"" . ($difficulty === 'easy' ? 'mcq' : 'short_answer') . "\",
                    \"options\": " . ($difficulty === 'easy' ? "{\"a\": \"Option A\", \"b\": \"Option B\", \"c\": \"Option C\", \"d\": \"Option D\"}" : "null") . ",
                    \"correct_answer\": \"" . ($difficulty === 'easy' ? "a/b/c/d" : "The correct text answer following the KBAT format") . "\",
                    \"points\": 10
                }
            ]
        }
        Do not return any markdown wrappers like ```json or anything else. Just the raw JSON string.";

        if (!empty($context)) {
            $prompt .= "\n\nUse this context material to formulate questions:\n" . substr($context, 0, 10000);
        }

        if (!empty($instructions)) {
            $prompt .= "\n\nCustom Instructions:\n" . $instructions;
        }

        return $prompt;
    }

    /**
     * Call APIFree AI Unified Endpoint
     */
    private function callAI($prompt, $model = 'openai/gpt-4o-mini', $temp = 0.7)
    {
        $key = env('OPENROUTER_API_KEY');
        
        if (empty($key)) {
            return ['error' => 'API Key is not set in the .env file.'];
        }

        // Clean up malformed UTF-8 characters to prevent json_encode exception in HTTP client
        $prompt = mb_convert_encoding($prompt, 'UTF-8', 'UTF-8');

        $url = "https://openrouter.ai/api/v1/chat/completions";

        try {
            $response = Http::timeout(120)->withToken($key)->withHeaders([
                'Content-Type' => 'application/json',
                'HTTP-Referer' => url('/'), // Optional, for OpenRouter rankings
                'X-Title' => 'EzPAIzy App' // Optional, for OpenRouter rankings
            ])->post($url, [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => $temp
            ]);

            if ($response->successful()) {
                $jsonResponse = $response->json();
                $text = data_get($jsonResponse, 'choices.0.message.content');
                
                if ($text === null) {
                    return ['error' => 'Unexpected API format. Raw JSON: ' . json_encode($jsonResponse)];
                }

                // Clean up any potential markdown wraps
                $text = trim($text);
                if (str_starts_with($text, '```')) {
                    $text = preg_replace('/^```(?:json)?|```$/s', '', $text);
                    $text = trim($text);
                }
                
                $decoded = json_decode($text, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
                return ['error' => 'Failed to parse JSON response: ' . json_last_error_msg() . ' (Raw: ' . substr($text, 0, 200) . ')'];
            }

            return ['error' => 'API response status ' . $response->status() . ' - ' . $response->body()];

        } catch (\Exception $e) {
            return ['error' => 'Request Exception: ' . $e->getMessage()];
        }
    }
}
