import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import '../widgets/study_notepad_widget.dart';
import '../services/tts_service.dart';
import '../widgets/visual_highlight_text.dart';
import '../widgets/profile_dropdown_helper.dart';
import '../widgets/app_top_bar.dart';

class TakeQuizScreen extends StatefulWidget {
  final int quizId;
  const TakeQuizScreen({super.key, required this.quizId});

  @override
  State<TakeQuizScreen> createState() => _TakeQuizScreenState();
}

class _TakeQuizScreenState extends State<TakeQuizScreen> {
  Map<String, dynamic>? quiz;
  bool loading = true;
  bool submitted = false;
  bool _isPending = false;
  int? result;

  // answers: index -> selected option key (e.g. 'a', 'b') or text for essay
  final Map<int, dynamic> answers = {};
  int currentPage = 0;
  final TextEditingController _essayController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    TtsService.stop();
    _essayController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    try {
      final d = await ApiService.getQuizDetail(widget.quizId);
      setState(() { quiz = d; loading = false; });
    } catch (_) {
      setState(() => loading = false);
    }
  }

  Future<void> _submit() async {
    final questions = quiz!['questions'] as List;
    // Confirm all answered
    if (answers.length < questions.length) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please answer all questions first')),
      );
      return;
    }

    setState(() => loading = true);
    try {
      final res = await ApiService.submitQuiz(
          widget.quizId, answers.map((k, v) => MapEntry(k.toString(), v)));
      setState(() {
        result = res['score'];
        _isPending = res['status'] == 'pending';
        submitted = true;
        loading = false;
      });
    } catch (_) {
      setState(() => loading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Submission failed. Try again.')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (loading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }
    if (quiz == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Quiz')),
        body: const Center(child: Text('Failed to load quiz')),
      );
    }
    if (submitted) return _buildResult();

    final questions = (quiz?['questions'] as List?) ?? [];
    if (questions.isEmpty) {
      return Scaffold(
        appBar: AppBar(title: const Text('Quiz')),
        body: const Center(child: Text('No questions available for this quiz')),
      );
    }
    final q = questions[currentPage];
    final auth = context.read<AuthProvider>();
    final isReadWrite = auth.user?['learning_style'] == 'read_write';
    final isKinesthetic = auth.user?['learning_style'] == 'kinesthetic';

    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9), // Soft grey background matching web
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
          color: const Color(0xFFF1F5F9).withOpacity(0.15),
          child: SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 800), // Match web container max-width 800
                  child: Container(
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16), // Rounded card corners
                      border: Border.all(color: const Color(0xFFE2E8F0)), // Slate-200 border
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.05),
                          blurRadius: 12,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        // Header: Title and Back button
                        Row(
                          children: [
                            InkWell(
                              onTap: () => context.pop(),
                              borderRadius: BorderRadius.circular(8),
                              child: const Padding(
                                padding: EdgeInsets.all(4),
                                child: Icon(Icons.arrow_back, color: Color(0xFF64748B), size: 22),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                quiz!['title'] ?? 'Quiz',
                                style: const TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF1E293B),
                                  fontFamily: 'Outfit',
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),

                        // Subtitle: Progress Text
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Question ${currentPage + 1} of ${questions.length}',
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                                color: Color(0xFF64748B),
                                fontFamily: 'Outfit',
                              ),
                            ),
                            Text(
                              '${answers.length} answered',
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                                color: Color(0xFF64748B),
                                fontFamily: 'Outfit',
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),

                        // Thin Progress Bar
                        ClipRRect(
                          borderRadius: BorderRadius.circular(50),
                          child: LinearProgressIndicator(
                            value: (currentPage + 1) / questions.length,
                            backgroundColor: const Color(0xFFF1F5F9),
                            color: const Color(0xFF3B82F6), // Web primary blue
                            minHeight: 6,
                          ),
                        ),
                        const SizedBox(height: 16),
                        const Divider(color: Color(0xFFE2E8F0), thickness: 1),
                        const SizedBox(height: 12),

                        // Question & Options Content
                        _buildQuestion(q, currentPage),

                        if (isKinesthetic && q['type'] == 'mcq') ...[
                          const SizedBox(height: 24),
                          _buildDragDropTray(currentPage, q['options']),
                        ],

                        if (isReadWrite) ...[
                          const SizedBox(height: 20),
                          StudyNotepadWidget(
                            resourceType: 'quiz',
                            resourceId: widget.quizId,
                            topic: quiz!['topic'] ?? 'General',
                            defaultTitle: 'Notes: ${quiz!['title'] ?? ''}',
                          ),
                        ],

                        const SizedBox(height: 24),
                        const Divider(color: Color(0xFFE2E8F0), thickness: 1),
                        const SizedBox(height: 16),

                        // Footer Navigation buttons
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            // Previous Button
                             OutlinedButton(
                              onPressed: currentPage > 0
                                  ? () {
                                      TtsService.stop();
                                      setState(() {
                                        currentPage--;
                                        _essayController.text = (answers[currentPage] ?? '').toString();
                                      });
                                    }
                                  : null,
                              style: OutlinedButton.styleFrom(
                                foregroundColor: const Color(0xFF64748B),
                                side: const BorderSide(color: Color(0xFFCBD5E1)),
                                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                              ),
                              child: const Text(
                                'Previous',
                                style: TextStyle(fontFamily: 'Outfit', fontWeight: FontWeight.bold),
                              ),
                            ),

                            // Next/Submit Button
                            ElevatedButton(
                              onPressed: (q['type'] == 'mcq' && answers[currentPage] == null)
                                  ? null // Disable if MCQ and no answer selected
                                  : (currentPage < questions.length - 1
                                      ? () {
                                          TtsService.stop();
                                          setState(() {
                                            currentPage++;
                                            _essayController.text = (answers[currentPage] ?? '').toString();
                                          });
                                        }
                                      : () {
                                          TtsService.stop();
                                          _submit();
                                        }),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF3B82F6), // Web primary blue
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                elevation: 0,
                              ),
                              child: Text(
                                currentPage < questions.length - 1 ? 'Next' : 'Submit',
                                style: const TextStyle(fontFamily: 'Outfit', fontWeight: FontWeight.bold),
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
      ),
    );
  }

  void _speakQuestion(Map<String, dynamic> q) {
    final text = q['question_text'] ?? '';
    final optionsData = q['options'];
    String toSpeak = text;
    if (optionsData is Map<String, dynamic>) {
      optionsData.forEach((key, val) {
        toSpeak += ", Pilihan $key, $val";
      });
    }
    TtsService.speak(toSpeak);
  }

  Widget _buildDragDropTray(int index, Map<String, dynamic>? options) {
    final selectedKey = answers[index] as String?;
    final selectedText = selectedKey != null ? (options?[selectedKey] ?? '') : '';

    return DragTarget<String>(
      onWillAccept: (data) => true,
      onAccept: (data) {
        setState(() {
          answers[index] = data;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Dropped Option ${data.toUpperCase()} into tray!'),
            duration: const Duration(milliseconds: 600),
          ),
        );
      },
      builder: (context, candidateData, rejectedData) {
        final isHovered = candidateData.isNotEmpty;
        return Container(
          width: double.infinity,
          height: 90,
          decoration: BoxDecoration(
            color: selectedKey != null
                ? const Color(0xFFEFF6FF)
                : (isHovered ? const Color(0xFFEFF6FF) : const Color(0xFFF8FAFC)),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: selectedKey != null
                  ? const Color(0xFF3B82F6)
                  : (isHovered ? const Color(0xFF3B82F6) : const Color(0xFFCBD5E1)),
              width: (isHovered || selectedKey != null) ? 2.0 : 1.5,
            ),
          ),
          child: Center(
            child: selectedKey != null
                ? Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        width: 36,
                        height: 36,
                        decoration: const BoxDecoration(
                          shape: BoxShape.circle,
                          color: Color(0xFF3B82F6),
                        ),
                        child: Center(
                          child: Text(
                            selectedKey.toUpperCase(),
                            style: const TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                              fontFamily: 'Outfit',
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 16),
                      Text(
                        selectedText,
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF1E293B),
                          fontFamily: 'Outfit',
                        ),
                      ),
                    ],
                  )
                : Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.move_to_inbox,
                        color: isHovered ? const Color(0xFF3B82F6) : const Color(0xFF94A3B8),
                        size: 24,
                      ),
                      const SizedBox(width: 12),
                      Text(
                        isHovered ? 'Drop Answer Here!' : 'Drag and Drop Chosen Answer Tray',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          color: isHovered ? const Color(0xFF3B82F6) : const Color(0xFF94A3B8),
                          fontFamily: 'Outfit',
                        ),
                      ),
                    ],
                  ),
          ),
        );
      },
    );
  }

  Widget _buildQuestion(Map<String, dynamic> q, int index) {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final isAuditory = auth.user?['learning_style'] == 'auditory';
    final optionsData = q['options'];
    final Map<String, dynamic>? options = (optionsData is Map<String, dynamic>) ? optionsData : null;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Question Text Row with speaker button
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Builder(
                builder: (context) {
                  final isVisual = auth.user?['learning_style'] == 'visual';
                  if (isVisual) {
                    return VisualHighlightText(
                      text: q['question_text'] ?? '',
                      storageKey: 'hl_quiz_q_${q['id']}',
                      textAlign: TextAlign.left,
                      style: const TextStyle(
                        fontSize: 24, // Matches large web title size
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF0F172A),
                        fontFamily: 'Outfit',
                        height: 1.45,
                      ),
                    );
                  }
                  return Text(
                    q['question_text'] ?? '',
                    style: const TextStyle(
                      fontSize: 24, // Matches large web title size
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF0F172A),
                      fontFamily: 'Outfit',
                      height: 1.45,
                    ),
                  );
                }
              ),
            ),
            if (isAuditory) ...[
              const SizedBox(width: 12),
              GestureDetector(
                onTap: () => _speakQuestion(q),
                child: Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: Colors.grey[100],
                    shape: BoxShape.circle,
                    border: Border.all(color: const Color(0xFFCBD5E1)),
                  ),
                  child: const Icon(Icons.volume_up, color: Color(0xFF3B82F6), size: 20),
                ),
              ),
            ],
          ],
        ),
        const SizedBox(height: 24),

        // MCQ Options
        if (options != null && options.isNotEmpty)
          ...options.entries.map((entry) {
            final selected = answers[index] == entry.key;
            final isKinesthetic = auth.user?['learning_style'] == 'kinesthetic';

            final optionCard = Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              decoration: BoxDecoration(
                color: selected
                    ? const Color(0xFFEFF6FF) // Light blue selected bg
                    : Colors.white,
                border: Border.all(
                  color: selected ? const Color(0xFF3B82F6) : const Color(0xFFE2E8F0),
                  width: selected ? 2 : 1,
                ),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                children: [
                  // Circle Badge with Letter
                  Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: selected ? const Color(0xFF3B82F6) : const Color(0xFFEFF6FF), // Blue when selected, light blue when not
                      border: Border.all(color: const Color(0xFF3B82F6), width: 1.5),
                    ),
                    child: Center(
                      child: Text(
                        entry.key.toUpperCase(),
                        style: TextStyle(
                          color: selected ? Colors.white : const Color(0xFF3B82F6),
                          fontWeight: FontWeight.bold,
                          fontSize: 13,
                          fontFamily: 'Outfit',
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Builder(
                      builder: (context) {
                        final isVisual = auth.user?['learning_style'] == 'visual';
                        if (isVisual) {
                          return VisualHighlightText(
                            text: entry.value ?? '',
                            storageKey: 'hl_quiz_opt_${q['id']}_${entry.key}',
                            textAlign: TextAlign.left,
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w500,
                              color: Color(0xFF1E293B),
                              fontFamily: 'Outfit',
                            ),
                          );
                        }
                        return Text(
                          entry.value ?? '',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w500,
                            color: Color(0xFF1E293B),
                            fontFamily: 'Outfit',
                          ),
                        );
                      }
                    ),
                  ),
                ],
              ),
            );

            Widget mainOptionWidget = GestureDetector(
              onTap: () => setState(() => answers[index] = entry.key),
              child: optionCard,
            );

            if (isKinesthetic) {
              return Draggable<String>(
                data: entry.key,
                feedback: Material(
                  color: Colors.transparent,
                  child: Container(
                    width: MediaQuery.of(context).size.width * 0.8,
                    constraints: const BoxConstraints(maxWidth: 500),
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEFF6FF).withOpacity(0.9),
                      border: Border.all(color: const Color(0xFF3B82F6), width: 2),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 32,
                          height: 32,
                          decoration: const BoxDecoration(
                            shape: BoxShape.circle,
                            color: Color(0xFF3B82F6),
                          ),
                          child: Center(
                            child: Text(
                              entry.key.toUpperCase(),
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Text(
                            entry.value ?? '',
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w500,
                              color: Color(0xFF1E293B),
                              fontFamily: 'Outfit',
                              decoration: TextDecoration.none,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                childWhenDragging: Opacity(
                  opacity: 0.4,
                  child: optionCard,
                ),
                child: mainOptionWidget,
              );
            }

            return mainOptionWidget;
          })
        else
          TextField(
            controller: _essayController,
            maxLines: 4,
            onChanged: (val) => answers[index] = val,
            style: const TextStyle(fontFamily: 'Outfit'),
            decoration: const InputDecoration(
              hintText: 'Type your answer here...',
              hintStyle: TextStyle(fontFamily: 'Outfit', color: Color(0xFF94A3B8)),
              border: OutlineInputBorder(),
            ),
          ),
      ],
    );
  }

  Widget _buildResult() {
    final score = result ?? 0;
    final topic = (quiz?['topic'] ?? 'General').toString();
    final quizTitle = (quiz?['title'] ?? topic).toString();

    Color scoreColor = const Color(0xFFEF4444); // red
    String scoreStatus = '💡 KEEP PRACTICING';
    Color statusBg = const Color(0xFFFEE2E2);
    Color statusFg = const Color(0xFF991B1B);
    String subtitleText = "Don't give up! Review your study materials and try retaking the quiz to boost your score.";

    if (score >= 70) {
      scoreColor = const Color(0xFF10B981); // green
      scoreStatus = '🎉 EXCELLENT PASS!';
      statusBg = const Color(0xFFD1FAE5);
      statusFg = const Color(0xFF065F46);
      subtitleText = 'Great job! You have demonstrated strong mastery of this topic.';
    } else if (score >= 50) {
      scoreColor = const Color(0xFFF59E0B); // amber
      scoreStatus = '👍 GOOD EFFORT!';
      statusBg = const Color(0xFFFEF3C7);
      statusFg = const Color(0xFF92400E);
      subtitleText = 'You are getting closer! A quick revision will help you get a top score.';
    }

    if (_isPending) {
      scoreColor = const Color(0xFF8B5CF6);
      scoreStatus = '⏳ PENDING TEACHER REVIEW';
      statusBg = const Color(0xFFEDE9FE);
      statusFg = const Color(0xFF5B21B6);
      subtitleText = 'This quiz contains KBAT essay questions. Your teacher will review your submission soon.';
    }

    final authUser = Provider.of<AuthProvider>(context, listen: false).user;
    final userName = (authUser?['name'] ?? 'Student').toString();
    final initial = userName.isNotEmpty ? userName[0].toUpperCase() : 'S';

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
          color: const Color(0xFFF1F5F9).withOpacity(0.15),
          child: SafeArea(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
              child: Center(
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 800),
                  child: Column(
                    children: [
                      // Universal Top Nav Bar
                      AppTopBar(
                        showBackButton: true,
                        onBack: () => context.go('/quizzes'),
                      ),
                      const SizedBox(height: 24),

                      // Result Card Container
                      Container(
                        width: double.infinity,
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.04),
                              blurRadius: 16,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        padding: const EdgeInsets.all(32),
                        child: Column(
                          children: [
                            // Header Topic Badge & Title
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF1F5F9),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Text(
                                topic.toUpperCase(),
                                style: GoogleFonts.outfit(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: const Color(0xFF64748B),
                                  letterSpacing: 0.5,
                                ),
                              ),
                            ),
                            const SizedBox(height: 10),
                            Text(
                              quizTitle,
                              style: GoogleFonts.outfit(
                                fontSize: 22,
                                fontWeight: FontWeight.w900,
                                color: const Color(0xFF0F172A),
                              ),
                              textAlign: TextAlign.center,
                            ),
                            const SizedBox(height: 24),

                            // Score Circle Display
                            Container(
                              width: 140,
                              height: 140,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: scoreColor.withOpacity(0.08),
                                border: Border.all(color: scoreColor.withOpacity(0.3), width: 3),
                              ),
                              child: Center(
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    if (_isPending)
                                      const Icon(Icons.hourglass_top_rounded, size: 44, color: Color(0xFF8B5CF6))
                                    else
                                      Text(
                                        '$score%',
                                        style: GoogleFonts.outfit(
                                          fontSize: 42,
                                          fontWeight: FontWeight.w900,
                                          color: scoreColor,
                                        ),
                                      ),
                                    if (!_isPending)
                                      Text(
                                        'FINAL SCORE',
                                        style: GoogleFonts.outfit(
                                          fontSize: 9,
                                          fontWeight: FontWeight.bold,
                                          color: const Color(0xFF64748B),
                                          letterSpacing: 0.5,
                                        ),
                                      ),
                                  ],
                                ),
                              ),
                            ),
                            const SizedBox(height: 18),

                            // Status Pill
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                              decoration: BoxDecoration(
                                color: statusBg,
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Text(
                                scoreStatus,
                                style: GoogleFonts.outfit(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                  color: statusFg,
                                ),
                              ),
                            ),
                            const SizedBox(height: 12),

                            // Subtitle Explanation
                            ConstrainedBox(
                              constraints: const BoxConstraints(maxWidth: 500),
                              child: Text(
                                subtitleText,
                                style: GoogleFonts.outfit(
                                  fontSize: 13,
                                  color: const Color(0xFF64748B),
                                  height: 1.5,
                                ),
                                textAlign: TextAlign.center,
                              ),
                            ),
                            const SizedBox(height: 28),
                            const Divider(color: Color(0xFFF1F5F9), thickness: 1),
                            const SizedBox(height: 24),

                            // Action Buttons Row
                            LayoutBuilder(
                              builder: (context, constraints) {
                                final isWide = constraints.maxWidth > 500;
                                return isWide
                                    ? Row(
                                        children: [
                                          Expanded(
                                            child: ElevatedButton.icon(
                                              onPressed: () {
                                                setState(() {
                                                  submitted = false;
                                                  answers.clear();
                                                  currentPage = 0;
                                                  result = null;
                                                });
                                              },
                                              icon: const Icon(Icons.refresh, size: 18, color: Colors.white),
                                              label: Text('Retake Quiz', style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.white)),
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor: const Color(0xFF10B981),
                                                padding: const EdgeInsets.symmetric(vertical: 14),
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                                elevation: 0,
                                              ),
                                            ),
                                          ),
                                          const SizedBox(width: 12),
                                          Expanded(
                                            child: ElevatedButton.icon(
                                              onPressed: () => context.go('/quizzes'),
                                              icon: const Icon(Icons.quiz_outlined, size: 18, color: Colors.white),
                                              label: Text('Back to Quizzes', style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.white)),
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor: const Color(0xFF3B82F6),
                                                padding: const EdgeInsets.symmetric(vertical: 14),
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                                elevation: 0,
                                              ),
                                            ),
                                          ),
                                          const SizedBox(width: 12),
                                          Expanded(
                                            child: OutlinedButton.icon(
                                              onPressed: () => context.go('/progress'),
                                              icon: const Icon(Icons.bar_chart, size: 18, color: Color(0xFF475569)),
                                              label: Text('View Progress', style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 13, color: const Color(0xFF475569))),
                                              style: OutlinedButton.styleFrom(
                                                side: const BorderSide(color: Color(0xFFCBD5E1)),
                                                padding: const EdgeInsets.symmetric(vertical: 14),
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                              ),
                                            ),
                                          ),
                                        ],
                                      )
                                    : Column(
                                        children: [
                                          SizedBox(
                                            width: double.infinity,
                                            child: ElevatedButton.icon(
                                              onPressed: () {
                                                setState(() {
                                                  submitted = false;
                                                  answers.clear();
                                                  currentPage = 0;
                                                  result = null;
                                                });
                                              },
                                              icon: const Icon(Icons.refresh, size: 18, color: Colors.white),
                                              label: Text('Retake Quiz', style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.white)),
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor: const Color(0xFF10B981),
                                                padding: const EdgeInsets.symmetric(vertical: 14),
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                                elevation: 0,
                                              ),
                                            ),
                                          ),
                                          const SizedBox(height: 10),
                                          SizedBox(
                                            width: double.infinity,
                                            child: ElevatedButton.icon(
                                              onPressed: () => context.go('/quizzes'),
                                              icon: const Icon(Icons.quiz_outlined, size: 18, color: Colors.white),
                                              label: Text('Back to Quizzes', style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.white)),
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor: const Color(0xFF3B82F6),
                                                padding: const EdgeInsets.symmetric(vertical: 14),
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                                elevation: 0,
                                              ),
                                            ),
                                          ),
                                          const SizedBox(height: 10),
                                          SizedBox(
                                            width: double.infinity,
                                            child: OutlinedButton.icon(
                                              onPressed: () => context.go('/progress'),
                                              icon: const Icon(Icons.bar_chart, size: 18, color: Color(0xFF475569)),
                                              label: Text('View Progress', style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 13, color: const Color(0xFF475569))),
                                              style: OutlinedButton.styleFrom(
                                                side: const BorderSide(color: Color(0xFFCBD5E1)),
                                                padding: const EdgeInsets.symmetric(vertical: 14),
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                              ),
                                            ),
                                          ),
                                        ],
                                      );
                              },
                            ),
                          ],
                        ),
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
