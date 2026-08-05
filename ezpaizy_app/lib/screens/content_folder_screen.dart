import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import '../widgets/app_top_bar.dart';

class ContentFolderScreen extends StatefulWidget {
  final String topic;

  const ContentFolderScreen({super.key, required this.topic});

  @override
  State<ContentFolderScreen> createState() => _ContentFolderScreenState();
}

class _ContentFolderScreenState extends State<ContentFolderScreen> {
  List<dynamic> contents = [];
  List<dynamic> filteredContents = [];
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
      final allContents = await ApiService.getContents();
      contents = allContents.where((c) {
        final t = c['topic']?.toString().trim();
        return t == widget.topic || (widget.topic == 'General' && (t == null || t.isEmpty));
      }).toList();
      filteredContents = contents;
    } catch (_) {}
    setState(() => loading = false);
  }

  void _filter() {
    final q = _search.text.toLowerCase();
    setState(() {
      filteredContents = contents
          .where((item) => item['title']?.toString().toLowerCase().contains(q) ?? false)
          .toList();
    });
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

  String _formatDate(String? raw) {
    if (raw == null) return '';
    try {
      final dt = DateTime.parse(raw);
      return DateFormat('MMM d, yyyy').format(dt);
    } catch (_) {
      return '';
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
            child: Center(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 800),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Padding(
                      padding: EdgeInsets.symmetric(horizontal: 16),
                      child: AppTopBar(showBackButton: true),
                    ),
                    // AppBar Header Row
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      child: Row(
                        children: [
                          InkWell(
                            onTap: () {
                              if (context.canPop()) {
                                context.pop();
                              } else {
                                context.go('/dashboard');
                              }
                            },
                            borderRadius: BorderRadius.circular(20),
                            child: Container(
                              width: 36,
                              height: 36,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                border: Border.all(color: const Color(0xFFCBD5E1)),
                                color: Colors.white,
                              ),
                              child: const Icon(Icons.arrow_back, size: 18, color: Color(0xFF475569)),
                            ),
                          ),
                          const SizedBox(width: 12),
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
                        'Learning materials under this folder',
                        style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), fontFamily: 'Outfit'),
                      ),
                    ),
                    const SizedBox(height: 16),
                    // Search Bar
                    Container(
                      margin: const EdgeInsets.symmetric(horizontal: 24, vertical: 8),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: TextField(
                        controller: _search,
                        style: const TextStyle(fontFamily: 'Outfit', fontSize: 13),
                        decoration: const InputDecoration(
                          hintText: 'Search materials...',
                          hintStyle: TextStyle(fontFamily: 'Outfit', fontSize: 13, color: Color(0xFF94A3B8)),
                          prefixIcon: Icon(Icons.search, color: Color(0xFF94A3B8), size: 18),
                          border: InputBorder.none,
                          contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    // Responsive Grid of Material Cards matching web
                    Expanded(
                      child: loading
                          ? const Center(child: CircularProgressIndicator())
                          : filteredContents.isEmpty
                              ? const Center(
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(Icons.menu_book, size: 64, color: Color(0xFFCBD5E1)),
                                      SizedBox(height: 12),
                                      Text(
                                        'No materials in this folder',
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
                                      maxCrossAxisExtent: 340,
                                      crossAxisSpacing: 16,
                                      mainAxisSpacing: 16,
                                      mainAxisExtent: 185, // Height matching card structure
                                    ),
                                    itemCount: filteredContents.length,
                                    itemBuilder: (context, i) {
                                      final c = filteredContents[i];
                                      final author = c['teacher']?['name'] ?? 'Hamzah';
                                      final type = (c['content_type'] ?? c['type'] ?? 'PDF').toString().toUpperCase();
                                      final dateStr = _formatDate(c['created_at']);

                                      return Container(
                                        decoration: BoxDecoration(
                                          color: Colors.white,
                                          borderRadius: BorderRadius.circular(16),
                                          border: Border.all(color: const Color(0xFFE2E8F0)),
                                          boxShadow: [
                                            BoxShadow(
                                              color: Colors.black.withOpacity(0.04),
                                              blurRadius: 10,
                                              offset: const Offset(0, 4),
                                            ),
                                          ],
                                        ),
                                        padding: const EdgeInsets.all(16),
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            // Title & Star
                                            Row(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Expanded(
                                                  child: Text(
                                                    c['title'] ?? '',
                                                    maxLines: 2,
                                                    overflow: TextOverflow.ellipsis,
                                                    style: const TextStyle(
                                                      fontSize: 14,
                                                      fontWeight: FontWeight.bold,
                                                      color: Color(0xFF1E293B),
                                                      fontFamily: 'Outfit',
                                                    ),
                                                  ),
                                                ),
                                                const SizedBox(width: 8),
                                                GestureDetector(
                                                  onTap: () => _toggleFavorite(c),
                                                  child: Icon(
                                                    c['is_favorited'] == true ? Icons.star : Icons.star_border,
                                                    color: const Color(0xFFFFC107),
                                                    size: 20,
                                                  ),
                                                ),
                                              ],
                                            ),
                                            const SizedBox(height: 8),

                                            // Author & Meta Info Row
                                            Row(
                                              children: [
                                                const Icon(Icons.person, size: 12, color: Color(0xFF94A3B8)),
                                                const SizedBox(width: 4),
                                                Flexible(
                                                  child: Text(
                                                    author,
                                                    style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontFamily: 'Outfit'),
                                                    overflow: TextOverflow.ellipsis,
                                                  ),
                                                ),
                                                if (dateStr.isNotEmpty) ...[
                                                  const Text(' • ', style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8))),
                                                  Text(
                                                    dateStr,
                                                    style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontFamily: 'Outfit'),
                                                  ),
                                                ],
                                                const Text(' • ', style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8))),
                                                Text(
                                                  type,
                                                  style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontFamily: 'Outfit'),
                                                ),
                                              ],
                                            ),
                                            const Spacer(),

                                            // Bottom Row: Type Pill Badge & Open Material Button
                                            Row(
                                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                              children: [
                                                // Format Pill Badge (e.g. PDF pink badge)
                                                Container(
                                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                                  decoration: BoxDecoration(
                                                    color: const Color(0xFFFFE4E6), // Light pink background
                                                    borderRadius: BorderRadius.circular(6),
                                                  ),
                                                  child: Text(
                                                    type,
                                                    style: const TextStyle(
                                                      fontSize: 10,
                                                      fontWeight: FontWeight.bold,
                                                      color: Color(0xFFE11D48), // Rose/Pink text matching web
                                                      fontFamily: 'Outfit',
                                                    ),
                                                  ),
                                                ),

                                                // Open Material Button (Teal green)
                                                ElevatedButton(
                                                  onPressed: () => context.push('/contents/${c['id']}'),
                                                  style: ElevatedButton.styleFrom(
                                                    backgroundColor: const Color(0xFF0F9D58), // Green/Teal matching web
                                                    foregroundColor: Colors.white,
                                                    elevation: 0,
                                                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                                  ),
                                                  child: const Row(
                                                    mainAxisSize: MainAxisSize.min,
                                                    children: [
                                                      Text(
                                                        'Open Material',
                                                        style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, fontFamily: 'Outfit'),
                                                      ),
                                                      SizedBox(width: 4),
                                                      Icon(Icons.arrow_forward, size: 12),
                                                    ],
                                                  ),
                                                ),
                                              ],
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
        ),
      ),
    );
  }
}
