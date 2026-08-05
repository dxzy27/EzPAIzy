import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';

class RevisionScreen extends StatefulWidget {
  const RevisionScreen({super.key});

  @override
  State<RevisionScreen> createState() => _RevisionScreenState();
}

class _RevisionScreenState extends State<RevisionScreen> {
  List<dynamic> favorites = [];
  List<dynamic> filteredFavorites = [];
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
      favorites = await ApiService.getRevision();
      filteredFavorites = favorites;
    } catch (_) {}
    setState(() => loading = false);
  }

  void _filter() {
    final q = _search.text.toLowerCase();
    setState(() {
      filteredFavorites = favorites.where((fav) {
        final isContent = fav['content'] != null;
        final isFlashcard = fav['flashcard_set'] != null || fav['flashcardSet'] != null;
        final item = isContent
            ? fav['content']
            : (isFlashcard ? (fav['flashcard_set'] ?? fav['flashcardSet']) : fav);
        final title = (item['title'] ?? item['topic'] ?? fav['quiz_topic'] ?? '').toString().toLowerCase();
        return title.contains(q);
      }).toList();
    });
  }

  Future<void> _remove(int index, dynamic fav) async {
    final removed = filteredFavorites[index];
    setState(() {
      filteredFavorites.removeAt(index);
      favorites.remove(fav);
    });

    try {
      if (fav['content_id'] != null) {
        await ApiService.removeFavorite(fav['content_id']);
      } else if (fav['flashcard_set_id'] != null) {
        await ApiService.removeFlashcardFavorite(fav['flashcard_set_id']);
      }
    } catch (_) {
      setState(() {
        filteredFavorites.insert(index, removed);
        favorites.add(fav);
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Failed to remove from revision')),
        );
      }
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

  String _timeAgo(String? raw) {
    if (raw == null) return '';
    try {
      final dt = DateTime.parse(raw);
      final diff = DateTime.now().difference(dt);
      if (diff.inDays > 0) return '${diff.inDays} days ago';
      if (diff.inHours > 0) return '${diff.inHours} hours ago';
      if (diff.inMinutes > 0) return '${diff.inMinutes} mins ago';
      return 'just now';
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
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  child: Row(
                    children: [
                      Material(
                        color: Colors.transparent,
                        child: InkWell(
                          borderRadius: BorderRadius.circular(20),
                          onTap: () {
                            if (context.canPop()) {
                              context.pop();
                            } else {
                              context.go('/dashboard');
                            }
                          },
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
                      ),
                      const SizedBox(width: 12),
                      const Text(
                        '🎴',
                        style: TextStyle(fontSize: 22),
                      ),
                      const SizedBox(width: 8),
                      const Expanded(
                        child: Text(
                          'My Revision List',
                          style: TextStyle(
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
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 56),
                  child: Text(
                    "Learning materials you've saved for review",
                    style: TextStyle(fontSize: 12, color: Color(0xFF64748B), fontFamily: 'Outfit'),
                  ),
                ),
                const SizedBox(height: 16),
                Container(
                  margin: const EdgeInsets.symmetric(horizontal: 24, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: TextField(
                    controller: _search,
                    style: const TextStyle(fontFamily: 'Outfit', fontSize: 13),
                    decoration: const InputDecoration(
                      hintText: 'Search revision list...',
                      hintStyle: TextStyle(fontFamily: 'Outfit', fontSize: 13, color: Color(0xFF94A3B8)),
                      prefixIcon: Icon(Icons.search, color: Color(0xFF94A3B8), size: 18),
                      border: InputBorder.none,
                      contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    ),
                  ),
                ),
                const SizedBox(height: 8),
                Expanded(
                  child: loading
                      ? const Center(child: CircularProgressIndicator())
                      : filteredFavorites.isEmpty
                          ? Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  const Icon(Icons.star_outline_rounded, size: 64, color: Color(0xFFCBD5E1)),
                                  const SizedBox(height: 12),
                                  const Text(
                                    'No saved materials in your revision list',
                                    style: TextStyle(color: Color(0xFF64748B), fontFamily: 'Outfit', fontSize: 14),
                                  ),
                                  const SizedBox(height: 6),
                                  const Text(
                                    'Tap ⭐ on any content or flashcard to save it here',
                                    textAlign: TextAlign.center,
                                    style: TextStyle(color: Color(0xFF94A3B8), fontFamily: 'Outfit', fontSize: 12),
                                  ),
                                  const SizedBox(height: 20),
                                  ElevatedButton(
                                    onPressed: () => context.go('/contents'),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: const Color(0xFF3B82F6),
                                      foregroundColor: Colors.white,
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                    ),
                                    child: const Text('Browse Materials', style: TextStyle(fontFamily: 'Outfit')),
                                  ),
                                ],
                              ),
                            )
                          : RefreshIndicator(
                              onRefresh: _load,
                              child: GridView.builder(
                                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                                gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
                                  maxCrossAxisExtent: 360,
                                  crossAxisSpacing: 16,
                                  mainAxisSpacing: 16,
                                  mainAxisExtent: 200,
                                ),
                                itemCount: filteredFavorites.length,
                                itemBuilder: (context, i) {
                                  final fav = filteredFavorites[i];
                                  final isContent = fav['content'] != null;
                                  final isFlashcard = fav['flashcard_set'] != null || fav['flashcardSet'] != null;
                                  final item = isContent
                                      ? fav['content']
                                      : (isFlashcard ? (fav['flashcard_set'] ?? fav['flashcardSet']) : fav);

                                  final title = (item?['title'] ?? item?['topic'] ?? fav['quiz_topic'] ?? 'Untitled').toString();
                                  final teacherName = isContent
                                      ? (item?['teacher']?['name'] ?? 'Hamzah')
                                      : (isFlashcard ? (item?['user']?['name'] ?? 'Hamzah') : 'PAI Teacher');
                                  final countLabel = isFlashcard
                                      ? '${item?['flashcards_count'] ?? (item?['flashcards'] as List?)?.length ?? 1} Cards'
                                      : (isContent ? (item?['file_type'] ?? 'PDF').toString().toUpperCase() : 'Quiz');
                                  final savedAgo = _timeAgo(fav['created_at']);
                                  final uploadDate = _formatDate(item?['created_at'] ?? fav['created_at']);

                                  Color badgeBg;
                                  Color badgeText;
                                  Color borderLeftColor;
                                  String badgeLabel;
                                  Color btnBg;
                                  String btnText;

                                  if (isFlashcard) {
                                    badgeBg = const Color(0xFFFFF8E1);
                                    badgeText = const Color(0xFFFF8F00);
                                    borderLeftColor = const Color(0xFFFF8F00);
                                    badgeLabel = '🎴 Flashcard';
                                    btnBg = const Color(0xFFFFC107);
                                    btnText = 'Open Flashcard Set';
                                  } else if (isContent) {
                                    badgeBg = const Color(0xFFE3F2FD);
                                    badgeText = const Color(0xFF1565C0);
                                    borderLeftColor = const Color(0xFF1565C0);
                                    badgeLabel = '📄 Material';
                                    btnBg = const Color(0xFF3B82F6);
                                    btnText = 'Open Material';
                                  } else {
                                    badgeBg = const Color(0xFFE0F2F1);
                                    badgeText = const Color(0xFF00A896);
                                    borderLeftColor = const Color(0xFF00A896);
                                    badgeLabel = '❓ Quiz';
                                    btnBg = const Color(0xFF0D9488);
                                    btnText = 'Take Quiz';
                                  }

                                  return ClipRRect(
                                    borderRadius: BorderRadius.circular(16),
                                    child: Container(
                                      decoration: BoxDecoration(
                                        color: Colors.white,
                                        border: Border.all(color: const Color(0xFFE2E8F0)),
                                        boxShadow: [
                                          BoxShadow(
                                            color: Colors.black.withOpacity(0.04),
                                            blurRadius: 10,
                                            offset: const Offset(0, 4),
                                          ),
                                        ],
                                      ),
                                      child: Row(
                                        children: [
                                          // Left accent color strip
                                          Container(
                                            width: 5,
                                            height: double.infinity,
                                            color: borderLeftColor,
                                          ),
                                          // Main card content
                                          Expanded(
                                            child: Padding(
                                              padding: const EdgeInsets.all(14),
                                              child: Column(
                                                crossAxisAlignment: CrossAxisAlignment.start,
                                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                                children: [
                                                  // Header Row: Badge & Delete Icon
                                                  Row(
                                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                                    children: [
                                                      Container(
                                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                                                        decoration: BoxDecoration(
                                                          color: badgeBg,
                                                          borderRadius: BorderRadius.circular(20),
                                                        ),
                                                        child: Text(
                                                          badgeLabel,
                                                          style: TextStyle(
                                                            fontSize: 11,
                                                            fontWeight: FontWeight.bold,
                                                            color: badgeText,
                                                            fontFamily: 'Outfit',
                                                          ),
                                                        ),
                                                      ),
                                                      GestureDetector(
                                                        onTap: () => _remove(i, fav),
                                                        child: const Icon(
                                                          Icons.delete_outline_rounded,
                                                          color: Color(0xFF94A3B8),
                                                          size: 18,
                                                        ),
                                                      ),
                                                    ],
                                                  ),

                                                  // Title & Info Block
                                                  Column(
                                                    crossAxisAlignment: CrossAxisAlignment.start,
                                                    children: [
                                                      Text(
                                                        title,
                                                        maxLines: 1,
                                                        overflow: TextOverflow.ellipsis,
                                                        style: const TextStyle(
                                                          fontSize: 15,
                                                          fontWeight: FontWeight.w800,
                                                          color: Color(0xFF1E293B),
                                                          fontFamily: 'Outfit',
                                                        ),
                                                      ),
                                                      const SizedBox(height: 4),
                                                      Row(
                                                        children: [
                                                          const Icon(Icons.person, size: 12, color: Color(0xFF94A3B8)),
                                                          const SizedBox(width: 4),
                                                          Flexible(
                                                            child: Text(
                                                              'By: $teacherName',
                                                              style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontFamily: 'Outfit'),
                                                              overflow: TextOverflow.ellipsis,
                                                            ),
                                                          ),
                                                          const Text(' • ', style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8))),
                                                          Text(
                                                            countLabel,
                                                            style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontFamily: 'Outfit'),
                                                          ),
                                                        ],
                                                      ),
                                                      const SizedBox(height: 3),
                                                      Row(
                                                        children: [
                                                          Text(
                                                            '⭐ Saved $savedAgo',
                                                            style: const TextStyle(
                                                              fontSize: 10,
                                                              fontWeight: FontWeight.bold,
                                                              color: Color(0xFF3B82F6),
                                                              fontFamily: 'Outfit',
                                                            ),
                                                          ),
                                                          if (uploadDate.isNotEmpty) ...[
                                                            const Text(' | ', style: TextStyle(fontSize: 10, color: Color(0xFFCBD5E1))),
                                                            Text(
                                                              'Uploaded: $uploadDate',
                                                              style: const TextStyle(fontSize: 10, color: Color(0xFF94A3B8), fontFamily: 'Outfit'),
                                                            ),
                                                          ],
                                                        ],
                                                      ),
                                                    ],
                                                  ),

                                                  // Bottom Action Button
                                                  SizedBox(
                                                    width: double.infinity,
                                                    height: 36,
                                                    child: ElevatedButton(
                                                      onPressed: () {
                                                        if (isFlashcard) {
                                                          final targetId = item?['id'] ?? fav['flashcard_set_id'];
                                                          context.push('/flashcards/set/$targetId');
                                                        } else if (isContent) {
                                                          final targetId = item?['id'] ?? fav['content_id'];
                                                          context.push('/contents/$targetId');
                                                        } else {
                                                          context.push('/take-quiz', extra: item);
                                                        }
                                                      },
                                                      style: ElevatedButton.styleFrom(
                                                        backgroundColor: btnBg,
                                                        foregroundColor: Colors.white,
                                                        elevation: 0,
                                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                                      ),
                                                      child: Row(
                                                        mainAxisAlignment: MainAxisAlignment.center,
                                                        children: [
                                                          Text(
                                                            btnText,
                                                            style: const TextStyle(
                                                              fontSize: 12,
                                                              fontWeight: FontWeight.bold,
                                                              fontFamily: 'Outfit',
                                                            ),
                                                          ),
                                                          const SizedBox(width: 6),
                                                          const Icon(Icons.arrow_forward, size: 14),
                                                        ],
                                                      ),
                                                    ),
                                                  ),
                                                ],
                                              ),
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
          ),
        ),
      ),
    );
  }
}
