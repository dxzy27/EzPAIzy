import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:fl_chart/fl_chart.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';

class LearningProfileScreen extends StatefulWidget {
  const LearningProfileScreen({super.key});

  @override
  State<LearningProfileScreen> createState() => _LearningProfileScreenState();
}

class _LearningProfileScreenState extends State<LearningProfileScreen> {
  Map<String, dynamic>? _profile;
  Map<String, dynamic>? _dashboardData;
  List<dynamic> _progress = [];
  List<dynamic> _revision = [];
  bool _loading = true;
  bool _showVark = false;

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    setState(() => _loading = true);
    try {
      final profile = await ApiService.getDiagnosis();
      final dashboard = await ApiService.getDashboard();
      final progress = await ApiService.getProgress();
      final revision = await ApiService.getRevision();
      setState(() {
        _profile = profile;
        _dashboardData = dashboard;
        _progress = progress;
        _revision = revision;
        _loading = false;
      });
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  Future<void> _resetProfile() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Reset Learning Style?'),
        content: const Text('Are you sure you want to reset your learning style and return to the Basic UI?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red, foregroundColor: Colors.white),
            child: const Text('Reset'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      setState(() => _loading = true);
      final ok = await ApiService.resetDiagnosis();
      setState(() => _loading = false);
      if (ok && mounted) {
        context.go('/dashboard');
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Failed to reset profile. Please try again.')),
        );
      }
    }
  }

  double get avgScore {
    final graded = _progress
        .where((p) => (p['quiz']?['difficulty'] != 'hard' && p['quiz']?['difficulty'] != 'medium') || p['status'] == 'graded')
        .where((p) => (p['score'] ?? 0) > 0)
        .toList();
    if (graded.isEmpty) return 0;
    final sum = graded.fold<double>(0, (acc, p) => acc + (p['score'] ?? 0).toDouble());
    return sum / graded.length;
  }

  String _formatMemberDate(String? raw) {
    if (raw == null) return 'Jul 19, 2026';
    try {
      final dt = DateTime.parse(raw);
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      return '${months[dt.month - 1]} ${dt.day.toString().padLeft(2, '0')}, ${dt.year}';
    } catch (_) {
      return 'Jul 19, 2026';
    }
  }

