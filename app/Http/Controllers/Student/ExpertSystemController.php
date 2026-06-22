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
            'weight'  => 3,
            'dimension' => 'memory_encoding',
            'answers' => [
                'A' => ['visual' => 3, 'auditory' => 0, 'competitive' => 0],
                'B' => ['visual' => 0, 'auditory' => 3, 'competitive' => 0],
                'C' => ['visual' => 1, 'auditory' => 0, 'competitive' => 2],
            ],
        ],
        'q2' => [
            'weight'  => 3,
            'dimension' => 'distraction_response',
            'answers' => [
                'A' => ['visual' => 2, 'auditory' => 1, 'competitive' => 0],
                'B' => ['visual' => 0, 'auditory' => 3, 'competitive' => 0],
                'C' => ['visual' => 0, 'auditory' => 0, 'competitive' => 3],
            ],
        ],
        'q3' => [
            'weight'  => 2,
            'dimension' => 'new_topic_approach',
            'answers' => [
                'A' => ['visual' => 2, 'auditory' => 0, 'competitive' => 1],
                'B' => ['visual' => 0, 'auditory' => 2, 'competitive' => 1],
                'C' => ['visual' => 0, 'auditory' => 0, 'competitive' => 3],
            ],
        ],
        'q4' => [
            'weight'  => 3,
            'dimension' => 'exam_preparation',
            'answers' => [
                'A' => ['visual' => 3, 'auditory' => 0, 'competitive' => 0],
                'B' => ['visual' => 0, 'auditory' => 2, 'competitive' => 1],
                'C' => ['visual' => 0, 'auditory' => 0, 'competitive' => 3],
            ],
        ],
        'q5' => [
            'weight'  => 2,
            'dimension' => 'group_dynamics',
            'answers' => [
                'A' => ['visual' => 1, 'auditory' => 2, 'competitive' => 0],
                'B' => ['visual' => 1, 'auditory' => 0, 'competitive' => 2],
                'C' => ['visual' => 2, 'auditory' => 0, 'competitive' => 1],
            ],
        ],
        'q6' => [
            'weight'  => 3,
            'dimension' => 'failure_reaction',
            'answers' => [
                'A' => ['visual' => 2, 'auditory' => 1, 'competitive' => 0],
                'B' => ['visual' => 0, 'auditory' => 0, 'competitive' => 3],
                'C' => ['visual' => 0, 'auditory' => 3, 'competitive' => 0],
            ],
        ],
        'q7' => [
            'weight'  => 2,
            'dimension' => 'content_preference',
            'answers' => [
                'A' => ['visual' => 3, 'auditory' => 0, 'competitive' => 0],
                'B' => ['visual' => 0, 'auditory' => 3, 'competitive' => 0],
                'C' => ['visual' => 0, 'auditory' => 1, 'competitive' => 2],
            ],
        ],
        'q8' => [
            'weight'  => 2,
            'dimension' => 'progress_motivation',
            'answers' => [
                'A' => ['visual' => 0, 'auditory' => 0, 'competitive' => 3],
                'B' => ['visual' => 1, 'auditory' => 2, 'competitive' => 0],
                'C' => ['visual' => 3, 'auditory' => 0, 'competitive' => 0],
            ],
        ],
        'q9' => [
            'weight'  => 3,
            'dimension' => 'retention_strategy',
            'answers' => [
                'A' => ['visual' => 0, 'auditory' => 3, 'competitive' => 0],
                'B' => ['visual' => 3, 'auditory' => 0, 'competitive' => 0],
                'C' => ['visual' => 0, 'auditory' => 0, 'competitive' => 3],
            ],
        ],
        'q10' => [
            'weight'  => 2,
            'dimension' => 'self_assessment',
            'answers' => [
                'A' => ['visual' => 2, 'auditory' => 1, 'competitive' => 0],
                'B' => ['visual' => 0, 'auditory' => 0, 'competitive' => 3],
                'C' => ['visual' => 1, 'auditory' => 2, 'competitive' => 0],
            ],
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
        // Validate all 10 answers are present and are A, B, or C
        $request->validate([
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

        $answers = $request->only(['q1','q2','q3','q4','q5','q6','q7','q8','q9','q10']);

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
                'score_visual'      => $result['scores']['visual'],
                'score_auditory'    => $result['scores']['auditory'],
                'score_competitive' => $result['scores']['competitive'],
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
        $scores = ['visual' => 0, 'auditory' => 0, 'competitive' => 0];
        $totalWeight = 0;

        // ── PASS 1: Weighted Evidence Accumulation ───────────────────────────
        foreach ($this->knowledgeBase as $qKey => $rule) {
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

        // ── PASS 2: Conflict Resolution ──────────────────────────────────────
        // Sort scores descending
        arsort($scores);
        $types   = array_keys($scores);
        $first   = $types[0];
        $second  = $types[1];
        $margin  = $scores[$first] - $scores[$second];

        // Conflict threshold: if gap < 15% of total evidence, apply tiebreakers
        $conflictThreshold = $totalEvidence * 0.15;

        if ($margin < $conflictThreshold) {
            // Tiebreaker rules using "strong signal" questions
            // q1 (memory_encoding) and q9 (retention_strategy) are the most
            // diagnostic questions — they get double-weighted in a tie.
            $strongSignals = ['q1', 'q4', 'q9'];
            $tieScores = ['visual' => 0, 'auditory' => 0, 'competitive' => 0];

            foreach ($strongSignals as $qKey) {
                $chosen = $answers[$qKey] ?? null;
                if (!$chosen || !isset($this->knowledgeBase[$qKey]['answers'][$chosen])) continue;

                $rule = $this->knowledgeBase[$qKey];
                foreach ($rule['answers'][$chosen] as $type => $points) {
                    $tieScores[$type] += $points * 2; // Double weight for tiebreakers
                }
            }

            // Apply tiebreaker deltas
            foreach ($tieScores as $type => $delta) {
                $scores[$type] += $delta;
            }
            arsort($scores);
            $types = array_keys($scores);
            $first = $types[0];
        }

        // ── PASS 3: Confidence Calculation ───────────────────────────────────
        $totalFinal   = max(1, array_sum($scores)); // avoid division by zero
        $confidence   = round(($scores[$first] / $totalFinal) * 100, 1);

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
            'visual'      => 'Visual Learner',
            'auditory'    => 'Auditory Learner',
            'competitive' => 'Competitive Learner',
        ];

        // Add a qualifier based on confidence strength
        if ($confidence >= 65) {
            $prefix = 'Strong ';
        } elseif ($confidence >= 45) {
            $prefix = '';
        } else {
            $prefix = 'Emerging ';
        }

        return $prefix . $labels[$style];
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

        if ($style === 'visual') {
            $recs[] = 'Your dashboard highlights Flashcards first — use them as your primary study tool since visual memory of cards helps you retain concepts faster.';
            $recs[] = 'When reading Other Materials (e-books, notes), try to mentally draw or sketch the concept you just read to reinforce it visually.';

            // Extra rec based on q5 (group dynamics)
            if ($answers['q5'] === 'C') {
                $recs[] = 'You prefer studying independently — use Revision Mode on flashcards to self-test in silence without distractions.';
            } else {
                $recs[] = 'Try forming a study group where you can share and compare your visual notes or flashcard sets with classmates.';
            }

            // Extra rec based on q8 (motivation)
            if ($answers['q8'] === 'C') {
                $recs[] = 'Create a visual study progress chart — seeing how many topics you have covered will keep you motivated to continue.';
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

        } else { // competitive
            $recs[] = 'Your dashboard highlights Quizzes first — use timed quiz mode to challenge yourself and aim for a higher score each attempt.';
            $recs[] = 'Track your quiz scores in My Progress and set a personal target — beating your own record is the strongest motivator for your learning style.';

            // Extra rec based on q3 (new topic approach)
            if ($answers['q3'] === 'C') {
                $recs[] = 'When encountering a new topic, jump straight into a short quiz to gauge your baseline — then study the gaps you discovered.';
            } else {
                $recs[] = 'Use flashcards as rapid-fire self-tests: flip through as many cards as possible in 5 minutes and measure how many you got right.';
            }

            // Extra rec based on q6 (failure reaction)
            if ($answers['q6'] === 'B') {
                $recs[] = 'When you score below your target, immediately retake the quiz with the intention of beating it — competitive learners thrive on fast recovery cycles.';
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
