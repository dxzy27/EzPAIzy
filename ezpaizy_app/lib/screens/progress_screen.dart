import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../services/api_service.dart';

class ProgressScreen extends StatefulWidget {
  const ProgressScreen({super.key});

  @override
  State<ProgressScreen> createState() => _ProgressScreenState();
}

class _ProgressScreenState extends State<ProgressScreen> {
  List<dynamic> progress = [];
  bool loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => loading = true);
    try {
      progress = await ApiService.getProgress();
    } catch (_) {}
    setState(() => loading = false);
  }

  double get avgScore {
    final graded = progress
        .where((p) => p['quiz']?['difficulty'] != 'hard' || p['status'] == 'graded')
        .where((p) => (p['score'] ?? 0) > 0)
        .toList();
    if (graded.isEmpty) return 0;
    final sum =
        graded.fold<double>(0, (acc, p) => acc + (p['score'] ?? 0).toDouble());
    return sum / graded.length;
  }

  int get highestScore {
    if (progress.isEmpty) return 0;
    return progress.fold<int>(
        0, (max, p) => (p['score'] ?? 0) > max ? p['score'] : max);
  }

  Color _scoreColor(int score) {
    if (score >= 70) return Colors.green;
    if (score >= 50) return Colors.orange;
    return Colors.red;
  }

  String _statusLabel(Map<String, dynamic> p) {
    final diff = p['quiz']?['difficulty'];
    final status = p['status'];
    if (diff == 'hard' && status == 'pending') return 'Pending Review';
    if (diff == 'hard' && status == 'graded') return 'Graded';
    final score = p['score'] ?? 0;
    if (score >= 70) return 'Passed';
    if (score >= 50) return 'Average';
    return 'Failed';
  }

  Color _statusColor(Map<String, dynamic> p) {
    final label = _statusLabel(p);
    switch (label) {
      case 'Passed':
        return Colors.green;
      case 'Average':
        return Colors.orange;
      case 'Pending Review':
        return Colors.grey;
      case 'Graded':
        return Colors.blue;
      default:
        return Colors.red;
    }
  }

  Future<void> _showDetail(BuildContext context, Map<String, dynamic> p) async {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _DetailSheet(progressId: p['id']),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Progress'),
        actions: [
          IconButton(
            icon: const Icon(Icons.star, color: Colors.amber),
            onPressed: () => context.go('/revision'),
          )
        ],
      ),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : progress.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.bar_chart, size: 64, color: Colors.grey),
                      const SizedBox(height: 12),
                      const Text('No quizzes completed yet',
                          style: TextStyle(color: Colors.grey)),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: () => context.go('/quizzes'),
                        child: const Text('Take a Quiz'),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Stats Row
                        Row(
                          children: [
                            _statCard('Total', '${progress.length}', Colors.blue,
                                Icons.quiz),
                            const SizedBox(width: 10),
                            _statCard('Average',
                                '${avgScore.toStringAsFixed(1)}%', Colors.teal,
                                Icons.trending_up),
                            const SizedBox(width: 10),
                            _statCard(
                                'Highest', '$highestScore%', Colors.green, Icons.emoji_events),
                          ],
                        ),
                        const SizedBox(height: 24),

                        const Text('Quiz History',
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 10),

                        ...progress.map((p) {
                          final score = p['score'] ?? 0;
                          final isHard = p['quiz']?['difficulty'] == 'hard';
                          final isPending = isHard && p['status'] == 'pending';
                          final isGraded = isHard && p['status'] == 'graded';

                          return Card(
                            margin: const EdgeInsets.only(bottom: 10),
                            child: Padding(
                              padding: const EdgeInsets.all(14),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              p['quiz']?['title'] ?? 'Quiz',
                                              style: const TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 14),
                                            ),
                                            if (isHard)
                                              Container(
                                                margin: const EdgeInsets.only(top: 3),
                                                padding: const EdgeInsets.symmetric(
                                                    horizontal: 6, vertical: 2),
                                                decoration: BoxDecoration(
                                                  color: Colors.deepPurple.withOpacity(0.1),
                                                  borderRadius: BorderRadius.circular(4),
                                                ),
                                                child: const Text('KBAT',
                                                    style: TextStyle(
                                                        fontSize: 10,
                                                        color: Colors.deepPurple,
                                                        fontWeight: FontWeight.bold)),
                                              ),
                                          ],
                                        ),
                                      ),
                                      Chip(
                                        label: Text(
                                          _statusLabel(p),
                                          style: const TextStyle(
                                              color: Colors.white, fontSize: 11),
                                        ),
                                        backgroundColor: _statusColor(p),
                                        padding: EdgeInsets.zero,
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 6),
                                  Row(
                                    children: [
                                      const Icon(Icons.person_outline,
                                          size: 13, color: Colors.grey),
                                      const SizedBox(width: 4),
                                      Text(
                                        p['quiz']?['teacher']?['name'] ?? 'Teacher',
                                        style: const TextStyle(
                                            fontSize: 12, color: Colors.grey),
                                      ),
                                      const Spacer(),
                                      if (isPending)
                                        const Text('Awaiting Grade',
                                            style: TextStyle(
                                                color: Colors.grey,
                                                fontStyle: FontStyle.italic,
                                                fontSize: 12))
                                      else
                                        Text(
                                          '$score%',
                                          style: TextStyle(
                                            fontWeight: FontWeight.bold,
                                            color: _scoreColor(score),
                                            fontSize: 16,
                                          ),
                                        ),
                                    ],
                                  ),
                                  if (!isPending) ...[
                                    const SizedBox(height: 8),
                                    LinearProgressIndicator(
                                      value: score / 100,
                                      backgroundColor: Colors.grey.shade200,
                                      color: _scoreColor(score),
                                    ),
                                  ],
                                  // View Details button
                                  const SizedBox(height: 10),
                                  SizedBox(
                                    width: double.infinity,
                                    child: OutlinedButton.icon(
                                      onPressed: () => _showDetail(context, p),
                                      icon: const Icon(Icons.visibility_outlined, size: 16),
                                      label: Text(
                                        isGraded
                                            ? 'View Details & Feedback'
                                            : 'View Answers',
                                        style: const TextStyle(fontSize: 13),
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
    );
  }

  Widget _statCard(String label, String value, Color color, IconData icon) {
    return Expanded(
      child: Card(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
          child: Column(
            children: [
              Icon(icon, color: color, size: 22),
              const SizedBox(height: 4),
              Text(value,
                  style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: color)),
              Text(label,
                  style: const TextStyle(fontSize: 11, color: Colors.grey)),
            ],
          ),
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────
// Detail Bottom Sheet
// ─────────────────────────────────────────────
class _DetailSheet extends StatefulWidget {
  final int progressId;
  const _DetailSheet({required this.progressId});

  @override
  State<_DetailSheet> createState() => _DetailSheetState();
}

class _DetailSheetState extends State<_DetailSheet> {
  Map<String, dynamic>? detail;
  bool loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final d = await ApiService.getProgressDetail(widget.progressId);
    setState(() {
      detail = d;
      loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      initialChildSize: 0.9,
      maxChildSize: 0.95,
      minChildSize: 0.5,
      builder: (_, controller) => Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
        child: loading
            ? const Center(child: CircularProgressIndicator())
            : detail == null || detail!.isEmpty
                ? const Center(child: Text('Failed to load details'))
                : _buildContent(controller),
      ),
    );
  }

  Widget _buildContent(ScrollController controller) {
    final questions = (detail!['quiz']?['questions'] as List?) ?? [];
    final answers = detail!['student_answers'] as Map<String, dynamic>? ?? {};
    final teacherNotes = detail!['teacher_notes'] as Map<String, dynamic>? ?? {};
    final overallComment = teacherNotes['overall_comment'] as String?;
    final isHard = detail!['quiz']?['difficulty'] == 'hard';

    return Column(
      children: [
        // Handle
        Container(
          margin: const EdgeInsets.only(top: 12, bottom: 8),
          width: 40,
          height: 4,
          decoration: BoxDecoration(
            color: Colors.grey.shade300,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        // Title bar
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          child: Row(
            children: [
              Expanded(
                child: Text(
                  detail!['quiz']?['title'] ?? 'Quiz Results',
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ),
              IconButton(
                onPressed: () => Navigator.pop(context),
                icon: const Icon(Icons.close),
              ),
            ],
          ),
        ),
        const Divider(height: 1),
        // Scrollable body
        Expanded(
          child: ListView(
            controller: controller,
            padding: const EdgeInsets.all(16),
            children: [
              // Teacher's overall comment (highlighted) — only if exists
              if (overallComment != null && overallComment.isNotEmpty) ...[
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: const Color(0xFFe8f4fd),
                    borderRadius: BorderRadius.circular(10),
                    border: const Border(
                      left: BorderSide(color: Color(0xFF0d6efd), width: 4),
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.chat_bubble_outline,
                              color: Color(0xFF0d6efd), size: 16),
                          SizedBox(width: 6),
                          Text(
                            "Teacher's Overall Comment",
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF0d6efd),
                              fontSize: 13,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(overallComment,
                          style: const TextStyle(fontSize: 14, height: 1.5)),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
              ],

              // Questions
              ...questions.asMap().entries.map((entry) {
                final index = entry.key;
                final q = entry.value as Map<String, dynamic>;
                final studentAns = answers[index.toString()];
                final correctAns = q['correct_answer'];
                final options = q['options'] as Map<String, dynamic>?;

                String studentAnsDisplay = studentAns ?? 'No answer provided';
                if (options != null && studentAns != null && options.containsKey(studentAns)) {
                  studentAnsDisplay =
                      '${studentAns.toString().toUpperCase()}: ${options[studentAns]}';
                }

                String correctAnsDisplay = correctAns ?? '—';
                if (options != null && correctAns != null && options.containsKey(correctAns)) {
                  correctAnsDisplay =
                      '${correctAns.toString().toUpperCase()}: ${options[correctAns]}';
                }

                final isWrong = !isHard &&
                    studentAns != null &&
                    correctAns != null &&
                    studentAns.toString().toLowerCase() !=
                        correctAns.toString().toLowerCase();

                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                    side: isWrong
                        ? const BorderSide(color: Colors.red, width: 1)
                        : BorderSide.none,
                  ),
                  child: Padding(
                    padding: const EdgeInsets.all(14),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Question text
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            if (isWrong)
                              const Text('● ',
                                  style: TextStyle(
                                      color: Colors.red, fontWeight: FontWeight.bold)),
                            Expanded(
                              child: Text(
                                'Q${index + 1}: ${q['question_text'] ?? ''}',
                                style: const TextStyle(
                                    fontWeight: FontWeight.w600, fontSize: 14),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),

                        // Student answer
                        _answerBox(
                          label: 'YOUR ANSWER',
                          text: studentAnsDisplay,
                          labelColor: Colors.blue.shade700,
                          bgColor: Colors.blue.shade50,
                        ),
                        const SizedBox(height: 8),

                        // Correct answer / key points
                        _answerBox(
                          label: isHard ? 'SUGGESTED ANSWER / KEY POINTS' : 'CORRECT ANSWER',
                          text: correctAnsDisplay,
                          labelColor: Colors.green.shade700,
                          bgColor: Colors.green.shade50,
                        ),
                      ],
                    ),
                  ),
                );
              }),
            ],
          ),
        ),
      ],
    );
  }

  Widget _answerBox({
    required String label,
    required String text,
    required Color labelColor,
    required Color bgColor,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label,
              style: TextStyle(
                  fontSize: 10,
                  fontWeight: FontWeight.bold,
                  color: labelColor,
                  letterSpacing: 0.5)),
          const SizedBox(height: 4),
          Text(text,
              style: const TextStyle(fontSize: 13, height: 1.4)),
        ],
      ),
    );
  }
}
