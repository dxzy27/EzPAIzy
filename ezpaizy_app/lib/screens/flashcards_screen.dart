import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../services/api_service.dart';

class FlashcardsScreen extends StatefulWidget {
  const FlashcardsScreen({super.key});

  @override
  State<FlashcardsScreen> createState() => _FlashcardsScreenState();
}

class _FlashcardsScreenState extends State<FlashcardsScreen> {
  List<dynamic> sets = [];
  String? selectedTopic;
  bool loading = true;

  List<String> get topics {
    final t = sets.map((s) => s['topic'] as String? ?? 'General').toSet().toList();
    t.sort();
    return t;
  }

  List<dynamic> get filtered => selectedTopic == null
      ? sets
      : sets.where((s) => s['topic'] == selectedTopic).toList();

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => loading = true);
    try {
      sets = await ApiService.getFlashcards();
    } catch (_) {}
    setState(() => loading = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Flashcards')),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                // Topic filter chips
                if (topics.isNotEmpty)
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    padding: const EdgeInsets.symmetric(
                        horizontal: 12, vertical: 8),
                    child: Row(
                      children: [
                        FilterChip(
                          label: const Text('All'),
                          selected: selectedTopic == null,
                          onSelected: (_) =>
                              setState(() => selectedTopic = null),
                        ),
                        const SizedBox(width: 8),
                        ...topics.map((t) => Padding(
                              padding: const EdgeInsets.only(right: 8),
                              child: FilterChip(
                                label: Text(t),
                                selected: selectedTopic == t,
                                onSelected: (_) =>
                                    setState(() => selectedTopic = t),
                              ),
                            )),
                      ],
                    ),
                  ),

                Expanded(
                  child: filtered.isEmpty
                      ? const Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.style, size: 64, color: Colors.grey),
                              SizedBox(height: 12),
                              Text('No flashcard sets found',
                                  style: TextStyle(color: Colors.grey)),
                            ],
                          ),
                        )
                      : RefreshIndicator(
                          onRefresh: _load,
                          child: GridView.builder(
                            padding: const EdgeInsets.all(12),
                            gridDelegate:
                                const SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: 2,
                              crossAxisSpacing: 10,
                              mainAxisSpacing: 10,
                              childAspectRatio: 0.85,
                            ),
                            itemCount: filtered.length,
                            itemBuilder: (_, i) {
                              final s = filtered[i];
                              final cards =
                                  (s['flashcards'] as List?)?.length ?? 0;
                              return GestureDetector(
                                onTap: () {
                                  context.push('/flashcards/${s['id']}');
                                },
                                child: Card(
                                  child: Padding(
                                    padding: const EdgeInsets.all(14),
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        const Icon(Icons.style,
                                            color: Colors.purple, size: 32),
                                        const SizedBox(height: 10),
                                        Text(
                                          s['title'] ?? '',
                                          style: const TextStyle(
                                              fontWeight: FontWeight.bold,
                                              fontSize: 14),
                                          maxLines: 2,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                        const Spacer(),
                                        if (s['topic'] != null)
                                          Chip(
                                            label: Text(s['topic'],
                                                style: const TextStyle(
                                                    fontSize: 10)),
                                            backgroundColor:
                                                Colors.blue.shade50,
                                            padding: EdgeInsets.zero,
                                          ),
                                        const SizedBox(height: 6),
                                        Row(
                                          children: [
                                            const Icon(Icons.credit_card,
                                                size: 13, color: Colors.grey),
                                            const SizedBox(width: 4),
                                            Text('$cards cards',
                                                style: const TextStyle(
                                                    fontSize: 11,
                                                    color: Colors.grey)),
                                          ],
                                        ),
                                      ],
                                    ),
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
