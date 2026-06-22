import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../services/api_service.dart';

class QuizzesScreen extends StatefulWidget {
  const QuizzesScreen({super.key});

  @override
  State<QuizzesScreen> createState() => _QuizzesScreenState();
}

class _QuizzesScreenState extends State<QuizzesScreen> {
  List<dynamic> quizzes = [];
  List<dynamic> filtered = [];
  bool loading = true;
  final _search = TextEditingController();

  @override
  void initState() {
    super.initState();
    _load();
    _search.addListener(_filter);
  }

  @override
  void dispose() {
    _search.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => loading = true);
    try {
      quizzes = await ApiService.getQuizzes();
      filtered = quizzes;
    } catch (_) {}
    setState(() => loading = false);
  }

  void _filter() {
    final q = _search.text.toLowerCase();
    setState(() {
      filtered = quizzes
          .where((quiz) =>
              (quiz['title'] ?? '').toLowerCase().contains(q) ||
              (quiz['topic'] ?? '').toLowerCase().contains(q))
          .toList();
    });
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Available Quizzes')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: TextField(
              controller: _search,
              decoration: InputDecoration(
                hintText: 'Search quizzes...',
                prefixIcon: const Icon(Icons.search),
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide.none),
              ),
            ),
          ),
          Expanded(
            child: loading
                ? const Center(child: CircularProgressIndicator())
                : filtered.isEmpty
                    ? const Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.quiz, size: 64, color: Colors.grey),
                            SizedBox(height: 12),
                            Text('No quizzes found',
                                style: TextStyle(color: Colors.grey)),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: _load,
                        child: ListView.separated(
                          padding: const EdgeInsets.all(12),
                          itemCount: filtered.length,
                          separatorBuilder: (_, _) =>
                              const SizedBox(height: 10),
                          itemBuilder: (_, i) {
                            final q = filtered[i];
                            final diff = q['difficulty'] ?? 'easy';
                            final count = q['questions_count'] ?? 0;
                            return Card(
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
                                                fontSize: 15),
                                          ),
                                        ),
                                        Chip(
                                          label: Text(
                                            diff[0].toUpperCase() +
                                                diff.substring(1),
                                            style: const TextStyle(
                                                color: Colors.white,
                                                fontSize: 11),
                                          ),
                                          backgroundColor: _diffColor(diff),
                                          padding: EdgeInsets.zero,
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 6),
                                    Row(
                                      children: [
                                        const Icon(Icons.person_outline,
                                            size: 14, color: Colors.grey),
                                        const SizedBox(width: 4),
                                        Text(
                                          q['teacher']?['name'] ?? 'Teacher',
                                          style: const TextStyle(
                                              fontSize: 12, color: Colors.grey),
                                        ),
                                        const SizedBox(width: 12),
                                        const Icon(Icons.help_outline,
                                            size: 14, color: Colors.grey),
                                        const SizedBox(width: 4),
                                        Text(
                                          '$count question${count != 1 ? 's' : ''}',
                                          style: const TextStyle(
                                              fontSize: 12, color: Colors.grey),
                                        ),
                                      ],
                                    ),
                                    if (q['topic'] != null) ...[
                                      const SizedBox(height: 6),
                                      Chip(
                                        label: Text(q['topic'],
                                            style:
                                                const TextStyle(fontSize: 11)),
                                        backgroundColor: Colors.blue.shade50,
                                        padding: EdgeInsets.zero,
                                      ),
                                    ],
                                    const SizedBox(height: 12),
                                    SizedBox(
                                      width: double.infinity,
                                      child: ElevatedButton.icon(
                                        onPressed: count > 0
                                            ? () => context.push(
                                                '/quiz/${q['id']}')
                                            : null,
                                        icon: const Icon(Icons.play_arrow),
                                        label: Text(count > 0
                                            ? 'Take Quiz'
                                            : 'No Questions Yet'),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }
}
