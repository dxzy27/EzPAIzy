import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../services/api_service.dart';

class RevisionScreen extends StatefulWidget {
  const RevisionScreen({super.key});

  @override
  State<RevisionScreen> createState() => _RevisionScreenState();
}

class _RevisionScreenState extends State<RevisionScreen> {
  List<dynamic> favorites = [];
  bool loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => loading = true);
    try {
      favorites = await ApiService.getRevision();
    } catch (_) {}
    setState(() => loading = false);
  }

  Future<void> _remove(int index, int contentId) async {
    final removed = favorites[index];
    setState(() => favorites.removeAt(index));
    try {
      await ApiService.removeFavorite(contentId);
    } catch (_) {
      setState(() => favorites.insert(index, removed));
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Failed to remove')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('My Revision List')),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : favorites.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.star_border,
                          size: 64, color: Colors.grey),
                      const SizedBox(height: 12),
                      const Text('No saved materials',
                          style: TextStyle(
                              color: Colors.grey, fontSize: 16)),
                      const SizedBox(height: 8),
                      const Text(
                          'Tap ⭐ on any content or flashcard to save it here',
                          textAlign: TextAlign.center,
                          style: TextStyle(color: Colors.grey, fontSize: 13)),
                      const SizedBox(height: 20),
                      ElevatedButton(
                        onPressed: () => context.go('/contents'),
                        child: const Text('Browse Materials'),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView.separated(
                    padding: const EdgeInsets.all(12),
                    itemCount: favorites.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 10),
                    itemBuilder: (_, i) {
                      final fav = favorites[i];
                      final isContent = fav['content'] != null;
                      final item =
                          isContent ? fav['content'] : fav['flashcard_set'];
                      if (item == null) return const SizedBox.shrink();

                      return Dismissible(
                        key: Key('fav_${fav['id']}'),
                        direction: DismissDirection.endToStart,
                        background: Container(
                          alignment: Alignment.centerRight,
                          padding: const EdgeInsets.only(right: 16),
                          decoration: BoxDecoration(
                            color: Colors.red,
                            borderRadius: BorderRadius.circular(14),
                          ),
                          child: const Icon(Icons.delete, color: Colors.white),
                        ),
                        onDismissed: (_) =>
                            _remove(i, item['id']),
                        child: Card(
                          child: Padding(
                            padding: const EdgeInsets.all(14),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Chip(
                                      label: Text(
                                        isContent ? 'Content' : 'Flashcard',
                                        style: const TextStyle(
                                            color: Colors.white,
                                            fontSize: 11),
                                      ),
                                      backgroundColor: isContent
                                          ? Colors.blue
                                          : Colors.amber.shade700,
                                      padding: EdgeInsets.zero,
                                    ),
                                    const Spacer(),
                                    IconButton(
                                      icon: const Icon(Icons.delete_outline,
                                          color: Colors.red),
                                      onPressed: () => _remove(i, item['id']),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  item['title'] ?? '',
                                  style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 15),
                                ),
                                const SizedBox(height: 6),
                                Text(
                                  isContent
                                      ? ((item['content'] ?? '') as String)
                                          .substring(0,
                                              ((item['content'] ?? '') as String)
                                                          .length >
                                                      100
                                                  ? 100
                                                  : (item['content'] ?? '')
                                                      .length)
                                      : (item['description'] ?? ''),
                                  style: const TextStyle(
                                      color: Colors.grey, fontSize: 13),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 10),
                                SizedBox(
                                  width: double.infinity,
                                  child: ElevatedButton.icon(
                                    onPressed: () {
                                      if (isContent) {
                                        context.push(
                                            '/contents/${item['id']}');
                                      } else {
                                        context.push(
                                            '/flashcards/${item['id']}');
                                      }
                                    },
                                    icon: const Icon(Icons.visibility,
                                        size: 16),
                                    label: Text(isContent
                                        ? 'Read Content'
                                        : 'Practice'),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: isContent
                                          ? Colors.blue
                                          : Colors.amber.shade700,
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
