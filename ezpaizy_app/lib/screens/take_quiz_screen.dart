import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../services/api_service.dart';
import '../app/theme.dart';

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

    return Scaffold(
      appBar: AppBar(
        title: Text(quiz!['title'] ?? 'Quiz'),
        leading: IconButton(
          icon: const Icon(Icons.close),
          onPressed: () => context.pop(),
        ),
      ),
      body: Column(
        children: [
          // Progress bar
          LinearProgressIndicator(
            value: (currentPage + 1) / questions.length,
            backgroundColor: Colors.grey.shade200,
            color: AppTheme.primaryLight,
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Question ${currentPage + 1} of ${questions.length}',
                    style: const TextStyle(color: Colors.grey, fontSize: 12)),
                Text('${answers.length} answered',
                    style: const TextStyle(color: Colors.grey, fontSize: 12)),
              ],
            ),
          ),

          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: _buildQuestion(q, currentPage),
            ),
          ),

          // Navigation buttons
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                if (currentPage > 0)
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () =>
                          setState(() => currentPage--),
                      child: const Text('Previous'),
                    ),
                  ),
                if (currentPage > 0) const SizedBox(width: 12),
                Expanded(
                  flex: 2,
                  child: ElevatedButton(
                    onPressed: currentPage < questions.length - 1
                        ? () => setState(() => currentPage++)
                        : _submit,
                    child: Text(
                      currentPage < questions.length - 1 ? 'Next' : 'Submit',
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuestion(Map<String, dynamic> q, int index) {
    final optionsData = q['options'];
    final Map<String, dynamic>? options = (optionsData is Map<String, dynamic>) ? optionsData : null;
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              q['question_text'] ?? '',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
            ),
            const SizedBox(height: 20),
            if (options != null && options.isNotEmpty)
              ...options.entries.map((entry) {
                final selected = answers[index] == entry.key;
                return GestureDetector(
                  onTap: () => setState(() => answers[index] = entry.key),
                  child: Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: selected
                          ? AppTheme.primary.withOpacity(0.1)
                          : Colors.white,
                      border: Border.all(
                        color: selected ? AppTheme.primary : Colors.grey.shade300,
                        width: selected ? 2 : 1,
                      ),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Row(
                      children: [
                        CircleAvatar(
                          radius: 14,
                          backgroundColor: selected
                              ? AppTheme.primary
                              : Colors.grey.shade200,
                          child: Text(
                            entry.key.toUpperCase(),
                            style: TextStyle(
                              color: selected ? Colors.white : Colors.grey,
                              fontWeight: FontWeight.bold,
                              fontSize: 12,
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(child: Text(entry.value ?? '')),
                      ],
                    ),
                  ),
                );
              })
            else
              TextField(
                maxLines: 4,
                onChanged: (val) => answers[index] = val,
                decoration: const InputDecoration(
                  hintText: 'Type your answer here...',
                  border: OutlineInputBorder(),
                ),
              ),
          ],
        ),
      ),
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
