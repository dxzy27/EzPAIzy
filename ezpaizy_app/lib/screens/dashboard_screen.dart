import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  Map<String, dynamic>? data;
  bool loading = true;
  String? error;
  bool sessionDismissed = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      loading = true;
      error = null;
    });
    try {
      final d = await ApiService.getDashboard();
      setState(() {
        data = d;
        loading = false;
      });
    } catch (e) {
      setState(() {
        error = 'Failed to load dashboard';
        loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.read<AuthProvider>();

    final style = data?['profile']?['learning_style'] as String?;
    final persona = data?['persona'] as String?;

    // ── Per-style configuration (Matching Web dashboard.blade.php) ──
    Color accentColor = const Color(0xFF3B82F6);
    Color accentLightColor = const Color(0xFFEFF6FF);
    String styleLabel = 'Basic Learner';
    String tipIcon = '💡';
    String tipTitle = 'Study Tip';
    String tipText = 'Complete the learning style diagnosis to get personalized recommendations and custom dashboard features.';

    if (style == 'read_write') {
      accentColor = const Color(0xFF7D6867);
      accentLightColor = const Color(0xFFFAF6F6);
      styleLabel = 'Read/Write Learner';
      tipIcon = '✍️';
      tipTitle = 'Read/Write Study Tip';
      tipText = 'Use the Notepad next to your materials and quizzes to jot down summaries and acronyms. You can access all your saved notes from the "My Folders" section.';
    } else if (style == 'auditory') {
      accentColor = const Color(0xFFE5B181);
      accentLightColor = const Color(0xFFFFF7ED);
      styleLabel = 'Auditory Learner';
      tipIcon = '🎵';
      tipTitle = 'Auditory Study Tip';
      tipText = 'After reading any material today, close it and say aloud — in your own words — what you just learned. If you can explain it, you have truly encoded it.';
    } else if (style == 'competitive') {
      accentColor = const Color(0xFFEF9086);
      accentLightColor = const Color(0xFFFEF2F2);
      styleLabel = 'Competitive Learner';
      tipIcon = '🏆';
      tipTitle = 'Competitive Study Tip';
      tipText = 'Challenge: Beat your last quiz score today. Check your recent results below and pick a quiz where you scored under 90% — retake it and push for a new personal best.';
    }

    return Scaffold(
      backgroundColor: Colors.transparent, // transparency allows gradient body
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 0,
        title: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Image.asset(
              'assets/images/logo.png',
              height: 28,
              errorBuilder: (_, __, ___) => const Icon(Icons.school, color: Color(0xFF3B82F6)),
            ),
            const SizedBox(width: 6),
            const Text(
              'EzPAIzy',
              style: TextStyle(
                color: Color(0xFF1E293B),
                fontWeight: FontWeight.bold,
                fontSize: 18,
              ),
            ),
          ],
        ),
        centerTitle: true,
        actions: [
          IconButton(
            icon: const Icon(Icons.logout, color: Color(0xFF64748B)),
            onPressed: () async {
              await auth.logout();
              if (context.mounted) context.go('/login');
            },
          ),
        ],
      ),
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [
              Color(0xFFF0F7FF),
              Color(0xFFE0EDFF),
              Color(0xFFEDE9FE),
            ],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: loading
            ? const Center(child: CircularProgressIndicator())
            : error != null
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.wifi_off, size: 48, color: Colors.grey),
                        const SizedBox(height: 12),
                        Text(error!, style: const TextStyle(color: Colors.grey)),
                        const SizedBox(height: 16),
                        ElevatedButton(onPressed: _load, child: const Text('Retry')),
                      ],
                    ),
                  )
                : RefreshIndicator(
                    onRefresh: _load,
                    child: SingleChildScrollView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // ── Welcome & Style Badge ──
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text(
                                      'Student Dashboard',
                                      style: TextStyle(
                                        fontSize: 20,
                                        fontWeight: FontWeight.bold,
                                        color: Color(0xFF1E293B),
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Row(
                                      children: [
                                        Text(
                                          'Welcome, ${data?['user']?['name'] ?? 'Student'}',
                                          style: const TextStyle(fontSize: 13, color: Color(0xFF64748B)),
                                        ),
                                        if (style != null) ...[
                                          const SizedBox(width: 8),
                                          _buildStyleBadge(style, persona ?? styleLabel),
                                        ],
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                              if (style != null)
                                TextButton.icon(
                                  onPressed: () => context.go('/learning-profile'),
                                  icon: const Icon(Icons.bar_chart, size: 16),
                                  label: const Text('My Profile', style: TextStyle(fontWeight: FontWeight.bold)),
                                  style: TextButton.styleFrom(
                                    foregroundColor: accentColor,
                                    padding: EdgeInsets.zero,
                                    minimumSize: Size.zero,
                                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                  ),
                                ),
                            ],
                          ),
                          const SizedBox(height: 20),

                          // ── Diagnosis Banner (For Undiagnosed) ──
                          if (style == null) _buildDiagnosisBanner(),

                          // ── Study Tip Card (Dynamic advice card mirroring web tip) ──
                          _buildTipCard(accentColor, accentLightColor, tipIcon, tipTitle, tipText),
                          const SizedBox(height: 20),

                          // ── Stats Cards Section (Dynamic ordering matching web layout) ──
                          _buildStatsRow(style, accentColor),
                          const SizedBox(height: 24),

                          // ── Quick Access ──
                          const Text(
                            'Quick Access',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF1E293B),
                            ),
                          ),
                          const SizedBox(height: 12),

                          GridView.count(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            crossAxisCount: 3,
                            crossAxisSpacing: 12,
                            mainAxisSpacing: 12,
                            childAspectRatio: 1.1,
                            children: [
                              _navCard('Quizzes', Icons.quiz, Colors.blue, () => context.go('/quizzes')),
                              _navCard('Flashcards', Icons.style, Colors.purple, () => context.go('/flashcards')),
                              _navCard('Progress', Icons.bar_chart, Colors.orange, () => context.go('/progress')),
                              _navCard('Materials', Icons.menu_book, Colors.teal, () => context.go('/contents')),
                              _navCard('Daily Quran', Icons.auto_stories, Colors.green, () => context.go('/daily-quran')),
                              _navCard('My Style', Icons.assignment_ind, Colors.indigo, () => context.go('/learning-profile')),
                            ],
                          ),

                          // ── Recent Results ──
                          if ((data?['recent_results'] as List?)?.isNotEmpty == true) ...[
                            const SizedBox(height: 26),
                            const Text(
                              'Recent Results',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: Color(0xFF1E293B),
                              ),
                            ),
                            const SizedBox(height: 12),
                            ...((data!['recent_results'] as List).take(3).map((p) {
                              final score = p['score'] ?? 0;
                              final isPending = p['status'] == 'pending';
                              final color = isPending
                                  ? Colors.grey
                                  : score >= 70
                                      ? Colors.green
                                      : score >= 50
                                          ? Colors.orange
                                          : Colors.red;
                              return Container(
                                margin: const EdgeInsets.only(bottom: 10),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(16),
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withOpacity(0.02),
                                      blurRadius: 10,
                                      offset: const Offset(0, 4),
                                    ),
                                  ],
                                ),
                                child: ListTile(
                                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                                  title: Text(
                                    p['quiz']?['title'] ?? 'Quiz',
                                    style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                                  ),
                                  subtitle: Text(
                                    p['quiz']?['teacher']?['name'] ?? 'Teacher',
                                    style: const TextStyle(color: Color(0xFF64748B), fontSize: 13),
                                  ),
                                  trailing: Chip(
                                    label: Text(
                                      isPending ? 'PENDING' : '$score%',
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontWeight: FontWeight.bold,
                                        fontSize: 12,
                                      ),
                                    ),
                                    backgroundColor: color,
                                    padding: EdgeInsets.zero,
                                    visualDensity: VisualDensity.compact,
                                    side: BorderSide.none,
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                                  ),
                                ),
                              );
                            })),
                          ],
                        ],
                      ),
                    ),
                  ),
      ),
    );
  }

  Widget _buildStyleBadge(String style, String label) {
    Color bg = const Color(0xFFEFF6FF);
    Color fg = const Color(0xFF3B82F6);
    Color border = const Color(0xFF3B82F6);
    IconData icon = Icons.person;

    if (style == 'read_write') {
      bg = const Color(0xFFFAF6F6);
      fg = const Color(0xFF453938);
      border = const Color(0xFF7D6867);
      icon = Icons.edit_note;
    } else if (style == 'auditory') {
      bg = const Color(0xFFFFF7ED);
      fg = const Color(0xFF7C2D12);
      border = const Color(0xFFE5B181);
      icon = Icons.hearing;
    } else if (style == 'competitive') {
      bg = const Color(0xFFFEF2F2);
      fg = const Color(0xFF991B1B);
      border = const Color(0xFFEF9086);
      icon = Icons.emoji_events;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: border),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: fg, size: 12),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(color: fg, fontSize: 10, fontWeight: FontWeight.bold),
          ),
        ],
      ),
    );
  }

  Widget _buildDiagnosisBanner() {
    if (sessionDismissed) return const SizedBox.shrink();
    return Container(
      margin: const EdgeInsets.only(bottom: 20),
      width: double.infinity,
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF7C3AED), Color(0xFF4F46E5)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF4F46E5).withOpacity(0.3),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.18),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Text('🧠', style: TextStyle(fontSize: 20)),
                ),
                const SizedBox(width: 12),
                const Expanded(
                  child: Text(
                    'Discover Your Learning Style',
                    style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            const Text(
              'Take a 10-question diagnosis to determine your learning styles to study how you learn best.',
              style: TextStyle(color: Colors.white70, fontSize: 13, height: 1.4),
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                ElevatedButton.icon(
                  onPressed: () => context.go('/learning-style'),
                  icon: const Icon(Icons.assignment, size: 16, color: Color(0xFF5B21B6)),
                  label: const Text('Start Diagnosis', style: TextStyle(color: Color(0xFF5B21B6), fontWeight: FontWeight.bold)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    minimumSize: Size.zero,
                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  ),
                ),
                const SizedBox(width: 10),
                TextButton(
                  onPressed: () {
                    setState(() {
                      sessionDismissed = true;
                    });
                  },
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    minimumSize: Size.zero,
                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    foregroundColor: Colors.white70,
                  ),
                  child: const Text('Maybe later', style: TextStyle(fontWeight: FontWeight.w600)),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTipCard(Color accent, Color bg, String emoji, String title, String tip) {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border(top: BorderSide(color: accent, width: 5)),
        boxShadow: [
          BoxShadow(
            color: accent.withOpacity(0.06),
            blurRadius: 20,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: bg,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(emoji, style: const TextStyle(fontSize: 22)),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: accent),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    tip,
                    style: const TextStyle(color: Color(0xFF64748B), fontSize: 13, height: 1.45),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatsRow(String? style, Color accentColor) {
    final quizCount = '${data?['quiz_count'] ?? 0}';
    final materialsCount = '${data?['materials_count'] ?? 0}';
    final completedCount = '${data?['completed_count'] ?? 0}';

    final qCard = _statCard('📝', 'Quizzes', quizCount, Colors.blue, accentColor);
    final mCard = _statCard('📚', 'Materials', materialsCount, Colors.green, accentColor);
    final cCard = _statCard('🏆', 'Done', completedCount, Colors.orange, accentColor);

    // Dynamic ordering: Competitive learner prioritizes quizzes, others prioritize materials
    List<Widget> cards;
    if (style == 'competitive') {
      cards = [qCard, const SizedBox(width: 12), mCard, const SizedBox(width: 12), cCard];
    } else {
      cards = [mCard, const SizedBox(width: 12), qCard, const SizedBox(width: 12), cCard];
    }

    return Row(
      children: cards,
    );
  }

  Widget _statCard(String emoji, String label, String value, Color color, Color accent) {
    return Expanded(
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border(top: BorderSide(color: accent.withOpacity(0.4), width: 3)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.02),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 8),
          child: Column(
            children: [
              Text(emoji, style: const TextStyle(fontSize: 22)),
              const SizedBox(height: 6),
              Text(
                value,
                style: TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  color: color,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                label,
                style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _navCard(String label, IconData icon, Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.02),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withOpacity(0.08),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, color: color, size: 24),
            ),
            const SizedBox(height: 8),
            Text(
              label,
              style: const TextStyle(
                fontWeight: FontWeight.bold,
                color: Color(0xFF1E293B),
                fontSize: 12,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
