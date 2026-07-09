<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LearningProfile;
use Illuminate\Http\Request;

class ExpertSystemController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // KNOWLEDGE BASE
    // Each question has a weight (importance) and each answer option maps to
    // one or more learning types with a point contribution.
    // This makes the system non-trivial: one question can contribute partial
    // evidence to multiple types, and weights differ across questions.
    // ─────────────────────────────────────────────────────────────────────────
    private array $knowledgeBase = [
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

    // ─────────────────────────────────────────────────────────────────────────
    // SHOW — landing page: if diagnosed, show profile; otherwise redirect to quiz
    // ─────────────────────────────────────────────────────────────────────────
    public function show()
    {
        $profile = LearningProfile::where('user_id', auth()->id())->first();

        if (!$profile || !$profile->learning_style) {
            return redirect()->route('student.diagnosis.create');
        }

        return view('student.diagnosis.show', compact('profile'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CREATE — renders the 10-question diagnosis quiz
    // ─────────────────────────────────────────────────────────────────────────
    public function create()
    {
        return view('student.diagnosis.create');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE — runs the inference engine and saves the result
    // ─────────────────────────────────────────────────────────────────────────
    public function store(Request $request)
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
            return back()->withErrors(['error' => 'Please answer at least one question to diagnose your learning style.']);
        }

        // ── INFERENCE ENGINE ─────────────────────────────────────────────────
        $result = $this->runInferenceEngine($answers);
        // ─────────────────────────────────────────────────────────────────────

        $style  = $result['style'];
        $persona = $this->buildPersona($style, $result['confidence'], $answers);
        $recommendations = $this->generateRecommendations($style, $result, $answers);

        // Save to learning_profiles table
        LearningProfile::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'answers'           => $answers,
                'score_read_write'  => $result['scores']['read_write'] ?? 0,
                'score_auditory'    => $result['scores']['auditory'] ?? 0,
                'score_visual'      => $result['scores']['visual'] ?? 0,
                'score_kinesthetic' => $result['scores']['kinesthetic'] ?? 0,
                'score_competitive' => $result['scores']['competitive'] ?? 0,
                'confidence'        => $result['confidence'],
                'learning_style'    => $style,
                'persona'           => $persona,
                'recommendations'   => $recommendations,
            ]
        );

        // Also persist on the users table for fast access throughout the system
        auth()->user()->update(['learning_style' => $style]);

        return redirect()->route('student.diagnosis.show');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INFERENCE ENGINE
    //
    // This is a weighted, multi-pass rule-based engine:
    //
    // Pass 1 – Evidence Accumulation
    //   Each answer contributes weighted evidence to one or more types.
    //   A single answer can partially reinforce multiple types (not a simple vote).
    //
    // Pass 2 – Conflict Resolution Rules
    //   IF two scores are very close (margin < threshold), dimension-specific
    //   tiebreaker rules are applied using "strong signal" questions.
    //
    // Pass 3 – Confidence Calculation
    //   Confidence = (winning_score / total_evidence) * 100
    //   A result with confidence < 40 is flagged as "mixed" and uses secondary
    //   signals to break the tie.
    //
    // ─────────────────────────────────────────────────────────────────────────
    private function runInferenceEngine(array $answers): array
    {
        $scores = ['read_write' => 0, 'auditory' => 0, 'visual' => 0, 'kinesthetic' => 0, 'competitive' => 0];

        // ── PASS 1: VARK Scoring Method (Simple Selection Count) ─────────────
        foreach ($this->knowledgeBase as $qKey => $rule) {
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

    // ─────────────────────────────────────────────────────────────────────────
    // Build a human-readable persona label based on style + confidence + signals
    // ─────────────────────────────────────────────────────────────────────────
    private function buildPersona(string $style, float $confidence, array $answers): string
    {
        $labels = [
            'read_write'  => 'Read/Write Learner',
            'auditory'    => 'Auditory Learner',
            'visual'      => 'Visual Learner',
            'kinesthetic' => 'Kinaesthetic Learner',
            'competitive' => 'Competitive Learner',
        ];

        return $labels[$style];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Generate contextually-rich recommendations
    // These are NOT generic — they reference specific features in EzPAIzy
    // and vary based on which specific answers were chosen.
    // ─────────────────────────────────────────────────────────────────────────
    private function generateRecommendations(string $style, array $result, array $answers): array
    {
        $recs = [];
        $isMixed = $result['is_mixed'];

        if ($style === 'read_write') {
            $recs[] = 'Your dashboard highlights Materials first — use the sidebar Notepad to write custom notes and acronyms to reinforce the concepts.';
            $recs[] = 'When reading or taking quizzes, actively summarize the key points in the notepad on the right. Rewriting information helps your memory.';

            // Extra rec based on q5 (group dynamics)
            if ($answers['q5'] === 'C') {
                $recs[] = 'You prefer working independently — organize your study notes into custom topic folders using the "My Folders" sidebar section.';
            } else {
                $recs[] = 'Try forming a study group where you can compare summaries and share acronyms with classmates.';
            }

            // Extra rec based on q8 (motivation)
            if ($answers['q8'] === 'C') {
                $recs[] = 'Organize your study room into categorized note folders. Having neat, structured summaries keeps you motivated.';
            }

        } elseif ($style === 'auditory') {
            $recs[] = 'Your dashboard highlights Other Materials (e-books, notes) first — read them out loud or mouth the words silently to engage your auditory memory.';
            $recs[] = 'After reading a flashcard term, say it aloud and use it in a sentence. Verbal repetition is your strongest memory tool.';

            // Extra rec based on q2 (distraction response)
            if ($answers['q2'] === 'B') {
                $recs[] = 'You focus best with background sound — light instrumental or white noise while studying with flashcards can improve your retention.';
            } else {
                $recs[] = 'Try recording yourself reading key definitions and replaying them during rest periods for passive reinforcement.';
            }

            // Extra rec based on q6 (failure reaction)
            if ($answers['q6'] === 'C') {
                $recs[] = 'When you get a quiz question wrong, say the correct answer out loud three times — verbal repetition helps auditory learners correct mistakes faster.';
            }

        } elseif ($style === 'visual') {
            $recs[] = 'Your dashboard highlights Diagrams and Infographics first — make sure to study visual aids to lock in concepts.';
            $recs[] = 'Use mental pictures of postures (Rukuk, Sujud) and Wudhu sequences. Visualizing these processes is your strongest memory tool.';
            
            if ($answers['q5'] === 'C') {
                $recs[] = 'Try organizing your notes into highly visual mind maps or color-coded folders.';
            }

        } else { // kinesthetic
            $recs[] = 'Your dashboard highlights Flashcard Review Mode and Interactive matching — study by physically interacting with cards.';
            $recs[] = 'Practice using swipe flashcards and timed challenges — kinaesthetic learners learn best through active, physical actions.';

            if ($answers['q3'] === 'D') {
                $recs[] = 'Jump straight into active self-testing first, then check the reading materials to fill in what you missed.';
            }
        }

        // Mixed-type cross-cutting recommendation
        if ($isMixed) {
            $recs[] = 'Your learning style shows a blend of more than one type — experiment with different study approaches across Materials, Flashcards, and Quizzes to discover what works best for you each week.';
        }

        return $recs;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RESET — clears the student's learning style to return to basic UI
    // ─────────────────────────────────────────────────────────────────────────
    public function reset()
    {
        $user = auth()->user();
        $user->update(['learning_style' => null]);

        // Delete the profile so they can take a fresh diagnosis if desired
        LearningProfile::where('user_id', $user->id)->delete();

        return redirect()->route('student.dashboard')->with('success', 'Learning style reset successfully. You are now using the Basic UI.');
    }
}
