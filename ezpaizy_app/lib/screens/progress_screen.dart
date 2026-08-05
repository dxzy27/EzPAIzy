import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';

class ProgressScreen extends StatefulWidget {
  const ProgressScreen({super.key});

  @override
  State<ProgressScreen> createState() => _ProgressScreenState();
}

class _ProgressScreenState extends State<ProgressScreen> {
  List<dynamic> progress = [];
  List<dynamic> filteredProgress = [];
  bool loading = true;

  String selectedType = 'all'; // 'all', 'quiz', 'flashcards'
  String selectedTopic = 'all'; // 'all' or topic name
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _load();
    _searchController.addListener(_applyFilters);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => loading = true);
    try {
      progress = await ApiService.getProgress();
      _applyFilters();
    } catch (_) {}
    setState(() => loading = false);
  }

  void _applyFilters() {
    final query = _searchController.text.toLowerCase().trim();
    setState(() {
      filteredProgress = progress.where((p) {
        final isQuiz = p['quiz'] != null || p['difficulty'] != null;
        final type = isQuiz ? 'quiz' : 'flashcards';

        if (selectedType != 'all' && type != selectedType) {
          return false;
        }

        final topic = (p['topic'] ?? p['quiz']?['topic'] ?? '').toString();
        if (selectedTopic != 'all' && topic.toLowerCase() != selectedTopic.toLowerCase()) {
          return false;
        }

        final title = (p['title'] ?? p['quiz']?['title'] ?? topic).toString().toLowerCase();
        if (query.isNotEmpty && !title.contains(query) && !topic.toLowerCase().contains(query)) {
          return false;
        }

        return true;
      }).toList();
    });
  }

  List<String> get availableTopics {
    final set = <String>{};
    for (var p in progress) {
      final t = p['topic'] ?? p['quiz']?['topic'];
      if (t != null && t.toString().isNotEmpty) {
        set.add(t.toString());
      }
    }
    return set.toList()..sort();
  }

  double get avgScore {
    final graded = progress.where((p) {
      final diff = p['difficulty'] ?? p['quiz']?['difficulty'];
      final status = p['status'];
      return (diff != 'hard' && diff != 'medium') || status == 'graded' || status == 'completed';
    }).where((p) => (p['score'] ?? 0) > 0).toList();

    if (graded.isEmpty) return 0;
    final sum = graded.fold<double>(0, (acc, p) => acc + (p['score'] ?? 0).toDouble());
    return sum / graded.length;
  }

  int get highestScore {
    if (progress.isEmpty) return 0;
    return progress.fold<int>(0, (max, p) => (p['score'] ?? 0) > max ? p['score'] : max);
  }

  Map<String, List<dynamic>> get topicGroups {
    final map = <String, List<dynamic>>{};
    for (var p in progress) {
      final topic = (p['topic'] ?? p['quiz']?['topic'] ?? 'General').toString();
      map.putIfAbsent(topic, () => []).add(p);
    }
    return map;
  }

  String _formatDate(String? raw) {
    if (raw == null) return '';
    try {
      final dt = DateTime.parse(raw);
      return DateFormat('MMM d, yyyy HH:mm').format(dt);
    } catch (_) {
      return '';
    }
  }

  Map<String, Color> _getTopicBadgeColors(String topic) {
    final norm = topic.toLowerCase();
    if (norm.contains('quran') || norm.contains("qur'an")) {
      return {'bg': const Color(0xFFF3E5F5), 'text': const Color(0xFF7B1FA2)};
    } else if (norm.contains('hadis') || norm.contains('hadith')) {
      return {'bg': const Color(0xFFE3F2FD), 'text': const Color(0xFF1565C0)};
    } else if (norm.contains('akidah') || norm.contains('aqidah')) {
      return {'bg': const Color(0xFFE0F2F1), 'text': const Color(0xFF00796B)};
    } else if (norm.contains('fiqah') || norm.contains('fiqh')) {
      return {'bg': const Color(0xFFE8F5E9), 'text': const Color(0xFF2E7D32)};
    } else if (norm.contains('sirah') || norm.contains('sejarah')) {
      return {'bg': const Color(0xFFFFF3E0), 'text': const Color(0xFFE65100)};
    } else if (norm.contains('akhlak') || norm.contains('adab')) {
      return {'bg': const Color(0xFFFFEBEE), 'text': const Color(0xFFC62828)};
    }
    return {'bg': const Color(0xFFF5F5F5), 'text': const Color(0xFF616161)};
  }

  Future<void> _showDetail(BuildContext context, Map<String, dynamic> p) async {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _DetailSheet(progressId: p['id']),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isWide = MediaQuery.of(context).size.width > 768;

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
            child: loading
                ? const Center(child: CircularProgressIndicator())
                : RefreshIndicator(
                    onRefresh: _load,
                    child: SingleChildScrollView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                      child: Center(
                        child: ConstrainedBox(
                          constraints: const BoxConstraints(maxWidth: 850),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Header Row
                              Row(
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
                                  const Text('📈', style: TextStyle(fontSize: 24)),
                                  const SizedBox(width: 8),
                                  const Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          'My Progress',
                                          style: TextStyle(
                                            fontSize: 22,
                                            fontWeight: FontWeight.w900,
                                            color: Color(0xFF1E293B),
                                            fontFamily: 'Outfit',
                                          ),
                                        ),
                                        Text(
                                          'Track your quiz attempts and study performance',
                                          style: TextStyle(fontSize: 12, color: Color(0xFF64748B), fontFamily: 'Outfit'),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 20),

                              // Top 3 Statistics Overview Cards
                              Row(
                                children: [
                                  Expanded(
                                    child: _buildMetricCard(
                                      value: '${progress.length}',
                                      title: 'QUIZZES TAKEN',
                                      subtitle: progress.isNotEmpty ? 'Keep up the practice!' : 'Start your first quiz!',
                                      valueColor: const Color(0xFF1565C0),
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: _buildMetricCard(
                                      value: '${avgScore.toStringAsFixed(0)}%',
                                      title: 'AVERAGE SCORE',
                                      subtitle: avgScore >= 70 ? 'Excellent performance!' : (avgScore > 0 ? 'Good work, aim higher!' : 'No graded attempts yet.'),
                                      valueColor: const Color(0xFF00A896),
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: _buildMetricCard(
                                      value: '$highestScore%',
                                      title: 'BEST QUIZ SCORE',
                                      subtitle: highestScore >= 80 ? 'Mastery achieved!' : (highestScore > 0 ? 'Keep striving for 100%!' : 'Take a quiz to set a record.'),
                                      valueColor: const Color(0xFF2E7D32),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 16),

                              // Topics Mastery & Motivational Card Row
                              if (isWide)
                                Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Expanded(flex: 7, child: _buildTopicMasteryCard()),
                                    const SizedBox(width: 16),
                                    Expanded(flex: 5, child: _buildMotivationalCard()),
                                  ],
                                )
                              else
                                Column(
                                  children: [
                                    _buildTopicMasteryCard(),
                                    const SizedBox(height: 12),
                                    _buildMotivationalCard(),
                                  ],
                                ),
                              const SizedBox(height: 20),

                              // Filters & Search Bar
                              Container(
                                padding: const EdgeInsets.all(16),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(16),
                                  border: Border.all(color: const Color(0xFFE2E8F0)),
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withOpacity(0.02),
                                      blurRadius: 10,
                                      offset: const Offset(0, 2),
                                    ),
                                  ],
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    if (isWide)
                                      Row(
                                        children: [
                                          Expanded(child: _buildTypeFilter()),
                                          const SizedBox(width: 12),
                                          Expanded(child: _buildTopicFilter()),
                                          const SizedBox(width: 12),
                                          Expanded(flex: 2, child: _buildSearchInput()),
                                        ],
                                      )
                                    else
                                      Column(
                                        children: [
                                          Row(
                                            children: [
                                              Expanded(child: _buildTypeFilter()),
                                              const SizedBox(width: 12),
                                              Expanded(child: _buildTopicFilter()),
                                            ],
                                          ),
                                          const SizedBox(height: 12),
                                          _buildSearchInput(),
                                        ],
                                      ),
                                  ],
                                ),
                              ),
                              const SizedBox(height: 16),

                              // Progress Cards Grid
                              filteredProgress.isEmpty
                                  ? Container(
                                      padding: const EdgeInsets.symmetric(vertical: 40),
                                      alignment: Alignment.center,
                                      child: const Column(
                                        children: [
                                          Icon(Icons.bar_chart_outlined, size: 54, color: Color(0xFFCBD5E1)),
                                          SizedBox(height: 12),
                                          Text(
                                            'No matching performance history found',
                                            style: TextStyle(color: Color(0xFF64748B), fontFamily: 'Outfit', fontSize: 14),
                                          ),
                                        ],
                                      ),
                                    )
                                  : ListView.separated(
                                      shrinkWrap: true,
                                      physics: const NeverScrollableScrollPhysics(),
                                      itemCount: filteredProgress.length,
                                      separatorBuilder: (_, __) => const SizedBox(height: 12),
                                      itemBuilder: (context, index) {
                                        return _buildProgressRowCard(filteredProgress[index]);
                                      },
                                    ),

                              const SizedBox(height: 32),

                              // Continue Your Journey Footer
                              _buildFooterJourney(),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
          ),
        ),
      ),
    );
  }

  Widget _buildMetricCard({
    required String value,
    required String title,
    required String subtitle,
    required Color valueColor,
  }) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.03),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(
            value,
            style: TextStyle(
              fontSize: 26,
              fontWeight: FontWeight.w900,
              color: valueColor,
              fontFamily: 'Outfit',
            ),
          ),
          const SizedBox(height: 2),
          Text(
            title,
            style: const TextStyle(
              fontSize: 10,
              fontWeight: FontWeight.bold,
              color: Color(0xFF64748B),
              letterSpacing: 0.5,
              fontFamily: 'Outfit',
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            style: const TextStyle(
              fontSize: 10,
              color: Color(0xFF94A3B8),
              fontFamily: 'Outfit',
            ),
            textAlign: TextAlign.center,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildTopicMasteryCard() {
    final groups = topicGroups;
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.bar_chart_rounded, size: 18, color: Color(0xFF10B981)),
              SizedBox(width: 6),
              Text(
                'Topics Mastery',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF1E293B), fontFamily: 'Outfit'),
              ),
            ],
          ),
          const SizedBox(height: 12),
          if (groups.isEmpty)
            const Text('No topic performance data available yet.', style: TextStyle(fontSize: 12, color: Color(0xFF94A3B8), fontFamily: 'Outfit'))
          else
            Column(
              children: groups.entries.map((entry) {
                final topic = entry.key;
                final items = entry.value;
                final mastered = items.where((it) {
                  final score = it['score'] ?? 0;
                  return it['status'] == 'Mastered' || it['status'] == 'Excellent' || it['status'] == 'graded' || score >= 70;
                }).length;
                final pct = items.isNotEmpty ? ((mastered / items.length) * 100).round() : 0;

                Color barColor = const Color(0xFFEF4444);
                if (pct >= 85) {
                  barColor = const Color(0xFF10B981);
                } else if (pct >= 50) {
                  barColor = const Color(0xFFF59E0B);
                }

                return Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: const Color(0xFFF1F5F9)),
                    ),
                    child: Column(
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(topic, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF1E293B), fontFamily: 'Outfit')),
                            Text('$pct% Mastered', style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontFamily: 'Outfit')),
                          ],
                        ),
                        const SizedBox(height: 6),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(4),
                          child: LinearProgressIndicator(
                            value: pct / 100.0,
                            minHeight: 8,
                            backgroundColor: const Color(0xFFE2E8F0),
                            valueColor: AlwaysStoppedAnimation<Color>(barColor),
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              }).toList(),
            ),
        ],
      ),
    );
  }

  Widget _buildMotivationalCard() {
    final groups = topicGroups;
    String bestTopic = 'Al-Quran';
    int bestPct = 0;
    if (groups.isNotEmpty) {
      for (var entry in groups.entries) {
        final items = entry.value;
        final mastered = items.where((it) {
          final score = it['score'] ?? 0;
          return it['status'] == 'Mastered' || score >= 70;
        }).length;
        final pct = items.isNotEmpty ? ((mastered / items.length) * 100).round() : 0;
        if (pct >= bestPct) {
          bestPct = pct;
          bestTopic = entry.key;
        }
      }
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFE8F5E9),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFC8E6C9)),
      ),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: const BoxDecoration(
              color: Color(0xFF2E7D32),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.sentiment_satisfied_alt_rounded, color: Colors.white, size: 24),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Keep learning!', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF2E7D32), fontFamily: 'Outfit')),
                const SizedBox(height: 2),
                Text(
                  groups.isNotEmpty
                      ? 'You mastered $bestPct% of $bestTopic. Great job!'
                      : 'Complete quizzes or study flashcards to track your progress.',
                  style: const TextStyle(fontSize: 12, color: Color(0xFF1E293B), fontFamily: 'Outfit'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTypeFilter() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('FILTER TYPE', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF64748B), fontFamily: 'Outfit')),
        const SizedBox(height: 4),
        DropdownButtonFormField<String>(
          value: selectedType,
          style: const TextStyle(fontFamily: 'Outfit', fontSize: 12, color: Color(0xFF1E293B)),
          decoration: InputDecoration(
            isDense: true,
            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
          ),
          items: const [
            DropdownMenuItem(value: 'all', child: Text('All (Quiz & Flashcards)')),
            DropdownMenuItem(value: 'quiz', child: Text('Quiz')),
            DropdownMenuItem(value: 'flashcards', child: Text('Flashcards')),
          ],
          onChanged: (val) {
            if (val != null) {
              selectedType = val;
              _applyFilters();
            }
          },
        ),
      ],
    );
  }

  Widget _buildTopicFilter() {
    final topics = ['all', ...availableTopics];
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('FILTER TOPIC', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF64748B), fontFamily: 'Outfit')),
        const SizedBox(height: 4),
        DropdownButtonFormField<String>(
          value: topics.contains(selectedTopic) ? selectedTopic : 'all',
          style: const TextStyle(fontFamily: 'Outfit', fontSize: 12, color: Color(0xFF1E293B)),
          decoration: InputDecoration(
            isDense: true,
            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
          ),
          items: topics.map((t) {
            return DropdownMenuItem(value: t, child: Text(t == 'all' ? 'All Topics' : t));
          }).toList(),
          onChanged: (val) {
            if (val != null) {
              selectedTopic = val;
              _applyFilters();
            }
          },
        ),
      ],
    );
  }

  Widget _buildSearchInput() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('SEARCH TITLE / TOPIC', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF64748B), fontFamily: 'Outfit')),
        const SizedBox(height: 4),
        TextField(
          controller: _searchController,
          style: const TextStyle(fontFamily: 'Outfit', fontSize: 12),
          decoration: InputDecoration(
            isDense: true,
            hintText: 'Search title or topic...',
            hintStyle: const TextStyle(fontFamily: 'Outfit', fontSize: 12, color: Color(0xFF94A3B8)),
            prefixIcon: const Icon(Icons.search, size: 16, color: Color(0xFF94A3B8)),
            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
          ),
        ),
      ],
    );
  }

  Widget _buildProgressRowCard(dynamic p) {
    final isQuiz = p['quiz'] != null || p['difficulty'] != null;
    final topic = (p['topic'] ?? p['quiz']?['topic'] ?? 'General').toString();
    final title = (p['title'] ?? p['quiz']?['title'] ?? topic).toString();
    final teacherName = (p['teacher'] ?? p['quiz']?['teacher']?['name'] ?? 'Hamzah').toString();
    final dateStr = _formatDate(p['created_at']);
    final score = p['score'] ?? 0;
    final status = (p['status'] ?? 'completed').toString();

    final borderLeftColor = isQuiz ? const Color(0xFF00A896) : const Color(0xFFFF8F00);
    final Color badgeBg = isQuiz ? const Color(0xFFE0F2F1) : const Color(0xFFFFF8E1);
    final Color badgeTextColor = isQuiz ? const Color(0xFF00A896) : const Color(0xFFFF8F00);
    final String badgeLabel = isQuiz ? '❓ Quiz' : '🎴 Flashcard';

    final topicBadge = _getTopicBadgeColors(topic);

    String statusLabel = status;
    Color statusBg = const Color(0xFFF1F5F9);
    Color statusText = const Color(0xFF475569);

    if (status == 'completed' || status == 'Excellent' || status == 'Mastered') {
      statusLabel = isQuiz ? (score >= 50 ? 'Passed' : 'Failed') : 'Completed';
      statusBg = (score >= 50 || status == 'Mastered') ? const Color(0xFFDCFCE7) : const Color(0xFFFEE2E2);
      statusText = (score >= 50 || status == 'Mastered') ? const Color(0xFF166534) : const Color(0xFF991B1B);
    } else if (status == 'pending') {
      statusLabel = 'Pending Review';
      statusBg = const Color(0xFFF1F5F9);
      statusText = const Color(0xFF64748B);
    } else if (status == 'graded') {
      statusLabel = 'Graded';
      statusBg = const Color(0xFFDBEAFE);
      statusText = const Color(0xFF1E40AF);
    } else if (status == 'Learning') {
      statusLabel = 'Learning';
      statusBg = const Color(0xFFFEF3C7);
      statusText = const Color(0xFF92400E);
    }

    return ClipRRect(
      borderRadius: BorderRadius.circular(16),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          border: Border.all(color: const Color(0xFFE2E8F0)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.03),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          children: [
            Container(width: 5, height: 130, color: borderLeftColor),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Badges row
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                              decoration: BoxDecoration(
                                color: badgeBg,
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Text(
                                badgeLabel,
                                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: badgeTextColor, fontFamily: 'Outfit'),
                              ),
                            ),
                            const SizedBox(width: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                              decoration: BoxDecoration(
                                color: statusBg,
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Text(
                                statusLabel,
                                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: statusText, fontFamily: 'Outfit'),
                              ),
                            ),
                          ],
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                          decoration: BoxDecoration(
                            color: topicBadge['bg'],
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            topic.toUpperCase(),
                            style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: topicBadge['text'], fontFamily: 'Outfit'),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),

                    // Title
                    Text(
                      title,
                      style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: Color(0xFF1E293B), fontFamily: 'Outfit'),
                    ),
                    const SizedBox(height: 4),

                    // Subtitle metadata
                    Row(
                      children: [
                        const Icon(Icons.person, size: 12, color: Color(0xFF94A3B8)),
                        const SizedBox(width: 4),
                        Text('By: $teacherName', style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontFamily: 'Outfit')),
                        const Text(' • ', style: TextStyle(fontSize: 11, color: Color(0xFF94A3B8))),
                        const Icon(Icons.access_time_rounded, size: 12, color: Color(0xFF94A3B8)),
                        const SizedBox(width: 4),
                        Text('Attempted: $dateStr', style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontFamily: 'Outfit')),
                      ],
                    ),
                    const SizedBox(height: 8),
                    const Divider(color: Color(0xFFF1F5F9), height: 1),
                    const SizedBox(height: 8),

                    // Score & Action Row
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          status == 'pending' ? 'Pending Review' : '$score%',
                          style: TextStyle(
                            fontSize: status == 'pending' ? 12 : 20,
                            fontWeight: FontWeight.w900,
                            color: status == 'pending' ? const Color(0xFF64748B) : const Color(0xFF10B981),
                            fontFamily: 'Outfit',
                          ),
                        ),
                        if (isQuiz)
                          OutlinedButton.icon(
                            onPressed: () => _showDetail(context, p),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: const Color(0xFF3B82F6),
                              side: const BorderSide(color: Color(0xFF3B82F6)),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                            ),
                            icon: const Icon(Icons.remove_red_eye_outlined, size: 14),
                            label: const Text('View Results', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, fontFamily: 'Outfit')),
                          )
                        else
                          ElevatedButton.icon(
                            onPressed: () {
                              final setId = p['flashcard_set_id'] ?? p['id'];
                              context.push('/flashcards/set/$setId');
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF0D9488),
                              foregroundColor: Colors.white,
                              elevation: 0,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                            ),
                            icon: const Text('Continue Study', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, fontFamily: 'Outfit')),
                            label: const Icon(Icons.arrow_forward, size: 14),
                          ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFooterJourney() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'CONTINUE YOUR JOURNEY',
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.bold,
            color: Color(0xFF64748B),
            letterSpacing: 0.5,
            fontFamily: 'Outfit',
          ),
        ),
        const SizedBox(height: 8),
        Wrap(
          spacing: 10,
          runSpacing: 10,
          crossAxisAlignment: WrapCrossAlignment.center,
          children: [
            ElevatedButton.icon(
              onPressed: () => context.go('/quizzes'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF10B981),
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              ),
              label: const Text('Take More Quizzes', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, fontFamily: 'Outfit')),
              icon: const Icon(Icons.arrow_forward, size: 16),
            ),
            ElevatedButton.icon(
              onPressed: () => context.go('/flashcards'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF3B82F6),
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              ),
              label: const Text('Study More Flashcards', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, fontFamily: 'Outfit')),
              icon: const Icon(Icons.arrow_forward, size: 16),
            ),
            TextButton.icon(
              onPressed: () => context.go('/dashboard'),
              icon: const Icon(Icons.home_outlined, size: 16, color: Color(0xFF64748B)),
              label: const Text('Back to Dashboard', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF64748B), fontFamily: 'Outfit')),
            ),
          ],
        ),
      ],
    );
  }
}

