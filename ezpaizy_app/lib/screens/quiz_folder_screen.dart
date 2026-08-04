import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';

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
      // Filter quizzes by current folder topic
      quizzes = allQuizzes.where((q) {
        final t = q['topic']?.toString().trim();
        return t == widget.topic || (widget.topic == 'General' && (t == null || t.isEmpty));
      }).toList();
    } catch (_) {}
    setState(() => loading = false);
  }

  Color _diffColor(String? diff) {
    switch (diff?.toLowerCase()) {
      case 'easy':
        return const Color(0xFF22C55E); // green
      case 'medium':
        return const Color(0xFFF59E0B); // orange
      case 'hard':
        return const Color(0xFFEF4444); // red
      default:
        return const Color(0xFF3B82F6);
    }
  }

  Color _diffBgColor(String? diff) {
    switch (diff?.toLowerCase()) {
      case 'easy':
        return const Color(0xFFDCFCE7); // light green
      case 'medium':
        return const Color(0xFFFEF3C7); // light orange
      case 'hard':
        return const Color(0xFFFEE2E2); // light red
      default:
        return const Color(0xFFDBEAFE);
    }
  }

  void _onTakeQuiz(Map<String, dynamic> q) {
    final style = Provider.of<AuthProvider>(context, listen: false).user?['learning_style']?.toString().toLowerCase();

    if (style == 'kinesthetic') {
      context.push('/quiz/${q['id']}/practice');
    } else {
      context.push('/quiz/${q['id']}');
    }
  }

  @override
  Widget build(BuildContext context) {
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
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // AppBar Header Row
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  child: Row(
                    children: [
                      IconButton(
                        icon: const Icon(Icons.arrow_back, color: Color(0xFF1E293B)),
                        onPressed: () => context.pop(),
                      ),
                      const SizedBox(width: 8),
                      // Folder Icon and Topic
                      const Icon(Icons.folder, color: Color(0xFFFFC107), size: 28),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          widget.topic,
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.w900,
                            color: Color(0xFF1E293B),
                            fontFamily: 'Outfit',
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ),

                // Subtitle path helper
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 56),
                  child: Text(
                    'Quizzes / ${widget.topic}',
                    style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), fontFamily: 'Outfit'),
                  ),
                ),
                const SizedBox(height: 16),

                // Responsive Grid of Quiz cards (unlocked structure matching web)
                Expanded(
                  child: loading
                      ? const Center(child: CircularProgressIndicator())
                      : quizzes.isEmpty
                          ? const Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.quiz, size: 64, color: Color(0xFFCBD5E1)),
                                  SizedBox(height: 12),
                                  Text(
                                    'No quizzes in this folder',
                                    style: TextStyle(color: Color(0xFF64748B), fontFamily: 'Outfit'),
                                  ),
                                ],
                              ),
                            )
                          : RefreshIndicator(
                              onRefresh: _load,
                              child: GridView.builder(
                                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 8),
                                gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
                                  maxCrossAxisExtent: 280,
                                  crossAxisSpacing: 16,
                                  mainAxisSpacing: 16,
                                  mainAxisExtent: 250, // Fixed height for dynamic clean cards
                                ),
                                itemCount: quizzes.length,
                                itemBuilder: (context, i) {
                                  final q = quizzes[i];
                                  final diff = q['difficulty'] ?? 'easy';
                                  final count = q['questions_count'] ?? 0;
                                  final progressList = q['progress'] as List?;
                                  
                                  // Find best attempt
                                  Map<String, dynamic>? bestProgress;
                                  if (progressList != null && progressList.isNotEmpty) {
                                    int bestScore = -1;
                                    for (var item in progressList) {
                                      final score = item['score'] ?? 0;
                                      if (score > bestScore) {
                                        bestScore = score;
                                        bestProgress = Map<String, dynamic>.from(item);
                                      }
                                    }
                                  }

                                  final hasAttempted = bestProgress != null;
                                  final scoreVal = bestProgress?['score'] ?? 0;
                                  final statusVal = bestProgress?['status'] ?? 'pending';

                                  return Container(
                                    decoration: BoxDecoration(
                                      color: Colors.white,
                                      borderRadius: BorderRadius.circular(16),
                                      border: Border.all(color: const Color(0xFFE2E8F0)),
                                      boxShadow: [
                                        BoxShadow(
                                          color: Colors.black.withOpacity(0.02),
                                          blurRadius: 8,
                                          offset: const Offset(0, 4),
                                        ),
                                      ],
                                    ),
                                    padding: const EdgeInsets.all(12),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        // Header Difficulty Pill & Star Row
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                              decoration: BoxDecoration(
                                                color: _diffBgColor(diff),
                                                borderRadius: BorderRadius.circular(12),
                                              ),
                                              child: Text(
                                                diff.toString().toUpperCase(),
                                                style: TextStyle(
                                                  color: _diffColor(diff),
                                                  fontSize: 9,
                                                  fontWeight: FontWeight.w900,
                                                  fontFamily: 'Outfit',
                                                ),
                                              ),
                                            ),
                                            const Icon(
                                              Icons.star_border,
                                              color: Color(0xFFCBD5E1),
                                              size: 20,
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 10),

                                        // Title
                                        Text(
                                          q['title'] ?? '',
                                          style: const TextStyle(
                                            fontWeight: FontWeight.w900,
                                            fontSize: 14,
                                            color: Color(0xFF0F172A),
                                            fontFamily: 'Outfit',
                                          ),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                        const SizedBox(height: 2),

                                        // Teacher
                                        Row(
                                          children: [
                                            const Icon(Icons.person, size: 12, color: Color(0xFF94A3B8)),
                                            const SizedBox(width: 4),
                                            Text(
                                              'By: ${q['teacher']?['name'] ?? 'Teacher'}',
                                              style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontFamily: 'Outfit'),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 12),

                                        // Score Row matching Web Card
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            const Text(
                                              'Best Score',
                                              style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8), fontFamily: 'Outfit'),
                                            ),
                                            Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                              decoration: BoxDecoration(
                                                color: hasAttempted 
                                                    ? (statusVal == 'completed' ? const Color(0xFFE6F4EA) : const Color(0xFFFFF4E5))
                                                    : const Color(0xFFF1F5F9),
                                                borderRadius: BorderRadius.circular(4),
                                              ),
                                              child: Text(
                                                hasAttempted ? '$scoreVal%' : 'NOT STARTED',
                                                style: TextStyle(
                                                  fontSize: 9,
                                                  fontWeight: FontWeight.bold,
                                                  color: hasAttempted 
                                                      ? (statusVal == 'completed' ? const Color(0xFF137333) : const Color(0xFFB06000))
                                                      : const Color(0xFF64748B),
                                                  fontFamily: 'Outfit',
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          hasAttempted 
                                              ? (statusVal == 'completed' ? 'Successfully completed' : 'Grading pending') 
                                              : 'Not attempted yet',
                                          style: const TextStyle(fontSize: 10, color: Color(0xFF94A3B8), fontFamily: 'Outfit'),
                                        ),
                                        const Spacer(),

                                        // Stats Row (Questions and Category pill)
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            Row(
                                              children: [
                                                const Icon(Icons.help_outline, size: 12, color: Color(0xFF94A3B8)),
                                                const SizedBox(width: 4),
                                                Text(
                                                  '$count Questions',
                                                  style: const TextStyle(fontSize: 10, color: Color(0xFF64748B), fontFamily: 'Outfit'),
                                                ),
                                              ],
                                            ),
                                            Text(
                                              widget.topic.toUpperCase(),
                                              style: const TextStyle(
                                                fontSize: 9, 
                                                fontWeight: FontWeight.bold, 
                                                color: Color(0xFFD946EF), // Fuchsia matching web category
                                                fontFamily: 'Outfit',
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 10),

                                        // Take Quiz Button (Teal/Green style matching web)
                                        SizedBox(
                                          width: double.infinity,
                                          height: 32,
                                          child: ElevatedButton(
                                            onPressed: () => _onTakeQuiz(q),
                                            style: ElevatedButton.styleFrom(
                                              backgroundColor: const Color(0xFF0F9D58), // Green matching web button
                                              foregroundColor: Colors.white,
                                              elevation: 0,
                                              padding: EdgeInsets.zero,
                                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                            ),
                                            child: const Row(
                                              mainAxisAlignment: MainAxisAlignment.center,
                                              children: [
                                                Text('Take Quiz', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, fontFamily: 'Outfit')),
                                                SizedBox(width: 4),
                                                Icon(Icons.arrow_forward, size: 10),
                                              ],
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  );
                                },
                              ),
                            ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

