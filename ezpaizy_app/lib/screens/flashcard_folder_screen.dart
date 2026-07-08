import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../services/api_service.dart';

class FlashcardFolderScreen extends StatefulWidget {
  final String topic;

  const FlashcardFolderScreen({super.key, required this.topic});

  @override
  State<FlashcardFolderScreen> createState() => _FlashcardFolderScreenState();
}

class _FlashcardFolderScreenState extends State<FlashcardFolderScreen> {
  List<dynamic> sets = [];
  bool loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => loading = true);
    try {
      final allSets = await ApiService.getFlashcards();
      sets = allSets.where((s) {
        final t = s['topic']?.toString().trim();
        return t == widget.topic || (widget.topic == 'General' && (t == null || t.isEmpty));
      }).toList();
    } catch (_) {}
    setState(() => loading = false);
  }

  Future<void> _toggleFavorite(Map<String, dynamic> item) async {
    final isFav = item['is_favorited'] == true;
    setState(() => item['is_favorited'] = !isFav);
    try {
      if (isFav) {
        await ApiService.removeFlashcardFavorite(item['id']);
      } else {
        await ApiService.addFlashcardFavorite(item['id']);
      }
    } catch (_) {
      setState(() => item['is_favorited'] = isFav);
    }
  }

  Widget _buildMasteryBar(Map<String, dynamic>? stats) {
    if (stats == null) return const SizedBox();
    final total = stats['total'] ?? 0;
    if (total == 0) return const SizedBox();

    final mastered = stats['mastered'] ?? 0;
    final review = stats['review'] ?? 0;
    final learning = stats['learning'] ?? 0;
    
    // new isn't shown in the bar typically, it's just the remainder

    return Container(
      height: 6,
      width: double.infinity,
      decoration: BoxDecoration(
        color: Colors.grey.shade200,
        borderRadius: BorderRadius.circular(3),
      ),
      child: Row(
        children: [
          if (mastered > 0)
            Expanded(
              flex: mastered,
              child: Container(
                decoration: const BoxDecoration(
                  color: Colors.green,
                  borderRadius: BorderRadius.horizontal(left: Radius.circular(3)),
                ),
              ),
            ),
          if (review > 0)
            Expanded(
              flex: review,
              child: Container(color: Colors.orange),
            ),
          if (learning > 0)
            Expanded(
              flex: learning,
              child: Container(color: Colors.red),
            ),
          if (total - (mastered + review + learning) > 0)
            Expanded(
              flex: total - (mastered + review + learning),
              child: const SizedBox(),
            ),
        ],
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
        actions: [
          IconButton(
            icon: const Icon(Icons.star, color: Colors.amber),
            tooltip: 'My Revision',
            onPressed: () => context.go('/revision'),
          ),
        ],
      ),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : sets.isEmpty
              ? const Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.style, size: 64, color: Colors.grey),
                      SizedBox(height: 12),
                      Text('No flashcard sets in this folder',
                          style: TextStyle(color: Colors.grey)),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: sets.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 16),
                    itemBuilder: (_, i) {
                      final s = sets[i];
                      final cards = (s['flashcards'] as List?)?.length ?? 0;
                      final stats = s['stats'];
                      final mastered = stats?['mastered'] ?? 0;
                      final review = stats?['review'] ?? 0;
                      final learning = stats?['learning'] ?? 0;

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
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      s['title'] ?? '',
                                      style: const TextStyle(
                                          fontWeight: FontWeight.bold,
                                          fontSize: 16),
                                    ),
                                  ),
                                  IconButton(
                                    icon: Icon(
                                      s['is_favorited'] == true
                                          ? Icons.star
                                          : Icons.star_border,
                                      color: Colors.amber,
                                    ),
                                    onPressed: () => _toggleFavorite(s),
                                  ),
                                ],
                              ),
                              Row(
                                children: [
                                  const Icon(Icons.credit_card,
                                      size: 16, color: Colors.grey),
                                  const SizedBox(width: 4),
                                  Text('$cards cards',
                                      style: const TextStyle(
                                          fontSize: 13, color: Colors.grey)),
                                ],
                              ),
                              if (stats != null && cards > 0) ...[
                                const SizedBox(height: 16),
                                _buildMasteryBar(stats),
                                const SizedBox(height: 8),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    _StatItem(color: Colors.green, label: 'Mastered', count: mastered),
                                    _StatItem(color: Colors.orange, label: 'Review', count: review),
                                    _StatItem(color: Colors.red, label: 'Learning', count: learning),
                                  ],
                                ),
                              ],
                              const SizedBox(height: 16),
                              Row(
                                children: [
                                  Expanded(
                                    child: OutlinedButton.icon(
                                      onPressed: cards > 0
                                          ? () => context.push('/flashcards/${s['id']}/practice')
                                          : null,
                                      icon: const Icon(Icons.psychology, size: 18),
                                      label: const Text('Practice'),
                                      style: OutlinedButton.styleFrom(
                                        padding: const EdgeInsets.symmetric(vertical: 12),
                                        shape: RoundedRectangleBorder(
                                          borderRadius: BorderRadius.circular(12),
                                        ),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: ElevatedButton.icon(
                                      onPressed: cards > 0
                                          ? () => context.push('/flashcards/${s['id']}')
                                          : null,
                                      icon: const Icon(Icons.menu_book, size: 18),
                                      label: const Text('Read'),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Theme.of(context).primaryColor,
                                        foregroundColor: Colors.white,
                                        padding: const EdgeInsets.symmetric(vertical: 12),
                                        shape: RoundedRectangleBorder(
                                          borderRadius: BorderRadius.circular(12),
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}

class _StatItem extends StatelessWidget {
  final Color color;
  final String label;
  final int count;

  const _StatItem({required this.color, required this.label, required this.count});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(width: 8, height: 8, decoration: BoxDecoration(color: color, shape: BoxShape.circle)),
        const SizedBox(width: 4),
        Text('$label: $count', style: const TextStyle(fontSize: 11, color: Colors.grey)),
      ],
    );
  }
}
