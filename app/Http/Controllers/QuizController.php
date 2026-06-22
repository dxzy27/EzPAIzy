<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
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

    /**
     * Display quizzes in a specific folder.
     */
    public function folder($topic)
    {
        $quizzes = Quiz::where('teacher_id', auth()->id())
            ->where('topic', $topic)
            ->latest()
            ->paginate(12);

        return view('teacher.quizzes.folder', compact('quizzes', 'topic'));
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

    /**
     * Store new quiz.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'topic' => 'required|string',
            'difficulty' => 'required|string|in:easy,medium,hard',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string',
            'questions.*.type' => 'required|string',
            'questions.*.correct' => 'required|string',
            'questions.*.options' => 'nullable|array',
        ]);

        $quiz = Quiz::create([
            'title' => $validated['title'],
            'topic' => $validated['topic'],
            'difficulty' => $validated['difficulty'],
            'teacher_id' => auth()->id(),
        ]);

        foreach ($validated['questions'] as $q) {
            Question::create([
                'quiz_id' => $quiz->id,
                'question_text' => $q['text'],
                'type' => $q['type'],
                'options' => $q['options'] ?? null,
                'correct_answer' => $q['correct'],
                'points' => 10,
            ]);
        }

        // Clear generated questions session
        session()->forget('generated_questions');

        return redirect()->route('teacher.quizzes.index', ['topic' => $quiz->topic])
            ->with('success', 'Quiz created successfully!');
    }

    /**
     * Show quiz details.
     */
    public function show(Quiz $quiz)
    {
        $quiz->load('questions');
        return view('teacher.quizzes.show', compact('quiz'));
    }

    /**
     * Show quiz edit form.
     */
    public function edit(Quiz $quiz)
    {
        $topics = Topic::where('user_id', auth()->id())->where('type', 'quiz')->get();

        return view('teacher.quizzes.edit', compact('quiz', 'topics'));
    }

    /**
     * Update quiz.
     */
    public function update(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'topic' => 'required|string',
            'questions' => 'required|array|min:1',
            'questions.*.text' => 'required|string',
            'questions.*.type' => 'required|string',
            'questions.*.correct' => 'required|string',
            'questions.*.options' => 'nullable|array',
        ]);

        $quiz->update([
            'title' => $validated['title'],
            'topic' => $validated['topic'],
        ]);

        // Delete existing questions and recreate
        $quiz->questions()->delete();

        foreach ($validated['questions'] as $q) {
            Question::create([
                'quiz_id' => $quiz->id,
                'question_text' => $q['text'],
                'type' => $q['type'],
                'options' => $q['options'] ?? null,
                'correct_answer' => $q['correct'],
                'points' => 10,
            ]);
        }

        return redirect()->route('teacher.quizzes.index', ['topic' => $quiz->topic])
            ->with('success', 'Quiz updated successfully!');
    }

    /**
     * Delete quiz.
     */
    public function destroy(Quiz $quiz)
    {
        $topic = $quiz->topic;
        $quiz->questions()->delete();
        $quiz->delete();

        return redirect()->route('teacher.quizzes.index', ['topic' => $topic])
            ->with('success', 'Quiz deleted successfully!');
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
            'file' => 'nullable|file|max:10240', // 10MB
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

        $result = $this->callGemini($prompt);

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
            'file' => 'nullable|file|max:10240',
        ]);

        $textContext = $request->input('context', '');

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

        // Fetch Gemini questions
        $gemini = $this->callGemini($prompt);

        // Fetch GPT questions (simulated by calling Gemini with a different prompt/temperature/system setting)
        $gptPrompt = "Act as OpenAI's GPT model. Generate a distinct set of questions for comparison. Here is the requirement:\n\n" . $prompt;
        $gpt = $this->callGemini($gptPrompt, 0.9); // higher temperature for variance

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
            'title' => 'required|string|max:255',
            'topic' => 'required|string',
            'difficulty' => 'required|string',
            'questions' => 'required|string', // JSON string
        ]);

        $quiz = Quiz::create([
            'title' => $validated['title'],
            'topic' => $validated['topic'],
            'difficulty' => $validated['difficulty'],
            'teacher_id' => auth()->id()
        ]);

        $selectedQuestions = json_decode($validated['questions'], true);

        if (is_array($selectedQuestions)) {
            foreach ($selectedQuestions as $q) {
                Question::create([
                    'quiz_id' => $quiz->id,
                    'question_text' => $q['text'] ?? $q['question_text'],
                    'type' => $q['type'] ?? (($quiz->difficulty === 'easy') ? 'mcq' : 'short_answer'),
                    'options' => $q['options'] ?? null,
                    'correct_answer' => $q['correct_answer'] ?? '',
                    'points' => 10
                ]);
            }
        }

        return redirect()->route('teacher.quizzes.index', ['topic' => $quiz->topic])
            ->with('success', 'Selected questions saved as a quiz!');
    }

    /**
     * Show student quiz taking page.
     */
    public function take(Quiz $quiz)
    {
        $quiz->load('questions');
        return view('student.quiz.take', compact('quiz'));
    }

    /**
     * Submit student quiz results.
     */
    public function submit(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'score' => 'required|integer',
            'answers' => 'required|string', // JSON string
        ]);

        $status = $quiz->difficulty === 'hard' ? 'pending' : 'completed';

        Progress::updateOrCreate(
            ['student_id' => auth()->id(), 'quiz_id' => $quiz->id],
            [
                'score' => $validated['score'],
                'student_answers' => json_decode($validated['answers'], true),
                'status' => $status,
            ]
        );

        return redirect()->route('student.progress')->with('success', 'Quiz results submitted!');
    }

    /**
     * Question Bank Index view.
     */
    public function questionBankIndex()
    {
        return view('teacher.question_bank.index');
    }

    /**
     * PDF Question Bank Extract to CSV.
     */
    public function extractQuestionBank(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:20480', // 20MB
        ]);

        try {
            $pdfParser = new PdfParser();
            $pdf = $pdfParser->parseFile($request->file('pdf')->path());
            $text = $pdf->getText();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to extract text from PDF: ' . $e->getMessage());
        }

        $prompt = "You are a professional exam coordinator. Read the following text from a study module and extract every question along with its answer. Enforce output strictly as a JSON object matching this schema:
        {
            \"questions\": [
                {
                    \"topic\": \"The specific subject category, e.g. Aqidah, Akhlak, Feqah\",
                    \"question\": \"The extracted question text\",
                    \"answer\": \"The correct answer text\"
                }
            ]
        }
        Do not return any other text, comments or formatting besides the raw JSON. Here is the module text:\n\n" . substr($text, 0, 15000); // chunk to avoid size limits

        $result = $this->callGemini($prompt);

        if (isset($result['error']) || empty($result['questions'])) {
            return redirect()->back()->with('error', 'AI Extraction failed: ' . ($result['error'] ?? 'No questions found.'));
        }

        // Generate CSV file
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="extracted_questions.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($result) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['topic', 'question', 'answer']);

            foreach ($result['questions'] as $q) {
                fputcsv($file, [
                    $q['topic'] ?? 'General',
                    $q['question'] ?? '',
                    $q['answer'] ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Build prompt for Quiz Generation.
     */
    private function buildQuizPrompt($topic, $difficulty, $count, $context = '', $instructions = '')
    {
        $typeInstruction = ($difficulty === 'easy') 
            ? "MCQ questions with exactly 4 options (a, b, c, d) and a single correct option."
            : "Short answer questions requiring textual verification.";

        $prompt = "You are an AI specialized in Pendidikan Agama Islam (PAI). Generate exactly {$count} quiz questions for the topic: '{$topic}' at a '{$difficulty}' difficulty level.
        The questions must be {$typeInstruction}
        
        Enforce output strictly as a JSON object matching this schema:
        {
            \"questions\": [
                {
                    \"text\": \"The question text\",
                    \"type\": \"" . ($difficulty === 'easy' ? 'mcq' : 'short_answer') . "\",
                    \"options\": " . ($difficulty === 'easy' ? "{\"a\": \"Option A\", \"b\": \"Option B\", \"c\": \"Option C\", \"d\": \"Option D\"}" : "null") . ",
                    \"correct_answer\": \"" . ($difficulty === 'easy' ? "a/b/c/d" : "The correct text answer") . "\",
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
     * Call Gemini 1.5 Flash API.
     */
    private function callGemini($prompt, $temp = 0.2)
    {
        $key = env('GEMINI_API_KEY');
        if (empty($key)) {
            return ['error' => 'GEMINI_API_KEY is not set in the .env file.'];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $key;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature' => $temp
                ]
            ]);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text');
                
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
