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
  final Map<String, List<String>> _answers = {
    'q1': [],
    'q2': [],
    'q3': [],
    'q4': [],
    'q5': [],
    'q6': [],
    'q7': [],
    'q8': [],
    'q9': [],
    'q10': [],
    'q11': [],
    'q12': [],
    'q13': [],
    'q14': [],
    'q15': [],
    'q16': [],
  };

  int _currentIndex = 0;
  bool _loading = false;

  final List<Question> _questions = const [
    Question(
      number: '1',
      dimension: 'VARK v9.2',
      text: 'I am making a history of the area where I live. I would:',
      options: {
        'A': 'compare historical photos of the area with what is there now.',
        'B': 'record stories from people talking about old times.',
        'C': 'read articles and other information in old newspapers and documents.',
        'D': 'gather old maps and charts.',
      },
    ),
    Question(
      number: '2',
      dimension: 'VARK v9.2',
      text: 'A website has a video showing how to make a special graph or chart. There is a person speaking, some lists and words describing what to do and some diagrams. I would learn most from:',
      options: {
        'A': 'seeing the diagrams.',
        'B': 'listening.',
        'C': 'reading the words.',
        'D': 'watching the actions.',
      },
    ),
    Question(
      number: '3',
      dimension: 'VARK v9.2',
      text: 'I want to find out more about a tour that I am going on. I would:',
      options: {
        'A': 'look at details about the highlights and activities on the tour.',
        'B': 'use a map and see where the places are.',
        'C': 'read about the tour on the itinerary.',
        'D': 'talk with the person who planned the tour or others who are going on the tour.',
      },
    ),
    Question(
      number: '4',
      dimension: 'VARK v9.2',
      text: 'When choosing a career or area of study, these are important for me:',
      options: {
        'A': 'Applying my knowledge in real situations.',
        'B': 'Communicating with others through discussion.',
        'C': 'Working with designs, maps or charts.',
        'D': 'Using words well in written communications.',
      },
    ),
    Question(
      number: '5',
      dimension: 'VARK v9.2',
      text: 'When I am learning I:',
      options: {
        'A': 'like to talk things through.',
        'B': 'see patterns in things.',
        'C': 'use examples and applications.',
        'D': 'read books, articles and handouts.',
      },
    ),
    Question(
      number: '6',
      dimension: 'VARK v9.2',
      text: 'I want to save more money and to decide between a range of options. I would:',
      options: {
        'A': 'consider examples of each option using my financial information.',
        'B': 'read a print brochure that describes the options in detail.',
        'C': 'use graphs showing different options for different time periods.',
        'D': 'talk with an expert about the options.',
      },
    ),
    Question(
      number: '7',
      dimension: 'VARK v9.2',
      text: 'I want to learn how to play a new board game or card game. I would:',
      options: {
        'A': 'watch others play the game before joining in.',
        'B': 'listen to somebody explaining it and ask questions.',
        'C': 'use the diagrams that explain the various stages, moves and strategies in the game.',
        'D': 'read the instructions.',
      },
    ),
    Question(
      number: '8',
      dimension: 'VARK v9.2',
      text: 'I want to be sure I am doing my physiotherapy exercises correctly. I would:',
      options: {
        'A': 'check a list of important aspects of the exercises to get right.',
        'B': 'compare what I am doing with a video demonstration.',
        'C': 'listen to an explanation about how the exercises should be done.',
        'D': 'study diagrams illustrating the way the exercises should be done.',
      },
    ),
    Question(
      number: '9',
      dimension: 'VARK v9.2',
      text: 'I want to learn to do something new on a computer. I would:',
      options: {
        'A': 'read the written instructions that came with the program.',
        'B': 'talk with people who know about the program.',
        'C': 'start using it and learn by trial and error.',
        'D': 'follow the diagrams in a book.',
      },
    ),
    Question(
      number: '10',
      dimension: 'VARK v9.2',
      text: 'When learning from the Internet I like:',
      options: {
        'A': 'videos showing how to do things.',
        'B': 'interesting design and visual features.',
        'C': 'detailed articles.',
        'D': 'podcasts and videos where I can listen to experts.',
      },
    ),
    Question(
      number: '11',
      dimension: 'VARK v9.2',
      text: 'I want to learn about a new project. I would ask for:',
      options: {
        'A': 'diagrams to show the project stages with charts of benefits and costs.',
        'B': 'a written report describing the main features of the project.',
        'C': 'an opportunity to discuss the project.',
        'D': 'examples where the project has been used successfully.',
      },
    ),
    Question(
      number: '12',
      dimension: 'VARK v9.2',
      text: 'I want to learn how to take better photos. I would:',
      options: {
        'A': 'ask questions and talk about the camera and its features.',
        'B': 'use the written instructions about what to do.',
        'C': 'use diagrams showing the camera and what each part does.',
        'D': 'use examples of good and poor photos showing how to improve them.',
      },
    ),
    Question(
      number: '13',
      dimension: 'VARK v9.2',
      text: 'I prefer a presenter or a teacher who uses:',
      options: {
        'A': 'demonstrations, models or practical sessions.',
        'B': 'question and answer, talk, group discussion, or guest speakers.',
        'C': 'handouts, books, or readings.',
        'D': 'diagrams, charts, maps or graphs.',
      },
    ),
    Question(
      number: '14',
      dimension: 'VARK v9.2',
      text: 'I have finished a competition or test and I would like some feedback:',
      options: {
        'A': 'using examples from what I have done.',
        'B': 'using a written description of my results.',
        'C': 'from somebody who talks it through with me.',
        'D': 'using graphs showing how my performance has improved.',
      },
    ),
    Question(
      number: '15',
      dimension: 'VARK v9.2',
      text: 'I want to find out about some accommodation. Before visiting, I would want:',
      options: {
        'A': 'to view a video of the property.',
        'B': 'a discussion with the owner or manager.',
        'C': 'a printed description of the rooms and features.',
        'D': 'a plan showing the rooms and a map of the area.',
      },
    ),
    Question(
      number: '16',
      dimension: 'VARK v9.2',
      text: 'I am having trouble assembling a piece of furniture that came in parts. I would:',
      options: {
        'A': 'go through the step-by-step diagrams again to see if I missed something.',
        'B': 'ask for advice or help from someone else.',
        'C': 'go through the step-by-step written instructions again to see if I missed something.',
        'D': 'try arranging the parts to see how they fit together.',
      },
    ),
  ];

  bool get _hasAnyAnswer => _answers.values.any((v) => v.isNotEmpty);

  Future<void> _submit() async {
    if (!_hasAnyAnswer) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please answer at least one question before submitting.'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    setState(() => _loading = true);
    final res = await ApiService.storeDiagnosis(
      // Filter out empty selections so we only send actual selected ones
      _answers.entries.where((e) => e.value.isNotEmpty).fold<Map<String, dynamic>>({}, (prev, e) {
        prev[e.key] = e.value;
        return prev;
      }),
    );
    setState(() => _loading = false);

    if (res != null && mounted) {
      context.go('/learning-profile');
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Failed to save assessment. Please try again.')),
      );
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
                                final selectedList = _answers['q${currentQuestion.number}'] ?? [];
                                final isSelected = selectedList.contains(letter);

                                return GestureDetector(
                                  onTap: () {
                                    setState(() {
                                      final qKey = 'q${currentQuestion.number}';
                                      final currentList = List<String>.from(_answers[qKey] ?? []);
                                      if (currentList.contains(letter)) {
                                        currentList.remove(letter);
                                      } else {
                                        currentList.add(letter);
                                      }
                                      _answers[qKey] = currentList;
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
                            onPressed: _next,
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
