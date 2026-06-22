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
        .where((p) => p['quiz']?['difficulty'] != 'hard')
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
    if (diff == 'hard' && status == 'pending') return 'Not Graded';
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
      case 'Not Graded':
        return Colors.grey;
      case 'Graded':
        return Colors.blue;
      default:
        return Colors.red;
    }
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
                          final isPending =
                              p['quiz']?['difficulty'] == 'hard' &&
                                  p['status'] == 'pending';
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
                                        child: Text(
                                          p['quiz']?['title'] ?? 'Quiz',
                                          style: const TextStyle(
                                              fontWeight: FontWeight.bold,
                                              fontSize: 14),
                                        ),
                                      ),
                                      Chip(
                                        label: Text(
                                          _statusLabel(p),
                                          style: const TextStyle(
                                              color: Colors.white,
                                              fontSize: 11),
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
                                        p['quiz']?['teacher']?['name'] ??
                                            'Teacher',
                                        style: const TextStyle(
                                            fontSize: 12, color: Colors.grey),
                                      ),
                                      const Spacer(),
                                      if (!isPending)
                                        Text(
                                          '$score%',
                                          style: TextStyle(
                                            fontWeight: FontWeight.bold,
                                            color: _scoreColor(score),
                                            fontSize: 16,
                                          ),
                                        )
                                      else
                                        const Text('Pending Review',
                                            style: TextStyle(
                                                color: Colors.grey,
                                                fontStyle: FontStyle.italic)),
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
