import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../services/api_service.dart';

class Question {
  final String number;
  final String dimension;
  final String text;
  final Map<String, String> options;

  const Question({
    required this.number,
    required this.dimension,
    required this.text,
    required this.options,
  });
}

class LearningStyleScreen extends StatefulWidget {
  const LearningStyleScreen({super.key});

  @override
  State<LearningStyleScreen> createState() => _LearningStyleScreenState();
}

class _LearningStyleScreenState extends State<LearningStyleScreen> {
  final Map<String, String?> _answers = {
    'q1': null,
    'q2': null,
    'q3': null,
    'q4': null,
    'q5': null,
    'q6': null,
    'q7': null,
    'q8': null,
    'q9': null,
    'q10': null,
  };

  int _currentIndex = 0;
  bool _loading = false;

  final List<Question> _questions = const [
    Question(
      number: '1',
      dimension: 'Memory Encoding',
      text: 'You just learned a new term. What helps you recall it best an hour later?',
      options: {
        'A': 'Writing it down, summarizing it, or making a quick acronym.',
        'B': 'Remembering how it sounded, or saying it out loud to yourself.',
        'C': 'Recalling whether you got it right or wrong in a self-test.',
      },
    ),
    Question(
      number: '2',
      dimension: 'Distraction Response',
      text: 'You lose focus while studying. What helps you get back on track?',
      options: {
        'A': 'Writing a quick summary list or making a flashcard.',
        'B': 'Listening to background music or speaking thoughts aloud.',
        'C': 'Starting a timed quiz or score-based challenge.',
      },
    ),
    Question(
      number: '3',
      dimension: 'New Topic Approach',
      text: 'How do you prefer to start learning a newly assigned topic?',
      options: {
        'A': 'Reading the text carefully and taking written summary notes.',
        'B': 'Reading the text and explaining difficult parts out loud.',
        'C': 'Jumping straight into practice questions or self-tests.',
      },
    ),
    Question(
      number: '4',
      dimension: 'Exam Preparation',
      text: 'You have an exam in two days. What is your preparation strategy?',
      options: {
        'A': 'Writing summary sheets, acronyms, or re-writing notes.',
        'B': 'Recording voice notes, discussing with friends, or explaining concepts out loud.',
        'C': 'Practicing as many timed exam questions as possible.',
      },
    ),
    Question(
      number: '5',
      dimension: 'Group Dynamics',
      text: 'What type of group study session is most beneficial for you?',
      options: {
        'A': 'Discussion-focused sessions explaining concepts verbally.',
        'B': 'Competitive quiz tournaments to see who gets the highest score.',
        'C': 'Studying quietly together and comparing written notes at the end.',
      },
    ),
    Question(
      number: '6',
      dimension: 'Failure Reaction',
      text: 'You score poorly on a quiz you prepared for. What is your reaction?',
      options: {
        'A': 'Reviewing and re-writing corrected facts in your notes.',
        'B': 'Retaking the quiz immediately to get a higher score.',
        'C': 'Talking it over and explaining the mistakes out loud.',
      },
    ),
    Question(
      number: '7',
      dimension: 'Content Preference',
      text: 'Which type of study material do you prefer most?',
      options: {
        'A': 'A printed text where you can write notes and marginal definitions.',
        'B': 'An audio lecture or podcast explaining the concepts.',
        'C': 'An interactive quiz bank with instant score feedback.',
      },
    ),
    Question(
      number: '8',
      dimension: 'Progress Motivation',
      text: 'What motivates you most to keep studying?',
      options: {
        'A': 'Seeing your scores improve on a leaderboard or progress graph.',
        'B': 'Hearing verbal praise or encouragement from a teacher or peer.',
        'C': 'Looking through folders of your completed written summaries.',
      },
    ),
    Question(
      number: '9',
      dimension: 'Retention Strategy',
      text: 'You need to memorize 15 terms for a test. What is your strategy?',
      options: {
        'A': 'Saying the terms and definitions out loud repeatedly.',
        'B': 'Writing down the terms and definitions multiple times.',
        'C': 'Practicing active recall via rapid self-quizzing.',
      },
    ),
    Question(
      number: '10',
      dimension: 'Self-Assessment',
      text: 'What is your greatest learning strength?',
      options: {
        'A': 'Learning by writing summaries, lists, and notes.',
        'B': 'Performing well under pressure, deadlines, or test scores.',
        'C': 'Retaining information by explaining, discussing, or hearing it.',
      },
    ),
  ];

