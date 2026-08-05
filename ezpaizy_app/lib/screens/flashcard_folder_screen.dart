import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import '../widgets/app_top_bar.dart';

class FlashcardFolderScreen extends StatefulWidget {
  final String topic;

  const FlashcardFolderScreen({super.key, required this.topic});

  @override
  State<FlashcardFolderScreen> createState() => _FlashcardFolderScreenState();
}

class _FlashcardFolderScreenState extends State<FlashcardFolderScreen> {
  List<dynamic> sets = [];
  List<dynamic> filteredSets = [];
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
      final allSets = await ApiService.getFlashcards();
      sets = allSets.where((s) {
        final t = s['topic']?.toString().trim();
        return t == widget.topic || (widget.topic == 'General' && (t == null || t.isEmpty));
      }).toList();
      filteredSets = sets;
    } catch (_) {}
    setState(() => loading = false);
  }

  void _filter() {
    final q = _search.text.toLowerCase();
    setState(() {
      filteredSets = sets
          .where((s) => s['title']?.toString().toLowerCase().contains(q) ?? false)
          .toList();
    });
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

  Future<void> _resetProgress(int setId) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Reset Progress?', style: TextStyle(fontFamily: 'Outfit')),
        content: const Text('Are you sure you want to reset your flashcard progress for this set? This will reset all cards status to New.', style: TextStyle(fontFamily: 'Outfit')),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel', style: TextStyle(fontFamily: 'Outfit')),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red, foregroundColor: Colors.white),
            child: const Text('Reset', style: TextStyle(fontFamily: 'Outfit')),
          ),
        ],
      ),
    );

    if (confirm == true) {
      setState(() => loading = true);
      final ok = await ApiService.resetFlashcardProgress(setId);
      if (ok && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Flashcard progress reset successfully!')),
        );
      }
      _load();
    }
  }

  void _openFlashcardModes(Map<String, dynamic> s, int cards) {
    if (cards == 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('This flashcard set contains no cards.')),
      );
      return;
    }

    final style = Provider.of<AuthProvider>(context, listen: false).user?['learning_style']?.toString().toLowerCase();

    if (style == 'kinesthetic') {
      // Kinesthetic learners go directly to Practice Mode (Study Screen)
      context.push('/flashcards/${s['id']}/study').then((_) => _load());
    } else {
      // All other styles (visual, auditory, read_write, null) go directly to Read Mode
      context.push('/flashcards/${s['id']}').then((_) => _load());
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
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 16),
                  child: AppTopBar(showBackButton: true),
                ),
                // Top Custom aligned Header
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 20, 24, 8),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          IconButton(
                            icon: const Icon(Icons.arrow_back, color: Color(0xFF1E293B)),
                            onPressed: () => context.go('/flashcards'),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  widget.topic,
                                  style: const TextStyle(
                                    fontSize: 24,
                                    fontWeight: FontWeight.w900,
                                    color: Color(0xFF1E293B),
                                    fontFamily: 'Outfit',
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  'Flashcards / ${widget.topic}',
                                  style: const TextStyle(
                                    fontSize: 12,
                                    color: Color(0xFF94A3B8),
                                    fontFamily: 'Outfit',
                                    fontWeight: FontWeight.w500,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),

                      // Search input styled like web
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 8),
                        child: TextField(
                          controller: _search,
                          decoration: InputDecoration(
                            hintText: 'Search sets...',
                            hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontFamily: 'Outfit'),
                            prefixIcon: const Icon(Icons.search, color: Color(0xFF94A3B8)),
                            filled: true,
                            fillColor: Colors.white,
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(10),
                              borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(10),
                              borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(10),
                              borderSide: const BorderSide(color: Color(0xFF3B82F6)),
                            ),
                            contentPadding: const EdgeInsets.symmetric(vertical: 10),
                          ),
                        ),
                      ),
                      const SizedBox(height: 20),

                      // Sub-section category header
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 8),
                        child: Text(
                          'SETS IN ${widget.topic.toUpperCase()}',
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFF64748B),
                            letterSpacing: 1.0,
                            fontFamily: 'Outfit',
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                // Sets Grid
                Expanded(
                  child: loading
                      ? const Center(child: CircularProgressIndicator())
                      : filteredSets.isEmpty
                          ? const Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.style, size: 64, color: Colors.grey),
                                  SizedBox(height: 12),
                                  Text('No flashcard sets in this folder', style: TextStyle(color: Colors.grey, fontFamily: 'Outfit')),
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
                                  mainAxisExtent: 260, // Increased height to provide clean breathing room at the bottom
                                ),
                                itemCount: filteredSets.length,
                                itemBuilder: (context, i) {
                                  final s = filteredSets[i];
                                  final cards = (s['flashcards'] as List?)?.length ?? 0;
                                  final stats = s['stats'];
                                  final mastered = stats?['mastered'] ?? 0;
                                  final review = stats?['review'] ?? 0;
                                  final learning = stats?['learning'] ?? 0;
                                  final newCount = stats?['new'] ?? (cards - (mastered + review + learning));

                                  final double pct = cards == 0 ? 0.0 : ((mastered + review) / cards);
                                  final pctText = '${(pct * 100).toStringAsFixed(0)}%';

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
                                        // Title and Star Row
                                        Row(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Expanded(
                                              child: Text(
                                                s['title'] ?? '',
                                                style: const TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 14,
                                                  color: Color(0xFF0F172A),
                                                  fontFamily: 'Outfit',
                                                ),
                                                maxLines: 2,
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            ),
                                            IconButton(
                                              padding: EdgeInsets.zero,
                                              constraints: const BoxConstraints(),
                                              icon: Icon(
                                                s['is_favorited'] == true ? Icons.star : Icons.star_border,
                                                color: Colors.amber,
                                                size: 20,
                                              ),
                                              onPressed: () => _toggleFavorite(s),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 6),

                                        // Topic badge
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                          decoration: BoxDecoration(
                                            color: const Color(0xFFF1F5F9),
                                            borderRadius: BorderRadius.circular(4),
                                          ),
                                          child: Text(
                                            widget.topic,
                                            style: const TextStyle(
                                              fontSize: 10,
                                              color: Color(0xFF64748B),
                                              fontWeight: FontWeight.bold,
                                              fontFamily: 'Outfit',
                                            ),
                                          ),
                                        ),
                                        const SizedBox(height: 12),

                                        // Mastery Progress text
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            const Text(
                                              'Mastery Progress',
                                              style: TextStyle(fontSize: 10, color: Color(0xFF64748B), fontFamily: 'Outfit'),
                                            ),
                                            Text(
                                              pctText,
                                              style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF10B981), fontFamily: 'Outfit'),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 4),

                                        // Progress Bar (Custom stacked bar matching web)
                                        ClipRRect(
                                          borderRadius: BorderRadius.circular(50),
                                          child: Container(
                                            height: 8,
                                            color: const Color(0xFFF1F5F9), // Background grey
                                            child: cards == 0
                                                ? const SizedBox.shrink()
                                                : Row(
                                                    children: [
                                                      if (mastered + review > 0)
                                                        Expanded(
                                                          flex: mastered + review,
                                                          child: Container(color: const Color(0xFF10B981)), // Green (Mastered)
                                                        ),
                                                      if (learning > 0)
                                                        Expanded(
                                                          flex: learning,
                                                          child: Container(color: const Color(0xFFF97316)), // Orange/Red (Learning)
                                                        ),
                                                      if (newCount > 0)
                                                        Expanded(
                                                          flex: newCount,
                                                          child: Container(color: Colors.transparent), // Remaining grey
                                                        ),
                                                    ],
                                                  ),
                                          ),
                                        ),
                                        const SizedBox(height: 12),

                                         // Status Pills
                                         Wrap(
                                           spacing: 4,
                                           runSpacing: 4,
                                           children: [
                                             _buildBadge('$newCount New', const Color(0xFF64748B), const Color(0xFFF1F5F9)),
                                             _buildBadge('Learning $learning', const Color(0xFFEA580C), const Color(0xFFFFF7ED)),
                                             _buildBadge('Mastered ${mastered + review}', const Color(0xFF16A34A), const Color(0xFFF0FDF4)),
                                           ],
                                         ),
                                        const SizedBox(height: 12),

                                        // Open button
                                        SizedBox(
                                          width: double.infinity,
                                          height: 32,
                                          child: ElevatedButton(
                                            onPressed: () => _openFlashcardModes(s, cards),
                                            style: ElevatedButton.styleFrom(
                                              backgroundColor: const Color(0xFF0D9488), // Teal color
                                              foregroundColor: Colors.white,
                                              elevation: 0,
                                              padding: EdgeInsets.zero,
                                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                            ),
                                            child: const Row(
                                              mainAxisAlignment: MainAxisAlignment.center,
                                              children: [
                                                Text('Open Flashcards', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, fontFamily: 'Outfit')),
                                                SizedBox(width: 4),
                                                Icon(Icons.arrow_forward, size: 10),
                                              ],
                                            ),
                                          ),
                                        ),
                                        const SizedBox(height: 8),

                                        // Reset button
                                        Center(
                                          child: InkWell(
                                            onTap: () => _resetProgress(s['id']),
                                            child: const Row(
                                              mainAxisSize: MainAxisSize.min,
                                              children: [
                                                Icon(Icons.refresh, size: 12, color: Color(0xFF94A3B8)),
                                                SizedBox(width: 4),
                                                Text('Reset Progress', style: TextStyle(fontSize: 10, color: Color(0xFF94A3B8), fontWeight: FontWeight.bold, fontFamily: 'Outfit')),
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

  Widget _buildBadge(String label, Color color, Color bgColor) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(4),
        border: Border.all(color: color.withOpacity(0.3)),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: color,
          fontSize: 9,
          fontWeight: FontWeight.bold,
          fontFamily: 'Outfit',
        ),
      ),
    );
  }
}
