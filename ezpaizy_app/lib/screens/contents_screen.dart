import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../services/api_service.dart';

class ContentsScreen extends StatefulWidget {
  const ContentsScreen({super.key});

  @override
  State<ContentsScreen> createState() => _ContentsScreenState();
}

class _ContentsScreenState extends State<ContentsScreen> {
  List<dynamic> contents = [];
  bool loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => loading = true);
    try {
      contents = await ApiService.getContents();
    } catch (_) {}
    setState(() => loading = false);
  }

  Future<void> _toggleFavorite(Map<String, dynamic> item) async {
    final isFav = item['is_favorited'] == true;
    setState(() => item['is_favorited'] = !isFav);
    try {
      if (isFav) {
        await ApiService.removeFavorite(item['id']);
      } else {
        await ApiService.addFavorite(item['id']);
      }
    } catch (_) {
      setState(() => item['is_favorited'] = isFav);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Learning Materials'),
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
          : contents.isEmpty
              ? const Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.menu_book, size: 64, color: Colors.grey),
                      SizedBox(height: 12),
                      Text('No materials yet',
                          style: TextStyle(color: Colors.grey)),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView.separated(
                    padding: const EdgeInsets.all(12),
                    itemCount: contents.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 10),
                    itemBuilder: (_, i) {
                      final c = contents[i];
                      final body = (c['content'] ?? '') as String;
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
                                      c['title'] ?? '',
                                      style: const TextStyle(
                                          fontWeight: FontWeight.bold,
                                          fontSize: 15),
                                    ),
                                  ),
                                  IconButton(
                                    icon: Icon(
                                      c['is_favorited'] == true
                                          ? Icons.star
                                          : Icons.star_border,
                                      color: Colors.amber,
                                    ),
                                    onPressed: () => _toggleFavorite(c),
                                  ),
                                ],
                              ),
                              Text(
                                body.length > 120
                                    ? '${body.substring(0, 120)}...'
                                    : body,
                                style: const TextStyle(
                                    color: Colors.grey, fontSize: 13),
                              ),
                              const SizedBox(height: 8),
                              Row(
                                children: [
                                  const Icon(Icons.person_outline,
                                      size: 13, color: Colors.grey),
                                  const SizedBox(width: 4),
                                  Text(
                                    c['teacher']?['name'] ?? 'Teacher',
                                    style: const TextStyle(
                                        fontSize: 12, color: Colors.grey),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 10),
                              SizedBox(
                                width: double.infinity,
                                child: ElevatedButton.icon(
                                  onPressed: () =>
                                      context.push('/contents/${c['id']}'),
                                  icon: const Icon(Icons.visibility, size: 16),
                                  label: const Text('Read Content'),
                                ),
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