class _DetailSheet extends StatefulWidget {
  final int progressId;
  const _DetailSheet({required this.progressId});

  @override
  State<_DetailSheet> createState() => _DetailSheetState();
}

class _DetailSheetState extends State<_DetailSheet> {
  Map<String, dynamic>? data;
  bool loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      data = await ApiService.getProgressDetail(widget.progressId);
    } catch (_) {}
    setState(() => loading = false);
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      padding: const EdgeInsets.all(20),
      height: MediaQuery.of(context).size.height * 0.8,
      child: loading
          ? const Center(child: CircularProgressIndicator())
          : data == null
              ? const Center(child: Text('Failed to load results details'))
              : Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Text(
                            data!['quiz']?['title'] ?? 'Quiz Results',
                            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, fontFamily: 'Outfit'),
                          ),
                        ),
                        IconButton(
                          icon: const Icon(Icons.close),
                          onPressed: () => Navigator.pop(context),
                        ),
                      ],
                    ),
                    const Divider(),
                    Expanded(
                      child: ListView.builder(
                        itemCount: (data!['questions'] as List?)?.length ?? 0,
                        itemBuilder: (context, i) {
                          final q = data!['questions'][i];
                          final ans = (data!['student_answers'] as List?)?.elementAt(i);
                          final note = (data!['teacher_notes'] as List?)?.elementAt(i);

                          return Container(
                            margin: const EdgeInsets.only(bottom: 12),
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF8FAFC),
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text('Q${i + 1}: ${q['question_text'] ?? ''}', style: const TextStyle(fontWeight: FontWeight.bold, fontFamily: 'Outfit')),
                                const SizedBox(height: 6),
                                Text('Your Answer: ${ans ?? "None"}', style: const TextStyle(color: Color(0xFF475569), fontFamily: 'Outfit')),
                                if (q['correct_answer'] != null)
                                  Text('Correct Answer: ${q['correct_answer']}', style: const TextStyle(color: Color(0xFF166534), fontWeight: FontWeight.bold, fontFamily: 'Outfit')),
                                if (note != null && note['comment'] != null)
                                  Padding(
                                    padding: const EdgeInsets.only(top: 4),
                                    child: Text('Teacher Note: ${note['comment']}', style: const TextStyle(color: Color(0xFF2563EB), fontStyle: FontStyle.italic, fontFamily: 'Outfit')),
                                  ),
                              ],
                            ),
                          );
                        },
                      ),
                    ),
                  ],
                ),
    );
  }
}
