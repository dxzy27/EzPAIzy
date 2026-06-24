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
  List<dynamic> noteFolders = [];

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
      List<dynamic> folders = [];

      final style = (d['user']?['learning_style'] ?? d['profile']?['learning_style']) as String?;
      if (style == 'read_write') {
        folders = await ApiService.getNoteFolders();
      }

      setState(() {
        data = d;
        noteFolders = folders;
        loading = false;
      });
      if (d['user'] != null && mounted) {
        Provider.of<AuthProvider>(context, listen: false).setUser(d['user']);
      }
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

    final style = (data?['user']?['learning_style'] ?? data?['profile']?['learning_style']) as String?;
    final persona = data?['persona'] as String?;

    // ── Per-style configuration (Matching Web dashboard.blade.php) ──
    Color accentColor = const Color(0xFF3B82F6);
    Color accentLightColor = const Color(0xFFEFF6FF);
    Color accentTextColor = const Color(0xFF1E3A8A);
    String styleLabel = 'Basic Learner';
    String tipIcon = '💡';
    String tipTitle = 'Study Tip';
    String tipText = 'Complete the learning style diagnosis to get personalized recommendations and custom dashboard features.';

    if (style == 'read_write') {
      accentColor = const Color(0xFF7D6867);
      accentLightColor = const Color(0xFFFAF6F6);
      accentTextColor = const Color(0xFF453938);
      styleLabel = 'Read/Write Learner';
      tipIcon = '✍️';
      tipTitle = 'Read/Write Study Tip';
      tipText = 'Use the Notepad next to your materials and quizzes to jot down summaries and acronyms. You can access all your saved notes from the "My Folders" section.';
    } else if (style == 'auditory') {
      accentColor = const Color(0xFFE5B181);
      accentLightColor = const Color(0xFFFFF7ED);
      accentTextColor = const Color(0xFF7C2D12);
      styleLabel = 'Auditory Learner';
      tipIcon = '🎵';
      tipTitle = 'Auditory Study Tip';
      tipText = 'After reading any material today, close it and say aloud — in your own words — what you just learned. If you can explain it, you have truly encoded it.';
    } else if (style == 'competitive') {
      accentColor = const Color(0xFFEF9086);
      accentLightColor = const Color(0xFFFEF2F2);
      accentTextColor = const Color(0xFF991B1B);
      styleLabel = 'Competitive Learner';
      tipIcon = '🏆';
      tipTitle = 'Competitive Study Tip';
      tipText = 'Challenge: Beat your last quiz score today. Check your recent results below and pick a quiz where you scored under 90% — retake it and push for a new personal best.';
    }

    final width = MediaQuery.of(context).size.width;
    final isTablet = width >= 600;

    // Build the 3 stats cards
    final materialsCard = _buildStatCard(
      isPrimary: style == 'read_write' || style == 'auditory',
      accentColor: accentColor,
      accentLightColor: accentLightColor,
      accentTextColor: accentTextColor,
      title: '📚 Materials',
      value: '${data?['materials_count'] ?? 0}',
      subtitle: 'Available Materials',
      actionArea: Row(
        children: [
          Expanded(
            child: OutlinedButton.icon(
              onPressed: () => context.go('/flashcards'),
              icon: const Icon(Icons.collections_bookmark_outlined, size: 14),
              label: const Text('Flashcards', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
              style: OutlinedButton.styleFrom(
                foregroundColor: const Color(0xFF10B981),
                side: const BorderSide(color: Color(0xFF10B981)),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                padding: const EdgeInsets.symmetric(vertical: 10),
              ),
            ),
          ),
          const SizedBox(width: 6),
          Expanded(
            child: OutlinedButton.icon(
              onPressed: () => context.go('/contents'),
              icon: const Icon(Icons.article_outlined, size: 14),
              label: const Text('Other Materials', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
              style: OutlinedButton.styleFrom(
                foregroundColor: const Color(0xFF3B82F6),
                side: const BorderSide(color: Color(0xFF3B82F6)),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                padding: const EdgeInsets.symmetric(vertical: 10),
              ),
            ),
          ),
        ],
      ),
    );

    final quizzesCard = _buildStatCard(
      isPrimary: style == 'competitive',
      accentColor: accentColor,
      accentLightColor: accentLightColor,
      accentTextColor: accentTextColor,
      title: style == 'competitive' ? '🏆 Quizzes' : '📝 Quizzes',
      value: style == 'competitive' && data?['best_score'] != null
          ? '${data!['best_score']}%'
          : '${data?['quiz_count'] ?? 0}',
      subtitle: style == 'competitive' && data?['best_score'] != null
          ? 'Your Best Score'
          : 'Available Quizzes',
      actionArea: OutlinedButton.icon(
        onPressed: () => context.go('/quizzes'),
        icon: const Icon(Icons.play_circle_outline, size: 16),
        label: const Text('Browse Quizzes', style: TextStyle(fontWeight: FontWeight.bold)),
        style: OutlinedButton.styleFrom(
          foregroundColor: const Color(0xFF3B82F6),
          side: const BorderSide(color: Color(0xFF3B82F6)),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
          padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 16),
        ),
      ),
    );

    final completedCard = _buildStatCard(
      isPrimary: false,
      accentColor: accentColor,
      accentLightColor: accentLightColor,
      accentTextColor: accentTextColor,
      title: '✅ Completed',
      value: '${data?['completed_count'] ?? 0}',
      subtitle: 'Quizzes Completed',
      actionArea: OutlinedButton.icon(
        onPressed: () => context.go('/progress'),
        icon: const Icon(Icons.bar_chart_outlined, size: 16),
        label: const Text('View Progress', style: TextStyle(fontWeight: FontWeight.bold)),
        style: OutlinedButton.styleFrom(
          foregroundColor: const Color(0xFF06B6D4),
          side: const BorderSide(color: Color(0xFF06B6D4)),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
          padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 16),
        ),
      ),
    );

    List<Widget> orderedCards;
    if (style == 'read_write' || style == 'auditory') {
      orderedCards = [materialsCard, quizzesCard, completedCard];
    } else {
      orderedCards = [quizzesCard, materialsCard, completedCard];
    }

    return Scaffold(
      backgroundColor: Colors.transparent,
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
                          // ── Welcome Header with yellow "My Progress" button ──
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text(
                                      'Student Dashboard',
                                      style: TextStyle(
                                        fontSize: 22,
                                        fontWeight: FontWeight.bold,
                                        color: Color(0xFF1E293B),
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Wrap(
                                      crossAxisAlignment: WrapCrossAlignment.center,
                                      spacing: 8,
                                      runSpacing: 4,
                                      children: [
                                        Text(
                                          'Welcome, ${data?['user']?['name'] ?? 'Student'}',
                                          style: const TextStyle(fontSize: 14, color: Color(0xFF64748B)),
                                        ),
                                        if (style != null)
                                          _buildStyleBadge(style, persona ?? styleLabel),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(width: 8),
                              ElevatedButton.icon(
                                onPressed: () => context.go('/progress'),
                                icon: const Icon(Icons.bar_chart, color: Color(0xFF1E293B), size: 16),
                                label: const Text(
                                  'My Progress',
                                  style: TextStyle(
                                    color: Color(0xFF1E293B),
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12,
                                  ),
                                ),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFFFFC107), // My Progress warning yellow
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  elevation: 0,
                                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                  minimumSize: Size.zero,
                                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 20),

                          // ── Diagnosis Banner (For Undiagnosed) ──
                          if (style == null) ...[
                            _buildDiagnosisBanner(),
                            const SizedBox(height: 20),
                          ],

                          // ── Study Tip Card (diagnosed students only) ──
                          if (style != null) ...[
                            _buildTipCard(accentColor, accentLightColor, accentTextColor, tipIcon, tipTitle, tipText),
                            const SizedBox(height: 20),
                          ],

                          // ── Stats Cards Row/Column ──
                          isTablet
                              ? Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: orderedCards.map((card) => Expanded(child: card)).toList(),
                                )
                              : Column(
                                  crossAxisAlignment: CrossAxisAlignment.stretch,
                                  children: orderedCards,
                                ),
                          const SizedBox(height: 10),
                          if (style == 'read_write') ...[
                            _buildFoldersSection(accentColor),
                          ],

                          // ── Bottom Panel Row/Column ──
                          width >= 800
                              ? Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Expanded(child: _buildRecentResults(style, data?['best_score'])),
                                    const SizedBox(width: 20),
                                    Expanded(child: _buildAdaptiveSection(style, accentColor)),
                                  ],
                                )
                              : Column(
                                  crossAxisAlignment: CrossAxisAlignment.stretch,
                                  children: [
                                    _buildRecentResults(style, data?['best_score']),
                                    const SizedBox(height: 20),
                                    _buildAdaptiveSection(style, accentColor),
                                  ],
                                ),
                          const SizedBox(height: 20),
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

    return InkWell(
      onTap: () => context.go('/learning-profile'),
      child: Container(
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
      ),
    );
  }

  Widget _buildDiagnosisBanner() {
    if (sessionDismissed) return const SizedBox.shrink();
    return Container(
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

  Widget _buildTipCard(Color accent, Color bg, Color textAccent, String emoji, String title, String tip) {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: accent),
      ),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(emoji, style: const TextStyle(fontSize: 28)),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title.toUpperCase(),
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                      color: textAccent,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    tip,
                    style: TextStyle(color: textAccent, fontSize: 13, height: 1.5),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard({
    required bool isPrimary,
    required Color accentColor,
    required Color accentLightColor,
    required Color accentTextColor,
    required String title,
    required String value,
    required String subtitle,
    required Widget actionArea,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16, left: 4, right: 4),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: isPrimary
            ? Border.all(color: accentColor, width: 2)
            : Border.all(color: Colors.grey.withOpacity(0.15), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          if (isPrimary)
            Container(
              height: 4,
              decoration: BoxDecoration(
                color: accentColor,
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(18),
                  topRight: Radius.circular(18),
                ),
              ),
            ),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 16),
            child: Column(
              children: [
                if (isPrimary) ...[
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: accentLightColor,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      '⭐ Recommended for you',
                      style: TextStyle(
                        color: accentTextColor,
                        fontWeight: FontWeight.bold,
                        fontSize: 11,
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                ],
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF1E293B),
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 8),
                Text(
                  value,
                  style: TextStyle(
                    fontSize: 32,
                    fontWeight: FontWeight.bold,
                    color: isPrimary ? accentColor : const Color(0xFF14B8A6),
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 4),
                Text(
                  subtitle,
                  style: const TextStyle(
                    fontSize: 13,
                    color: Color(0xFF64748B),
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 16),
                actionArea,
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRecentResults(String? style, int? bestScore) {
    final list = data?['recent_results'] as List?;
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 20, 20, 12),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Recent Results',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF1E293B),
                  ),
                ),
                if (style == 'competitive' && bestScore != null)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF2F2),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      '🏆 Best: $bestScore%',
                      style: const TextStyle(
                        color: Color(0xFF991B1B),
                        fontWeight: FontWeight.bold,
                        fontSize: 11,
                      ),
                    ),
                  ),
              ],
            ),
          ),
          const Divider(height: 1),
          Padding(
            padding: const EdgeInsets.all(20),
            child: list == null || list.isEmpty
                ? Center(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(vertical: 24),
                      child: Column(
                        children: [
                          const Text(
                            'No quizzes completed yet. ',
                            style: TextStyle(color: Color(0xFF64748B), fontSize: 13),
                          ),
                          const SizedBox(height: 8),
                          TextButton(
                            onPressed: () => context.go('/quizzes'),
                            child: const Text('Take a quiz!', style: TextStyle(fontWeight: FontWeight.bold)),
                          ),
                        ],
                      ),
                    ),
                  )
                : SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Table(
                      defaultColumnWidth: const FixedColumnWidth(100),
                      columnWidths: const {
                        0: FixedColumnWidth(130), // Quiz title
                        1: FixedColumnWidth(70),  // By
                        2: FixedColumnWidth(70),  // Score
                        3: FixedColumnWidth(60),  // Date
                        4: FixedColumnWidth(40),  // Action
                      },
                      defaultVerticalAlignment: TableCellVerticalAlignment.middle,
                      children: [
                        const TableRow(
                          children: [
                            TableCell(child: Padding(padding: EdgeInsets.only(bottom: 8), child: Text('Quiz', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF64748B))))),
                            TableCell(child: Padding(padding: EdgeInsets.only(bottom: 8), child: Text('By', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF64748B))))),
                            TableCell(child: Padding(padding: EdgeInsets.only(bottom: 8), child: Text('Score', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF64748B))))),
                            TableCell(child: Padding(padding: EdgeInsets.only(bottom: 8), child: Text('Date', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF64748B))))),
                            TableCell(child: Padding(padding: EdgeInsets.only(bottom: 8), child: Text('', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF64748B))))),
                          ],
                        ),
                        ...list.map((p) {
                          final score = p['score'] ?? 0;
                          final isPending = p['status'] == 'pending';
                          final title = p['quiz']?['title'] ?? 'Quiz';
                          final teacher = p['quiz']?['teacher']?['name'] ?? 'Unknown';
                          final dateStr = _formatDate(p['created_at']);
                          
                          Color badgeBg = Colors.grey;
                          Color badgeFg = Colors.white;
                          String badgeText = '$score%';

                          if (isPending) {
                            badgeText = 'Pending';
                            badgeBg = const Color(0xFFE2E8F0);
                            badgeFg = const Color(0xFF64748B);
                          } else if (score >= 80) {
                            badgeBg = const Color(0xFFDCFCE7);
                            badgeFg = const Color(0xFF15803D);
                          } else if (score >= 50) {
                            badgeBg = const Color(0xFFFEF3C7);
                            badgeFg = const Color(0xFFB45309);
                          } else {
                            badgeBg = const Color(0xFFFEE2E2);
                            badgeFg = const Color(0xFFB91C1C);
                          }

                          return TableRow(
                            children: [
                              TableCell(
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(vertical: 6),
                                  child: Text(
                                    title,
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF1E293B)),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ),
                              TableCell(
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(vertical: 6),
                                  child: Text(
                                    teacher,
                                    style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ),
                              TableCell(
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(vertical: 6),
                                  child: Align(
                                    alignment: Alignment.centerLeft,
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: badgeBg,
                                        borderRadius: BorderRadius.circular(4),
                                      ),
                                      child: Text(
                                        badgeText,
                                        style: TextStyle(color: badgeFg, fontWeight: FontWeight.bold, fontSize: 10),
                                      ),
                                    ),
                                  ),
                                ),
                              ),
                              TableCell(
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(vertical: 6),
                                  child: Text(
                                    dateStr,
                                    style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                                  ),
                                ),
                              ),
                              TableCell(
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(vertical: 6),
                                  child: IconButton(
                                    icon: const Icon(Icons.visibility, size: 16, color: Color(0xFF3B82F6)),
                                    onPressed: () => context.go('/progress'),
                                    padding: EdgeInsets.zero,
                                    constraints: const BoxConstraints(),
                                  ),
                                ),
                              ),
                            ],
                          );
                        }),
                      ],
                    ),
                  ),
          ),
        ],
      ),
    );
  }

  String _formatDate(String? raw) {
    if (raw == null) return '';
    try {
      final dt = DateTime.parse(raw);
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      return '${months[dt.month - 1]} ${dt.day.toString().padLeft(2, '0')}';
    } catch (_) {
      return '';
    }
  }

  Widget _buildAdaptiveSection(String? style, Color accentColor) {
    String cardTitle = 'New Learning Materials';
    if (style == 'competitive') {
      cardTitle = '🏆 Class Leaderboard';
    } else if (style == 'read_write') {
      cardTitle = '✨ Recommended: Your Saved Notes & Materials';
    } else if (style == 'auditory') {
      cardTitle = '✨ Recent Listenable Materials';
    }

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 20, 20, 12),
            child: Row(
              children: [
                if (style == 'competitive' || style == 'read_write' || style == 'auditory')
                  Container(
                    width: 3,
                    height: 18,
                    decoration: BoxDecoration(
                      color: accentColor,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                if (style == 'competitive' || style == 'read_write' || style == 'auditory')
                  const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    cardTitle,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF1E293B),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1),
          Padding(
            padding: const EdgeInsets.all(20),
            child: _buildAdaptiveBody(style),
          ),
        ],
      ),
    );
  }

  Widget _buildAdaptiveBody(String? style) {
    if (style == 'competitive') {
      return _buildLeaderboard();
    } else if (style == 'auditory') {
      return _buildAuditoryList();
    } else {
      return _buildDefaultOrReadWriteList(style);
    }
  }

  Widget _buildLeaderboard() {
    final list = data?['leaderboard'] as List?;
    if (list == null || list.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.symmetric(vertical: 24),
          child: Text('No classmates found.', style: TextStyle(color: Color(0xFF64748B), fontSize: 13)),
        ),
      );
    }

    final currentUserId = data?['user']?['id'];

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Table(
        defaultColumnWidth: const FixedColumnWidth(100),
        columnWidths: const {
          0: FixedColumnWidth(50),  // Rank
          1: FixedColumnWidth(130), // Student
          2: FixedColumnWidth(90),  // Quizzes completed
          3: FixedColumnWidth(70),  // Points
        },
        defaultVerticalAlignment: TableCellVerticalAlignment.middle,
        children: [
          const TableRow(
            children: [
              TableCell(child: Padding(padding: EdgeInsets.only(bottom: 8), child: Text('Rank', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF64748B))))),
              TableCell(child: Padding(padding: EdgeInsets.only(bottom: 8), child: Text('Student', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF64748B))))),
              TableCell(child: Padding(padding: EdgeInsets.only(bottom: 8), child: Text('Quizzes', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF64748B)), textAlign: TextAlign.end))),
              TableCell(child: Padding(padding: EdgeInsets.only(bottom: 8), child: Text('Points', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF64748B)), textAlign: TextAlign.end))),
            ],
          ),
          ...list.asMap().entries.map((entry) {
            final idx = entry.key;
            final item = entry.value;
            final rank = idx + 1;
            final isSelf = item['id'] == currentUserId;
            
            String rankSymbol = '#$rank';
            if (rank == 1) {
              rankSymbol = '🥇';
            } else if (rank == 2) {
              rankSymbol = '🥈';
            } else if (rank == 3) {
              rankSymbol = '🥉';
            }

            return TableRow(
              decoration: isSelf ? const BoxDecoration(
                color: Color(0xFFFFFBEB),
              ) : null,
              children: [
                TableCell(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
                    child: Text(
                      rankSymbol,
                      style: TextStyle(
                        fontSize: (rank <= 3) ? 18 : 12,
                        fontWeight: isSelf ? FontWeight.bold : FontWeight.normal,
                      ),
                    ),
                  ),
                ),
                TableCell(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Expanded(
                          child: Text(
                            item['name'] ?? '',
                            style: TextStyle(
                              fontWeight: isSelf ? FontWeight.bold : FontWeight.normal,
                              fontSize: 12,
                              color: const Color(0xFF1E293B),
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        if (isSelf) ...[
                          const SizedBox(width: 4),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                            decoration: BoxDecoration(
                              color: const Color(0xFFFEF3C7),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: const Text(
                              'You',
                              style: TextStyle(color: Color(0xFFB45309), fontSize: 8, fontWeight: FontWeight.bold),
                            ),
                          ),
                        ]
                      ],
                    ),
                  ),
                ),
                TableCell(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
                    child: Text(
                      '${item['completed_count'] ?? 0} Completed',
                      style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                      textAlign: TextAlign.end,
                    ),
                  ),
                ),
                TableCell(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
                    child: Text(
                      '${item['points'] ?? 0} pts',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        color: isSelf ? const Color(0xFFD97706) : const Color(0xFF3B82F6),
                        fontSize: 12,
                      ),
                      textAlign: TextAlign.end,
                    ),
                  ),
                ),
              ],
            );
          }),
        ],
      ),
    );
  }

  Widget _buildAuditoryList() {
    final list = data?['new_materials'] as List?;
    if (list == null || list.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.symmetric(vertical: 24),
          child: Text('No materials available.', style: TextStyle(color: Color(0xFF64748B), fontSize: 13)),
        ),
      );
    }

    return Column(
      children: list.map((item) {
        final isFlash = item['type'] == 'Flashcard';
        final title = item['title'] ?? '';
        final topic = item['topic'] ?? 'General';
        final itemId = item['id'];

        return InkWell(
          onTap: () {
            if (isFlash) {
              context.go('/flashcards/$itemId');
            } else {
              context.go('/contents/$itemId');
            }
          },
          child: Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFFF0F9FF),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFBAE6FD)),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
                  decoration: BoxDecoration(
                    color: isFlash ? const Color(0xFFDCFCE7) : const Color(0xFFE0F2FE),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    isFlash ? '🃏 Flashcard' : '📄 Material ⭐',
                    style: TextStyle(
                      color: isFlash ? const Color(0xFF166534) : const Color(0xFF0C4A6E),
                      fontWeight: FontWeight.bold,
                      fontSize: 10,
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 12,
                          color: Color(0xFF0F172A),
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 2),
                      Text(
                        topic,
                        style: const TextStyle(fontSize: 10, color: Color(0xFF64748B)),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF0F9FF),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: const Color(0xFF7DD3FC)),
                  ),
                  child: Text(
                    isFlash ? 'Practice' : 'Read',
                    style: const TextStyle(
                      color: Color(0xFF0C4A6E),
                      fontWeight: FontWeight.bold,
                      fontSize: 11,
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      }).toList(),
    );
  }

  Widget _buildDefaultOrReadWriteList(String? style) {
    final list = data?['new_materials'] as List?;
    if (list == null || list.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.symmetric(vertical: 24),
          child: Text('No materials available.', style: TextStyle(color: Color(0xFF64748B), fontSize: 13)),
        ),
      );
    }

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Table(
        defaultColumnWidth: const FixedColumnWidth(100),
        columnWidths: const {
          0: FixedColumnWidth(160), // Title
          1: FixedColumnWidth(90),  // Type
          2: FixedColumnWidth(80),  // Action
        },
        defaultVerticalAlignment: TableCellVerticalAlignment.middle,
        children: [
          const TableRow(
            children: [
              TableCell(child: Padding(padding: EdgeInsets.only(bottom: 8), child: Text('Title', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF64748B))))),
              TableCell(child: Padding(padding: EdgeInsets.only(bottom: 8), child: Text('Type', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF64748B))))),
              TableCell(child: Padding(padding: EdgeInsets.only(bottom: 8), child: Text('', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 11, color: Color(0xFF64748B))))),
            ],
          ),
          ...list.map((item) {
            final isFlash = item['type'] == 'Flashcard';
            final title = item['title'] ?? '';
            final actionText = item['action'] ?? '';
            final itemId = item['id'];

            Color badgeBg;
            Color badgeFg;
            String typeLabel;

            if (style == 'read_write') {
              if (isFlash) {
                typeLabel = 'Flashcard ⭐';
                badgeBg = const Color(0xFFEDE9FE);
                badgeFg = const Color(0xFF6D28D9);
              } else {
                typeLabel = 'Content';
                badgeBg = const Color(0xFFDBEAFE);
                badgeFg = const Color(0xFF1D4ED8);
              }
            } else {
              if (isFlash) {
                typeLabel = 'Flashcard';
                badgeBg = const Color(0xFFDCFCE7);
                badgeFg = const Color(0xFF15803D);
              } else {
                typeLabel = 'Content';
                badgeBg = const Color(0xFFDBEAFE);
                badgeFg = const Color(0xFF1D4ED8);
              }
            }

            return TableRow(
              children: [
                TableCell(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    child: Text(
                      title,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFF1E293B)),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ),
                TableCell(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    child: Align(
                      alignment: Alignment.centerLeft,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: badgeBg,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          typeLabel,
                          style: TextStyle(color: badgeFg, fontWeight: FontWeight.bold, fontSize: 10),
                        ),
                      ),
                    ),
                  ),
                ),
                TableCell(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    child: Align(
                      alignment: Alignment.centerRight,
                      child: OutlinedButton(
                        onPressed: () {
                          if (isFlash) {
                            context.go('/flashcards/$itemId');
                          } else {
                            context.go('/contents/$itemId');
                          }
                        },
                        style: OutlinedButton.styleFrom(
                          foregroundColor: isFlash ? const Color(0xFF10B981) : const Color(0xFF3B82F6),
                          side: BorderSide(color: isFlash ? const Color(0xFF10B981) : const Color(0xFF3B82F6)),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                          minimumSize: Size.zero,
                          tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        ),
                        child: Text(
                          actionText,
                          style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            );
          }),
        ],
      ),
    );
  }

  Widget _buildFoldersSection(Color accentColor) {
    if (noteFolders.isEmpty) {
      return Container(
        margin: const EdgeInsets.only(top: 20),
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.02),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: const Column(
          children: [
            Icon(Icons.folder_open, size: 48, color: Colors.grey),
            SizedBox(height: 10),
            Text(
              'My Folders is Empty',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF1E293B)),
            ),
            SizedBox(height: 6),
            Text(
              'Your saved study notes will appear here grouped by topic folders.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Color(0xFF64748B), fontSize: 12),
            ),
          ],
        ),
      );
    }

    return Container(
      margin: const EdgeInsets.only(top: 20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 20, 20, 12),
            child: Row(
              children: [
                Container(
                  width: 3,
                  height: 18,
                  decoration: BoxDecoration(
                    color: accentColor,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
                const SizedBox(width: 8),
                const Expanded(
                  child: Text(
                    '📁 My Folders (Study Notes)',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF1E293B),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            padding: const EdgeInsets.all(20),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
              childAspectRatio: 2.2,
            ),
            itemCount: noteFolders.length,
            itemBuilder: (context, index) {
              final folderTopic = noteFolders[index].toString();
              return InkWell(
                onTap: () => context.go('/notes/folder/${Uri.encodeComponent(folderTopic)}'),
                child: Container(
                  decoration: BoxDecoration(
                    color: const Color(0xFFFAF6F6),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: const Color(0xFF7D6867).withOpacity(0.2)),
                  ),
                  padding: const EdgeInsets.all(12),
                  child: Row(
                    children: [
                      const Icon(Icons.folder_shared, color: Color(0xFF7D6867), size: 28),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(
                              folderTopic,
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                                color: Color(0xFF453938),
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 2),
                            const Text(
                              'View Notes',
                              style: TextStyle(fontSize: 10, color: Color(0xFF7D6867), fontWeight: FontWeight.w600),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ],
      ),
    );
  }
}
