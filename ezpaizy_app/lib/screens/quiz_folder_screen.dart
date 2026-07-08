import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../services/api_service.dart';

class QuizFolderScreen extends StatefulWidget {
  final String topic;

  const QuizFolderScreen({super.key, required this.topic});

  @override
  State<QuizFolderScreen> createState() => _QuizFolderScreenState();
}

class _QuizFolderScreenState extends State<QuizFolderScreen> {
  List<dynamic> quizzes = [];
  bool loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => loading = true);
    try {
      final allQuizzes = await ApiService.getQuizzes();
      quizzes = allQuizzes.where((q) {
        final t = q['topic']?.toString().trim();
        return t == widget.topic || (widget.topic == 'General' && (t == null || t.isEmpty));
      }).toList();
    } catch (_) {}
    setState(() => loading = false);
  }

  Color _diffColor(String? diff) {
    switch (diff) {
      case 'easy':
        return Colors.green;
      case 'medium':
        return Colors.orange;
      case 'hard':
        return Colors.red;
      default:
        return Colors.blue;
    }
  }

  Widget _buildProgressPill(dynamic quiz) {
    final progressList = quiz['progress'] as List?;
    if (progressList == null || progressList.isEmpty) {
      return const SizedBox();
    }
    final progress = progressList.first;
    final score = progress['score'] ?? 0;
    final status = progress['status'] ?? 'pending';

    Color bgColor = Colors.grey.shade100;
    Color textColor = Colors.grey.shade700;
    String text = '$score%';

    if (status == 'completed') {
      if (score >= 80) {
        bgColor = Colors.green.shade50;
        textColor = Colors.green.shade700;
      } else {
        bgColor = Colors.red.shade50;
        textColor = Colors.red.shade700;
      }
    } else {
      bgColor = Colors.orange.shade50;
      textColor = Colors.orange.shade700;
      text = 'Pending';
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        text,
        style: TextStyle(
          color: textColor,
          fontSize: 12,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.topic),
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 0,
      ),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : quizzes.isEmpty
              ? const Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.quiz, size: 64, color: Colors.grey),
                      SizedBox(height: 12),
                      Text('No quizzes in this folder',
                          style: TextStyle(color: Colors.grey)),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: quizzes.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 16),
                    itemBuilder: (_, i) {
                      final q = quizzes[i];
                      final diff = q['difficulty'] ?? 'easy';
                      final count = q['questions_count'] ?? 0;
                      final isLocked = q['is_locked'] == true;

                      return Container(
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: Colors.grey.shade200),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.02),
                              blurRadius: 8,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: Opacity(
                          opacity: isLocked ? 0.6 : 1.0,
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Expanded(
                                      child: Text(
                                        q['title'] ?? '',
                                        style: const TextStyle(
                                            fontWeight: FontWeight.bold,
                                            fontSize: 16),
                                      ),
                                    ),
                                    _buildProgressPill(q),
                                    const SizedBox(width: 8),
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                          horizontal: 8, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: _diffColor(diff).withOpacity(0.1),
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: Text(
                                        diff[0].toUpperCase() + diff.substring(1),
                                        style: TextStyle(
                                          color: _diffColor(diff),
                                          fontSize: 12,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 8),
                                Row(
                                  children: [
                                    const Icon(Icons.person_outline,
                                        size: 16, color: Colors.grey),
                                    const SizedBox(width: 4),
                                    Text(
                                      q['teacher']?['name'] ?? 'Teacher',
                                      style: const TextStyle(
                                          fontSize: 13, color: Colors.grey),
                                    ),
                                    const SizedBox(width: 16),
                                    const Icon(Icons.help_outline,
                                        size: 16, color: Colors.grey),
                                    const SizedBox(width: 4),
                                    Text(
                                      '$count question${count != 1 ? 's' : ''}',
                                      style: const TextStyle(
                                          fontSize: 13, color: Colors.grey),
                                    ),
                                  ],
                                ),
                                if (isLocked) ...[
                                  const SizedBox(height: 12),
                                  Container(
                                    padding: const EdgeInsets.all(8),
                                    decoration: BoxDecoration(
                                      color: Colors.amber.shade50,
                                      borderRadius: BorderRadius.circular(8),
                                      border: Border.all(color: Colors.amber.shade200),
                                    ),
                                    child: Row(
                                      children: [
                                        Icon(Icons.lock, size: 16, color: Colors.amber.shade700),
                                        const SizedBox(width: 8),
                                        Expanded(
                                          child: Text(
                                            diff == 'medium'
                                                ? 'Pass all Easy quizzes in this topic with 80% to unlock.'
                                                : 'Pass all Easy & Medium quizzes in this topic with 80% to unlock.',
                                            style: TextStyle(
                                                fontSize: 12,
                                                color: Colors.amber.shade900),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                                const SizedBox(height: 16),
                                SizedBox(
                                  width: double.infinity,
                                  child: ElevatedButton.icon(
                                    onPressed: (count > 0 && !isLocked)
                                        ? () => context.push('/quiz/${q['id']}')
                                        : null,
                                    icon: Icon(isLocked ? Icons.lock : Icons.play_arrow),
                                    label: Text(isLocked 
                                        ? 'Locked' 
                                        : (count > 0 ? 'Take Quiz' : 'No Questions Yet')),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: isLocked ? Colors.grey.shade300 : Theme.of(context).primaryColor,
                                      foregroundColor: isLocked ? Colors.grey.shade600 : Colors.white,
                                      padding: const EdgeInsets.symmetric(vertical: 12),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}