  @override
  Widget build(BuildContext context) {
    final style = _profile?['learning_style'] as String?;
    final persona = _profile?['persona'] as String? ?? 'Basic Learner';
    final confidence = _profile?['confidence'] != null
        ? (_profile!['confidence'] as num).toDouble()
        : 0.0;

    // User details from dashboard API
    final userMap = _dashboardData?['user'] as Map<String, dynamic>?;
    final name = userMap?['name'] as String? ?? 'DANIAL HAKIM MOHD ARIF';
    final email = userMap?['email'] as String? ?? 'danialhero434@gmail.com';
    final phone = userMap?['phone_number'] as String? ?? '01110698940';
    final className = userMap?['class_name'] as String? ?? '5A1';
    final address = userMap?['address'] as String? ?? 'PS1189 Jalan Bahagia, Kawasan Perindustrian Kecil, Pulau Sebang, 73000 Tampin, Melaka';
    final memberSince = _formatMemberDate(userMap?['created_at']);

    // Theme and style settings
    Color accentColor = const Color(0xFF3B82F6);
    LinearGradient heroGradient = const LinearGradient(
      colors: [Color(0xFF3B82F6), Color(0xFF60A5FA)],
      begin: Alignment.topLeft,
      end: Alignment.bottomRight,
    );
    String styleDesc = 'Complete your profile assessment to learn your dominant learning pathways.';
    IconData styleIcon = Icons.school;

    if (style == 'read_write') {
      accentColor = const Color(0xFF7D6867);
      heroGradient = const LinearGradient(
        colors: [Color(0xFF7D6867), Color(0xFF9B8786)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
      styleIcon = Icons.edit_note;
      styleDesc = 'You process and retain information most effectively through active textual manipulation — note-taking, acronyms, and summarizing key written details are your strongest memory anchors.';
    } else if (style == 'auditory') {
      accentColor = const Color(0xFFE5B181);
      heroGradient = const LinearGradient(
        colors: [Color(0xFFE5B181), Color(0xFFF3CCA6)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
      styleIcon = Icons.hearing;
      styleDesc = 'You learn best through sound and verbal processing — listening, speaking, reciting, and discussing are your strongest pathways to retaining information.';
    } else if (style == 'visual') {
      accentColor = const Color(0xFF06B6D4);
      heroGradient = const LinearGradient(
        colors: [Color(0xFF06B6D4), Color(0xFF67E8F9)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
      styleIcon = Icons.visibility;
      styleDesc = 'You learn best through visuals, charts, and diagrams. Visual layouts help you structure your thoughts.';
    } else if (style == 'kinesthetic') {
      accentColor = const Color(0xFFD946EF);
      heroGradient = const LinearGradient(
        colors: [Color(0xFFD946EF), Color(0xFFF472B6)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
      styleIcon = Icons.sports_handball;
      styleDesc = 'You learn best through hands-on practice, physical interactions, and self-testing flashcards.';
    }

    final auth = Provider.of<AuthProvider>(context, listen: false);
    final userName = auth.user?['name'] ?? 'Student';
    final initial = userName.isNotEmpty ? userName[0].toUpperCase() : 'D';

    final isWide = MediaQuery.of(context).size.width > 700;

    String dateStr = '05 Aug 2026';
    final rawDate = _profile?['updated_at'] ?? _profile?['created_at'];
    if (rawDate != null) {
      try {
        final dt = DateTime.parse(rawDate.toString());
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        dateStr = '${dt.day.toString().padLeft(2, '0')} ${months[dt.month - 1]} ${dt.year}';
      } catch (_) {}
    }

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
          color: const Color(0xFFF1F5F9).withValues(alpha: 0.15), // Translucent overlay matching dashboard
          child: SafeArea(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : SingleChildScrollView(
                    padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
                    child: Center(
                      child: ConstrainedBox(
                        constraints: const BoxConstraints(maxWidth: 800),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Top Navigation Bar
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Image.asset(
                                  'assets/images/newlogo.png',
                                  height: 38,
                                  errorBuilder: (_, __, ___) => const Icon(Icons.school, color: Color(0xFF3B82F6)),
                                ),
                                Row(
                                  children: [
                                    Container(
                                      width: 36,
                                      height: 36,
                                      decoration: BoxDecoration(
                                        color: const Color(0xFF14B8A6),
                                        borderRadius: BorderRadius.circular(10),
                                        boxShadow: [
                                          BoxShadow(
                                            color: Colors.black.withValues(alpha: 0.1),
                                            blurRadius: 4,
                                            offset: const Offset(0, 2),
                                          ),
                                        ],
                                      ),
                                      child: Center(
                                        child: Text(
                                          initial,
                                          style: const TextStyle(
                                            color: Colors.white,
                                            fontWeight: FontWeight.bold,
                                            fontSize: 16,
                                            fontFamily: 'Outfit',
                                          ),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                            const SizedBox(height: 20),

                            // Top Spacing / Title Row
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Expanded(
                                      child: Row(
                                        children: [
                                          Material(
                                            color: Colors.transparent,
                                            child: InkWell(
                                              borderRadius: BorderRadius.circular(20),
                                              onTap: () => context.go('/dashboard'),
                                              child: Container(
                                                width: 36,
                                                height: 36,
                                                decoration: BoxDecoration(
                                                  shape: BoxShape.circle,
                                                  border: Border.all(color: const Color(0xFFCBD5E1)),
                                                  color: Colors.white,
                                                ),
                                                child: const Icon(Icons.home_outlined, size: 18, color: Color(0xFF475569)),
                                              ),
                                            ),
                                          ),
                                          const SizedBox(width: 12),
                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                const Text(
                                                  'Your Learning Profile',
                                                  style: TextStyle(
                                                    fontSize: 20,
                                                    fontWeight: FontWeight.w900,
                                                    color: Color(0xFF1E293B),
                                                    fontFamily: 'Outfit',
                                                  ),
                                                ),
                                                Text(
                                                  style == null ? 'Not Diagnosed Yet' : 'Diagnosed on $dateStr',
                                                  style: const TextStyle(
                                                    fontSize: 12,
                                                    color: Color(0xFF64748B),
                                                    fontFamily: 'Outfit',
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    if (style != null) ...[
                                      if (isWide)
                                        Row(
                                          children: [
                                            OutlinedButton.icon(
                                              onPressed: _resetProfile,
                                              icon: const Icon(Icons.delete_outline, size: 14, color: Colors.red),
                                              label: const Text('Reset to Basic UI', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.red, fontFamily: 'Outfit')),
                                              style: OutlinedButton.styleFrom(
                                                side: const BorderSide(color: Colors.red),
                                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                              ),
                                            ),
                                            const SizedBox(width: 8),
                                            OutlinedButton.icon(
                                              onPressed: () => context.go('/learning-style?retake=true'),
                                              icon: const Icon(Icons.refresh, size: 14, color: Color(0xFF475569)),
                                              label: const Text('Retake Diagnosis', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF475569), fontFamily: 'Outfit')),
                                              style: OutlinedButton.styleFrom(
                                                side: const BorderSide(color: Color(0xFFCBD5E1)),
                                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                              ),
                                            ),
                                          ],
                                        ),
                                    ],
                                  ],
                                ),
                                if (style != null && !isWide) ...[
                                  const SizedBox(height: 12),
                                  Row(
                                    children: [
                                      Expanded(
                                        child: OutlinedButton.icon(
                                          onPressed: _resetProfile,
                                          icon: const Icon(Icons.delete_outline, size: 14, color: Colors.red),
                                          label: const Text('Reset to Basic UI', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.red, fontFamily: 'Outfit')),
                                          style: OutlinedButton.styleFrom(
                                            side: const BorderSide(color: Colors.red),
                                            padding: const EdgeInsets.symmetric(vertical: 8),
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: OutlinedButton.icon(
                                          onPressed: () => context.go('/learning-style?retake=true'),
                                          icon: const Icon(Icons.refresh, size: 14, color: Color(0xFF475569)),
                                          label: const Text('Retake Diagnosis', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF475569), fontFamily: 'Outfit')),
                                          style: OutlinedButton.styleFrom(
                                            side: const BorderSide(color: Color(0xFFCBD5E1)),
                                            padding: const EdgeInsets.symmetric(vertical: 8),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ],
                            ),
                            const SizedBox(height: 20),

                            if (style == null)
                              _buildVarkEmptyState()
                            else ...[
                              // ── Hero Profile Card (grad-{style}) ──
                              Container(
                                width: double.infinity,
                                padding: const EdgeInsets.all(24),
                                decoration: BoxDecoration(
                                  gradient: heroGradient,
                                  borderRadius: BorderRadius.circular(20),
                                  boxShadow: [
                                    BoxShadow(
                                      color: accentColor.withValues(alpha: 0.15),
                                      blurRadius: 20,
                                      offset: const Offset(0, 8),
                                    ),
                                  ],
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: Colors.white.withValues(alpha: 0.18),
                                        borderRadius: BorderRadius.circular(20),
                                        border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
                                      ),
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Icon(styleIcon, color: Colors.white, size: 14),
                                          const SizedBox(width: 6),
                                          Text(
                                            '${style.replaceAll('_', '/').toUpperCase()} LEARNER',
                                            style: const TextStyle(
                                              color: Colors.white,
                                              fontSize: 10,
                                              fontWeight: FontWeight.bold,
                                              letterSpacing: 0.5,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    const SizedBox(height: 16),
                                    Text(
                                      persona.replaceAll('Emerging ', '').replaceAll('Strong ', ''),
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 26,
                                        fontWeight: FontWeight.w800,
                                        height: 1.2,
                                        fontFamily: 'Outfit',
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    Text(
                                      styleDesc,
                                      style: const TextStyle(
                                        color: Colors.white70,
                                        fontSize: 13.5,
                                        height: 1.45,
                                        fontFamily: 'Outfit',
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(height: 24),

                              // Responsive Breakdown & Radar Chart
                              if (isWide)
                                Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Expanded(child: _buildBreakdownCard()),
                                    const SizedBox(width: 20),
                                    Expanded(child: _buildRadarChartCard(accentColor)),
                                  ],
                                )
                              else ...[
                                _buildBreakdownCard(),
                                const SizedBox(height: 24),
                                _buildRadarChartCard(accentColor),
                              ],
                              const SizedBox(height: 24),

                              // Bottom CTA Card
                              Container(
                                width: double.infinity,
                                padding: const EdgeInsets.all(24),
                                decoration: BoxDecoration(
                                  color: accentColor.withValues(alpha: 0.08),
                                  borderRadius: BorderRadius.circular(16),
                                  border: Border.all(color: accentColor.withValues(alpha: 0.3)),
                                ),
                                child: Column(
                                  children: [
                                    Text(
                                      'Ready to study your way?',
                                      style: TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.bold,
                                        color: style == 'read_write' ? const Color(0xFF453938) : accentColor,
                                        fontFamily: 'Outfit',
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    const Text(
                                      'Your dashboard is now personalised for your learning style.',
                                      textAlign: TextAlign.center,
                                      style: TextStyle(
                                        fontSize: 12,
                                        color: Color(0xFF64748B),
                                        fontFamily: 'Outfit',
                                      ),
                                    ),
                                    const SizedBox(height: 16),
                                    SizedBox(
                                      width: double.infinity,
                                      child: ElevatedButton.icon(
                                        onPressed: () => context.go('/dashboard'),
                                        icon: const Icon(Icons.home, color: Colors.white, size: 16),
                                        label: const Text(
                                          'Go to Dashboard',
                                          style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white, fontFamily: 'Outfit'),
                                        ),
                                        style: ElevatedButton.styleFrom(
                                          backgroundColor: accentColor,
                                          padding: const EdgeInsets.symmetric(vertical: 12),
                                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                          elevation: 0,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(height: 20),
                            ],
                          ],
                        ),
                      ),
                    ),
                  ),
          ),
        ),
      ),
    );
  }

  Widget _buildField(String label, String value, {bool isLongText = false}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        Text(
          label,
          style: const TextStyle(
            color: Color(0xFF94A3B8),
            fontSize: 13,
            fontFamily: 'Outfit',
          ),
        ),
        const SizedBox(height: 2),
        Text(
          value,
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: isLongText ? 14 : 16,
            fontWeight: FontWeight.w600,
            color: const Color(0xFF1E293B),
            fontFamily: 'Outfit',
            height: isLongText ? 1.4 : 1.0,
          ),
        ),
      ],
    );
  }

  Widget _buildStatColumn(String label, String value, Color color) {
    return Expanded(
      child: Column(
        children: [
          FittedBox(
            fit: BoxFit.scaleDown,
            child: Text(
              value,
              style: TextStyle(
                fontSize: 38,
                fontWeight: FontWeight.w800,
                color: color,
                fontFamily: 'Outfit',
              ),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontSize: 12,
              color: Color(0xFF64748B),
              fontWeight: FontWeight.w600,
              fontFamily: 'Outfit',
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildVarkEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            Icon(Icons.assignment_ind_outlined, size: 64, color: Colors.grey.shade300),
            const SizedBox(height: 12),
            const Text(
              'VARK Assessment not taken',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
            ),
            const SizedBox(height: 8),
            const Text(
              'Complete the assessment to get personalized radar charts and study recommendations.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Color(0xFF64748B), fontSize: 13),
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () => context.go('/learning-style'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF3B82F6),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              ),
              child: const Text('Start Assessment'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBreakdownCard() {
    final rwScore = _profile?['score_read_write'] as int? ?? 0;
    final audScore = _profile?['score_auditory'] as int? ?? 0;
    final visScore = _profile?['score_visual'] as int? ?? 0;
    final kinScore = _profile?['score_kinesthetic'] as int? ?? 0;
    final total = rwScore + audScore + visScore + kinScore;
    final maxTotal = total == 0 ? 1 : total;

    return Container(
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
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Evidence Score Breakdown',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
          ),
          const SizedBox(height: 6),
          const Text(
            'Raw weighted evidence accumulated from your 10 answers.',
            style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
          ),
          const Divider(height: 24),
          _buildProgressBar('✍️ Read/Write', rwScore, rwScore / maxTotal, const Color(0xFF7D6867)),
          const SizedBox(height: 16),
          _buildProgressBar('🎵 Auditory', audScore, audScore / maxTotal, const Color(0xFFE5B181)),
          const SizedBox(height: 16),
          _buildProgressBar('👁️ Visual', visScore, visScore / maxTotal, const Color(0xFF06B6D4)),
          const SizedBox(height: 16),
          _buildProgressBar('🤸 Kinaesthetic', kinScore, kinScore / maxTotal, const Color(0xFFD946EF)),
        ],
      ),
    );
  }

  Widget _buildProgressBar(String label, int score, double pct, Color color) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(label, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF1E293B))),
            Text('$score pts (${(pct * 100).toStringAsFixed(0)}%)',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: color)),
          ],
        ),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(4),
          child: LinearProgressIndicator(
            value: pct,
            backgroundColor: Colors.grey[100],
            valueColor: AlwaysStoppedAnimation<Color>(color),
            minHeight: 8,
          ),
        ),
      ],
    );
  }

  Widget _buildRadarChartCard(Color accent) {
    final rwScore = (_profile?['score_read_write'] as int? ?? 0).toDouble();
    final audScore = (_profile?['score_auditory'] as int? ?? 0).toDouble();
    final visScore = (_profile?['score_visual'] as int? ?? 0).toDouble();
    final kinScore = (_profile?['score_kinesthetic'] as int? ?? 0).toDouble();

    return Container(
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
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'VARK Distribution',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
          ),
          const SizedBox(height: 20),
          SizedBox(
            height: 220,
            child: RadarChart(
              RadarChartData(
                dataSets: [
                  RadarDataSet(
                    fillColor: accent.withOpacity(0.2),
                    borderColor: accent,
                    entryRadius: 3,
                    dataEntries: [
                      RadarEntry(value: rwScore),
                      RadarEntry(value: audScore),
                      RadarEntry(value: visScore),
                      RadarEntry(value: kinScore),
                    ],
                  ),
                ],
                radarShape: RadarShape.circle,
                titlePositionPercentageOffset: 0.15,
                titleTextStyle: const TextStyle(color: Color(0xFF64748B), fontSize: 10, fontWeight: FontWeight.bold),
                getTitle: (index, angle) {
                  switch (index) {
                    case 0:
                      return const RadarChartTitle(text: 'Read/Write');
                    case 1:
                      return const RadarChartTitle(text: 'Auditory');
                    case 2:
                      return const RadarChartTitle(text: 'Visual');
                    case 3:
                      return const RadarChartTitle(text: 'Kinaesthetic');
                    default:
                      return const RadarChartTitle(text: '');
                  }
                },
                tickCount: 4,
                ticksTextStyle: const TextStyle(fontSize: 8, color: Colors.transparent),
                gridBorderData: const BorderSide(color: Color(0xFFE2E8F0)),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRecommendationItem(String text, Color color) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.only(top: 2),
            child: Icon(Icons.check_circle_outline, color: color, size: 16),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(fontSize: 13, height: 1.4, color: Color(0xFF334155)),
            ),
          ),
        ],
      ),
    );
  }
}