  bool get _currentQuestionAnswered => _answers['q${_currentIndex + 1}'] != null;
  bool get _allAnswered => _answers.values.every((v) => v != null);

  Future<void> _submit() async {
    if (!_allAnswered) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please answer all 10 questions before submitting.'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    setState(() => _loading = true);
    final res = await ApiService.storeDiagnosis(
      _answers.map((k, v) => MapEntry(k, v!)),
    );
    setState(() => _loading = false);

    if (res != null && mounted) {
      context.go('/learning-profile');
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Failed to save assessment. Please try again.')),
      );
    }
  }

  void _next() {
    if (_currentIndex < _questions.length - 1) {
      setState(() {
        _currentIndex++;
      });
    } else {
      _submit();
    }
  }

  void _prev() {
    if (_currentIndex > 0) {
      setState(() {
        _currentIndex--;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final currentQuestion = _questions[_currentIndex];

    return Scaffold(
      backgroundColor: Colors.transparent,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 0,
        title: const Text(
          'Learning Style Diagnosis',
          style: TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 18),
        ),
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Color(0xFF64748B)),
          onPressed: () => context.go('/dashboard'),
        ),
      ),
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [
              Color(0xFFF0F7FF),
              Color(0xFFE0EDFF),
              Color(0xFFEDE9FE),
            ],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : SafeArea(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Introduction header info
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFFEDE9FE), Color(0xFFE0E7FF)],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(color: const Color(0xFFC4B5FD)),
                        ),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: const Color(0xFF7C3AED),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: const Icon(Icons.assignment, color: Colors.white, size: 20),
                            ),
                            const SizedBox(width: 14),
                            const Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'How this works',
                                    style: TextStyle(
                                      color: Color(0xFF4C1D95),
                                      fontWeight: FontWeight.bold,
                                      fontSize: 14,
                                    ),
                                  ),
                                  SizedBox(height: 4),
                                  Text(
                                    'Answer honestly based on how you actually behave. Each scenario reveals patterns across multiple learning dimensions.',
                                    style: TextStyle(color: Color(0xFF6B7280), fontSize: 12, height: 1.45),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 24),

                      // Progress bar and labels
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            'Question ${currentQuestion.number} of ${_questions.length}',
                            style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280), fontWeight: FontWeight.bold),
                          ),
                          Text(
                            '${((_currentIndex + 1) / _questions.length * 100).round()}% complete',
                            style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280), fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      ClipRRect(
                        borderRadius: BorderRadius.circular(10),
                        child: LinearProgressIndicator(
                          value: (_currentIndex + 1) / _questions.length,
                          color: const Color(0xFF7C3AED),
                          backgroundColor: Colors.white,
                          minHeight: 8,
                        ),
                      ),
                      const SizedBox(height: 24),

                      // Question Card
                      AnimatedSwitcher(
                        duration: const Duration(milliseconds: 300),
                        child: Container(
                          key: ValueKey<int>(_currentIndex),
                          width: double.infinity,
                          padding: const EdgeInsets.all(24),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(20),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFF7C3AED).withOpacity(0.06),
                                blurRadius: 24,
                                offset: const Offset(0, 8),
                              ),
                            ],
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  CircleAvatar(
                                    radius: 18,
                                    backgroundColor: const Color(0xFF7C3AED),
                                    child: Text(
                                      currentQuestion.number,
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontWeight: FontWeight.bold,
                                        fontSize: 14,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                                          decoration: BoxDecoration(
                                            color: const Color(0xFFEDE9FE),
                                            borderRadius: BorderRadius.circular(20),
                                          ),
                                          child: Text(
                                            currentQuestion.dimension.toUpperCase(),
                                            style: const TextStyle(
                                              fontSize: 9,
                                              fontWeight: FontWeight.bold,
                                              color: Color(0xFF7C3AED),
                                              letterSpacing: 0.5,
                                            ),
                                          ),
                                        ),
                                        const SizedBox(height: 10),
                                        Text(
                                          currentQuestion.text,
                                          style: const TextStyle(
                                            fontSize: 16,
                                            fontWeight: FontWeight.bold,
                                            color: Color(0xFF111827),
                                            height: 1.4,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 24),

                              // Options
                              ...currentQuestion.options.entries.map((entry) {
                                final letter = entry.key;
                                final optionText = entry.value;
                                final isSelected = _answers['q${currentQuestion.number}'] == letter;

                                return GestureDetector(
                                  onTap: () {
                                    setState(() {
                                      _answers['q${currentQuestion.number}'] = letter;
                                    });
                                  },
                                  child: Container(
                                    margin: const EdgeInsets.only(bottom: 12),
                                    padding: const EdgeInsets.all(16),
                                    decoration: BoxDecoration(
                                      color: isSelected ? const Color(0xFFEDE9FE) : Colors.white,
                                      borderRadius: BorderRadius.circular(12),
                                      border: Border.all(
                                        color: isSelected ? const Color(0xFF7C3AED) : const Color(0xFFE5E7EB),
                                        width: isSelected ? 2 : 1.5,
                                      ),
                                      boxShadow: isSelected
                                          ? [
                                              BoxShadow(
                                                color: const Color(0xFF7C3AED).withOpacity(0.08),
                                                blurRadius: 10,
                                                offset: const Offset(0, 4),
                                              ),
                                            ]
                                          : null,
                                    ),
                                    child: Row(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Container(
                                          width: 30,
                                          height: 30,
                                          decoration: BoxDecoration(
                                            color: isSelected ? const Color(0xFF7C3AED) : const Color(0xFFF3F4F6),
                                            borderRadius: BorderRadius.circular(8),
                                          ),
                                          child: Center(
                                            child: Text(
                                              letter,
                                              style: TextStyle(
                                                fontWeight: FontWeight.bold,
                                                color: isSelected ? Colors.white : const Color(0xFF374151),
                                                fontSize: 14,
                                              ),
                                            ),
                                          ),
                                        ),
                                        const SizedBox(width: 14),
                                        Expanded(
                                          child: Padding(
                                            padding: const EdgeInsets.only(top: 4),
                                            child: Text(
                                              optionText,
                                              style: TextStyle(
                                                fontSize: 14,
                                                color: const Color(0xFF111827),
                                                fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                                                height: 1.4,
                                              ),
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                );
                              }),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),

                      // Navigation Buttons
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          _currentIndex > 0
                              ? OutlinedButton.icon(
                                  onPressed: _prev,
                                  icon: const Icon(Icons.arrow_back, size: 16),
                                  label: const Text('Previous'),
                                  style: OutlinedButton.styleFrom(
                                    foregroundColor: const Color(0xFF6B7280),
                                    side: const BorderSide(color: Color(0xFFE5E7EB)),
                                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                  ),
                                )
                              : const SizedBox.shrink(),
                          ElevatedButton(
                            onPressed: _currentQuestionAnswered ? _next : null,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF7C3AED),
                              foregroundColor: Colors.white,
                              disabledBackgroundColor: const Color(0xFFC4B5FD),
                              padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 14),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  _currentIndex == _questions.length - 1 ? 'Get Results' : 'Next',
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                ),
                                const SizedBox(width: 6),
                                Icon(
                                  _currentIndex == _questions.length - 1 ? Icons.check_circle : Icons.arrow_forward,
                                  size: 16,
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
      ),
    );
  }
}
