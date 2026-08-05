import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../widgets/profile_dropdown_helper.dart';
import '../widgets/app_top_bar.dart';

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
  final bool retake;
  const LearningStyleScreen({super.key, this.retake = false});

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

  @override
  void initState() {
    super.initState();
    if (!widget.retake) {
      _checkDiagnosis();
    }
  }

  Future<void> _checkDiagnosis() async {
    setState(() => _loading = true);
    try {
      final profile = await ApiService.getDiagnosis();
      if (profile != null && profile['learning_style'] != null) {
        if (mounted) {
          context.go('/learning-profile');
          return;
        }
      }
    } catch (_) {}
    if (mounted) {
      setState(() => _loading = false);
    }
  }

  final List<Question> _questions = const [
    Question(
      number: '1',
      dimension: 'Younger VARK',
      text: 'I need to find the way to a shop that a friend has recommended. I would:',
      options: {
        'A': 'find out where the shop is in relation to somewhere I know.',
        'B': 'ask my friend to tell me the directions.',
        'C': 'write down the street directions I need to remember.',
        'D': 'use a map.',
      },
    ),
    Question(
      number: '2',
      dimension: 'Younger VARK',
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
      dimension: 'Younger VARK',
      text: 'I want to find out more about a tour that I am going on. I would:',
      options: {
        'A': 'watch videos to see if there are things I like.',
        'B': 'use a map and see where the places are.',
        'C': 'read about the tour on the itinerary.',
        'D': 'talk with the person who planned the tour or others who are going on the tour.',
      },
    ),
    Question(
      number: '4',
      dimension: 'Younger VARK',
      text: 'When choosing my subjects to study, these are important for me:',
      options: {
        'A': 'Applying my knowledge in real situations.',
        'B': 'Communicating with others through discussion.',
        'C': 'Working with designs, maps or charts.',
        'D': 'Using words well in written communications.',
      },
    ),
    Question(
      number: '5',
      dimension: 'Younger VARK',
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
      dimension: 'Younger VARK',
      text: 'I want to suggest fund-raising options for a sports team. I would:',
      options: {
        'A': 'focus on fund-raising options that I know will work.',
        'B': 'list details about different options.',
        'C': 'compare graphs of different fund-raising options.',
        'D': 'question others who have been involved with fundraising.',
      },
    ),
    Question(
      number: '7',
      dimension: 'Younger VARK',
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
      dimension: 'Younger VARK',
      text: 'I have problem with my knee. I would prefer that the doctor:',
      options: {
        'A': 'gave me something to read to explain what was wrong.',
        'B': 'used a plastic model to show me what was wrong.',
        'C': 'described what was wrong.',
        'D': 'showed me a diagram of what was wrong.',
      },
    ),
    Question(
      number: '9',
      dimension: 'Younger VARK',
      text: 'I want to learn to do something new on a computer. I would:',
      options: {
        'A': 'read the written instructions that came with the program.',
        'B': 'talk with people who know about the program.',
        'C': 'start using it and learn by trial and error.',
        'D': 'follow the diagrams in a manual or online.',
      },
    ),
    Question(
      number: '10',
      dimension: 'Younger VARK',
      text: 'When learning from the Internet I like:',
      options: {
        'A': 'videos showing how to do or make things.',
        'B': 'interesting design and visual features.',
        'C': 'interesting written descriptions, lists and explanations.',
        'D': 'audio channels where I can listen to podcasts or interviews.',
      },
    ),
    Question(
      number: '11',
      dimension: 'Younger VARK',
      text: 'After reading a play, I need to do a project. I would prefer to:',
      options: {
        'A': 'draw or sketch a scene from the play.',
        'B': 'write about the play.',
        'C': 'read a speech from the play.',
        'D': 'act out a scene from the play.',
      },
    ),
    Question(
      number: '12',
      dimension: 'Younger VARK',
      text: 'I want to learn how to take better photos. I would:',
      options: {
        'A': 'ask questions and talk about how to achieve interesting effects.',
        'B': 'use the written instructions about what to do.',
        'C': 'use diagrams showing how different camera settings work.',
        'D': 'use examples of good and poor photos showing how to improve them.',
      },
    ),
    Question(
      number: '13',
      dimension: 'Younger VARK',
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
      dimension: 'Younger VARK',
      text: 'I have finished a competition or test and I would like some feedback. I would like to have feedback:',
      options: {
        'A': 'using examples from what I have done.',
        'B': 'using a written description of my results.',
        'C': 'from somebody who talks it through with me.',
        'D': 'using graphs of my results.',
      },
    ),
    Question(
      number: '15',
      dimension: 'Younger VARK',
      text: 'I want to find out about a house or an apartment. Before visiting it, I would want:',
      options: {
        'A': 'to view a video of the property.',
        'B': 'a discussion with the owner.',
        'C': 'a printed description of the rooms and features.',
        'D': 'a plan showing the rooms and a map of the area.',
      },
    ),
    Question(
      number: '16',
      dimension: 'Younger VARK',
      text: 'I want to assemble a wooden table that came in parts (kitset). I would learn best from:',
      options: {
        'A': 'diagrams showing each stage of the assembly.',
        'B': 'advice from someone who has done it before.',
        'C': 'written instructions that came with the parts for the table.',
        'D': 'watching a video of a person assembling a similar table.',
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
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final userName = auth.user?['name'] ?? 'Student';
    final initial = userName.isNotEmpty ? userName[0].toUpperCase() : 'D';

    return Scaffold(
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
          image: DecorationImage(
            image: AssetImage('assets/images/bg1.png'),
            fit: BoxFit.cover,
          ),
        ),
        child: Container(
          color: const Color(0xFFF1F5F9).withValues(alpha: 0.15),
          child: _loading
              ? const Center(child: CircularProgressIndicator())
              : SafeArea(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
                    child: Center(
                      child: ConstrainedBox(
                        constraints: const BoxConstraints(maxWidth: 850),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Top Navigation Bar
                            const AppTopBar(),
                            const SizedBox(height: 20),

                            // Top Header Row
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Row(
                                  children: [
                                    Material(
                                      color: Colors.transparent,
                                      child: InkWell(
                                        borderRadius: BorderRadius.circular(20),
                                        onTap: () => context.go('/dashboard'),
                                        child: Container(
                                          width: 36,
                                          height: 36,
                                          decoration: BoxDecoration(
                                            shape: BoxShape.circle,
                                            border: Border.all(color: const Color(0xFFCBD5E1)),
                                            color: Colors.white,
                                          ),
                                          child: const Icon(Icons.home_outlined, size: 18, color: Color(0xFF475569)),
                                        ),
                                      ),
                                    ),
                                    const SizedBox(width: 12),
                                    const Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          'The VARK Questionnaire for Younger People',
                                          style: TextStyle(
                                            fontSize: 18,
                                            fontWeight: FontWeight.w800,
                                            color: Color(0xFF1E293B),
                                            fontFamily: 'Outfit',
                                          ),
                                        ),
                                        Text(
                                          '16 scenario-based questions • ~6 minutes',
                                          style: TextStyle(fontSize: 12, color: Color(0xFF64748B), fontFamily: 'Outfit'),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                                OutlinedButton.icon(
                                  onPressed: () => context.go('/dashboard'),
                                  style: OutlinedButton.styleFrom(
                                    foregroundColor: const Color(0xFF64748B),
                                    side: const BorderSide(color: Color(0xFFCBD5E1)),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                                  ),
                                  icon: const Icon(Icons.close, size: 14),
                                  label: const Text('Skip for now', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, fontFamily: 'Outfit')),
                                ),
                              ],
                            ),
                            const SizedBox(height: 20),

                            // How Do I Learn Best? Banner
                            Container(
                              padding: const EdgeInsets.all(18),
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
                                    width: 42,
                                    height: 42,
                                    decoration: BoxDecoration(
                                      color: const Color(0xFF7C3AED),
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                    child: const Icon(Icons.assignment_outlined, color: Colors.white, size: 22),
                                  ),
                                  const SizedBox(width: 14),
                                  const Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          'How Do I Learn Best?',
                                          style: TextStyle(
                                            color: Color(0xFF4C1D95),
                                            fontWeight: FontWeight.bold,
                                            fontSize: 15,
                                            fontFamily: 'Outfit',
                                          ),
                                        ),
                                        SizedBox(height: 4),
                                        Text(
                                          'Choose the answers which best explain your preference. Please click more than one if a single answer does not match your perception. Leave blank any question that does not apply.',
                                          style: TextStyle(color: Color(0xFF6B7280), fontSize: 12, height: 1.45, fontFamily: 'Outfit'),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 20),

                            // Progress bar and step labels
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  'Question ${currentQuestion.number} of ${_questions.length}',
                                  style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280), fontWeight: FontWeight.bold, fontFamily: 'Outfit'),
                                ),
                                Text(
                                  '${((_currentIndex + 1) / _questions.length * 100).round()}% complete',
                                  style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280), fontWeight: FontWeight.bold, fontFamily: 'Outfit'),
                                ),
                              ],
                            ),
                            const SizedBox(height: 6),
                            ClipRRect(
                              borderRadius: BorderRadius.circular(99),
                              child: LinearProgressIndicator(
                                value: (_currentIndex + 1) / _questions.length,
                                color: const Color(0xFF7C3AED),
                                backgroundColor: Colors.white,
                                minHeight: 6,
                              ),
                            ),
                            const SizedBox(height: 20),

                            // Question Card Block
                            AnimatedSwitcher(
                              duration: const Duration(milliseconds: 300),
                              child: Container(
                                key: ValueKey<int>(_currentIndex),
                                width: double.infinity,
                                padding: const EdgeInsets.all(24),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(18),
                                  border: Border.all(color: const Color(0xFFE5E7EB)),
                                  boxShadow: [
                                    BoxShadow(
                                      color: const Color(0xFF7C3AED).withOpacity(0.06),
                                      blurRadius: 20,
                                      offset: const Offset(0, 6),
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
                                              fontFamily: 'Outfit',
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
                                                    fontFamily: 'Outfit',
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
                                                  fontFamily: 'Outfit',
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 24),

                                    // Options List with Checkboxes
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
                                          margin: const EdgeInsets.only(bottom: 10),
                                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
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
                                            children: [
                                              // Explicit Checkbox
                                              Icon(
                                                isSelected ? Icons.check_box_rounded : Icons.check_box_outline_blank_rounded,
                                                color: isSelected ? const Color(0xFF7C3AED) : const Color(0xFF94A3B8),
                                                size: 20,
                                              ),
                                              const SizedBox(width: 12),
                                              // Option Letter Badge
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
                                                      fontFamily: 'Outfit',
                                                    ),
                                                  ),
                                                ),
                                              ),
                                              const SizedBox(width: 14),
                                              Expanded(
                                                child: Text(
                                                  optionText,
                                                  style: TextStyle(
                                                    fontSize: 14,
                                                    color: const Color(0xFF111827),
                                                    fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                                                    height: 1.4,
                                                    fontFamily: 'Outfit',
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
                                        label: const Text('Previous', style: TextStyle(fontFamily: 'Outfit')),
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
                                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, fontFamily: 'Outfit'),
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
            ),
        ),
      ),
    );
  }
}
