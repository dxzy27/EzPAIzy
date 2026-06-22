import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../app/theme.dart';

class ContentDetailScreen extends StatefulWidget {
  final int contentId;
  const ContentDetailScreen({super.key, required this.contentId});

  @override
  State<ContentDetailScreen> createState() => _ContentDetailScreenState();
}

class _ContentDetailScreenState extends State<ContentDetailScreen> {
  Map<String, dynamic>? content;
  bool loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final d = await ApiService.getContentDetail(widget.contentId);
      setState(() { content = d; loading = false; });
    } catch (_) {
      setState(() => loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(content?['title'] ?? 'Content'),
      ),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : content == null
              ? const Center(child: Text('Failed to load content'))
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Header card
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [AppTheme.primary, AppTheme.primaryLight],
                          ),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              content!['title'] ?? '',
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 8),
                            Row(
                              children: [
                                const Icon(Icons.person_outline,
                                    color: Colors.white70, size: 14),
                                const SizedBox(width: 4),
                                Text(
                                  content!['teacher']?['name'] ?? 'Teacher',
                                  style: const TextStyle(
                                      color: Colors.white70, fontSize: 13),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 20),

                      // Content body
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Text(
                            content!['content'] ?? '',
                            style: const TextStyle(
                                fontSize: 15, height: 1.7, color: Colors.black87),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
    );
  }
}
