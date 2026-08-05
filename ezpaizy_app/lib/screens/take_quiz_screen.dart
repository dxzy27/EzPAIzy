import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import '../widgets/study_notepad_widget.dart';
import '../services/tts_service.dart';

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

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    TtsService.stop();
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
                                      setState(() => currentPage--);
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
                                          setState(() => currentPage++);
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
              child: Text(
                q['question_text'] ?? '',
                style: const TextStyle(
                  fontSize: 24, // Matches large web title size
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF0F172A),
                  fontFamily: 'Outfit',
                  height: 1.45,
                ),
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
            return GestureDetector(
              onTap: () => setState(() => answers[index] = entry.key),
              child: Container(
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
                      child: Text(
                        entry.value ?? '',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w500,
                          color: Color(0xFF1E293B),
                          fontFamily: 'Outfit',
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          })
        else
          TextField(
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
    // KBAT quiz — pending teacher grading
    if (_isPending) {
      return Scaffold(
        appBar: AppBar(title: const Text('Quiz Submitted')),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.hourglass_top_rounded,
                    size: 80, color: Colors.deepPurple),
                const SizedBox(height: 20),
                const Text(
                  'Submitted for Review',
                  style: TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                      color: Colors.deepPurple),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 12),
                const Text(
                  'This is a KBAT (Higher Order Thinking) quiz.\nYour teacher will review your answers and assign a grade.',
                  style: TextStyle(fontSize: 14, color: Colors.grey, height: 1.5),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 32),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () => context.go('/progress'),
                    child: const Text('View My Progress'),
                  ),
                ),
                const SizedBox(height: 12),
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton(
                    onPressed: () => context.go('/quizzes'),
                    child: const Text('Back to Quizzes'),
                  ),
                ),
              ],
            ),
          ),
        ),
      );
    }

    // Normal quiz result
    final score = result ?? 0;
    final passed = score >= 70;
    return Scaffold(
      appBar: AppBar(title: const Text('Quiz Result')),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                passed ? Icons.emoji_events : Icons.sentiment_dissatisfied,
                size: 80,
                color: passed ? Colors.amber : Colors.grey,
              ),
              const SizedBox(height: 20),
              Text(
                '$score%',
                style: TextStyle(
                  fontSize: 64,
                  fontWeight: FontWeight.bold,
                  color: passed ? Colors.green : Colors.red,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                passed ? '🎉 Well done!' : 'Keep practicing!',
                style: const TextStyle(fontSize: 18, color: Colors.grey),
              ),
              const SizedBox(height: 32),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () => context.go('/quizzes'),
                  child: const Text('Back to Quizzes'),
                ),
              ),
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton(
                  onPressed: () => context.go('/progress'),
                  child: const Text('View Progress'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
