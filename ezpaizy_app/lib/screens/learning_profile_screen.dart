import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:fl_chart/fl_chart.dart';
import '../services/api_service.dart';

class LearningProfileScreen extends StatefulWidget {
  const LearningProfileScreen({super.key});

  @override
  State<LearningProfileScreen> createState() => _LearningProfileScreenState();
}

class _LearningProfileScreenState extends State<LearningProfileScreen> {
  Map<String, dynamic>? _profile;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    setState(() => _loading = true);
    final profile = await ApiService.getDiagnosis();
    setState(() {
      _profile = profile;
      _loading = false;
    });
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

  @override
  Widget build(BuildContext context) {
    final style = _profile?['learning_style'] as String?;
    final persona = _profile?['persona'] as String? ?? 'Basic Learner';
    final confidence = _profile?['confidence'] != null
        ? (_profile!['confidence'] as num).toDouble()
        : 0.0;

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
    } else if (style == 'competitive') {
      accentColor = const Color(0xFFEF9086);
      heroGradient = const LinearGradient(
        colors: [Color(0xFFEF9086), Color(0xFFF7B2AA)],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      );
      styleIcon = Icons.emoji_events;
      styleDesc = 'You are driven by challenge and performance — pressure, scoring, and the drive to beat your own record are the most powerful motivators for your learning.';
    }

    return Scaffold(
      backgroundColor: Colors.transparent,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 0,
        title: const Text(
          'Your Learning Profile',
          style: TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 18),
        ),
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Color(0xFF64748B)),
          onPressed: () => context.go('/dashboard'),
        ),
        actions: style != null
            ? [
                IconButton(
                  icon: const Icon(Icons.delete_outline, color: Colors.red),
                  onPressed: _resetProfile,
                  tooltip: 'Reset to Basic UI',
                ),
              ]
            : null,
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
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _profile == null
                ? _buildEmptyState()
                : SingleChildScrollView(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // ── Hero Profile Card (grad-{style}) ──
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(24),
                          decoration: BoxDecoration(
                            gradient: heroGradient,
                            borderRadius: BorderRadius.circular(20),
                            boxShadow: [
                              BoxShadow(
                                color: accentColor.withOpacity(0.3),
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
                                  color: Colors.white.withOpacity(0.18),
                                  borderRadius: BorderRadius.circular(20),
                                  border: Border.all(color: Colors.white.withOpacity(0.25)),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(styleIcon, color: Colors.white, size: 14),
                                    const SizedBox(width: 6),
                                    Text(
                                      '${(style ?? 'basic').replaceAll('_', '/').toUpperCase()} LEARNER',
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
                                persona,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 26,
                                  fontWeight: FontWeight.w800,
                                  height: 1.2,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text(
                                styleDesc,
                                style: const TextStyle(
                                  color: Colors.white70,
                                  fontSize: 13.5,
                                  height: 1.45,
                                ),
                              ),
                              const SizedBox(height: 18),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                decoration: BoxDecoration(
                                  color: Colors.white.withOpacity(0.15),
                                  borderRadius: BorderRadius.circular(20),
                                  border: Border.all(color: Colors.white.withOpacity(0.2)),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Icon(Icons.trending_up, color: Colors.white, size: 14),
                                    const SizedBox(width: 6),
                                    Text(
                                      'Confidence Score: ${confidence.toStringAsFixed(1)}% — ${confidence >= 65 ? 'Strong Match' : confidence >= 45 ? 'Moderate Match' : 'Emerging Match'}',
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 24),

                        // ── Evidence Score Breakdown Card ──
                        _buildBreakdownCard(),
                        const SizedBox(height: 24),

                        // ── Radar Chart Card ──
                        _buildRadarChartCard(accentColor),
                        const SizedBox(height: 24),

                        // ── Recommendations Card ──
                        const Text(
                          'Personalized Recommendations',
                          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                        ),
                        const SizedBox(height: 12),
                        ...(_profile!['recommendations'] as List).map(
                          (rec) => _buildRecommendationItem(rec.toString(), accentColor),
                        ),
                        const SizedBox(height: 24),

                        // Retake button
                        SizedBox(
                          width: double.infinity,
                          child: OutlinedButton.icon(
                            onPressed: () => context.go('/learning-style'),
                            icon: const Icon(Icons.history_edu, color: Color(0xFF6B7280)),
                            label: const Text('Retake Assessment', style: TextStyle(color: Color(0xFF374151), fontWeight: FontWeight.bold)),
                            style: OutlinedButton.styleFrom(
                              padding: const EdgeInsets.symmetric(vertical: 14),
                              side: const BorderSide(color: Color(0xFFD1D5DB)),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                          ),
                        ),
                        const SizedBox(height: 20),
                      ],
                    ),
                  ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(40),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.assignment_ind_outlined, size: 80, color: Colors.grey.shade300),
            const SizedBox(height: 20),
            const Text(
              'No Profile Found',
              style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
            ),
            const SizedBox(height: 10),
            const Text(
              'Complete the learning style assessment to get your personalized recommendations.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Color(0xFF64748B)),
            ),
            const SizedBox(height: 30),
            ElevatedButton(
              onPressed: () => context.go('/learning-style'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF3B82F6),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(25)),
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
    final compScore = _profile?['score_competitive'] as int? ?? 0;
    final total = rwScore + audScore + compScore;
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
          const SizedBox(height: 20),

          // Read Write
          _buildProgressBar('Read/Write', rwScore, maxTotal, const Color(0xFF7D6867)),
          const SizedBox(height: 14),

          // Auditory
          _buildProgressBar('Auditory', audScore, maxTotal, const Color(0xFFE5B181)),
          const SizedBox(height: 14),

          // Competitive
          _buildProgressBar('Competitive', compScore, maxTotal, const Color(0xFFEF9086)),
        ],
      ),
    );
  }

  Widget _buildProgressBar(String label, int score, int total, Color color) {
    final pct = score / total;
    return Column(
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              label,
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF1E293B)),
            ),
            Text(
              '$score',
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF4B5563)),
            ),
          ],
        ),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(10),
          child: LinearProgressIndicator(
            value: pct,
            color: color,
            backgroundColor: const Color(0xFFF3F4F6),
            minHeight: 10,
          ),
        ),
      ],
    );
  }

  Widget _buildRadarChartCard(Color primaryColor) {
    final rwScore = (_profile?['score_read_write'] as num? ?? 0).toDouble();
    final audScore = (_profile?['score_auditory'] as num? ?? 0).toDouble();
    final compScore = (_profile?['score_competitive'] as num? ?? 0).toDouble();

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
            'Learning Style Radar',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
          ),
          const SizedBox(height: 20),
          SizedBox(
            height: 220,
            child: RadarChart(
              RadarChartData(
                dataSets: [
                  RadarDataSet(
                    fillColor: primaryColor.withOpacity(0.2),
                    borderColor: primaryColor,
                    entryRadius: 3,
                    dataEntries: [
                      RadarEntry(value: rwScore),
                      RadarEntry(value: audScore),
                      RadarEntry(value: compScore),
                    ],
                  ),
                ],
                radarBackgroundColor: Colors.transparent,
                borderData: FlBorderData(show: false),
                radarBorderData: const BorderSide(color: Colors.grey, width: 0.5),
                titlePositionPercentageOffset: 0.15,
                titleTextStyle: const TextStyle(color: Color(0xFF4B5563), fontSize: 11, fontWeight: FontWeight.bold),
                getTitle: (index, angle) {
                  switch (index) {
                    case 0:
                      return const RadarChartTitle(text: 'Read/Write');
                    case 1:
                      return const RadarChartTitle(text: 'Auditory');
                    case 2:
                      return const RadarChartTitle(text: 'Competitive');
                    default:
                      return const RadarChartTitle(text: '');
                  }
                },
                tickCount: 3,
                ticksTextStyle: const TextStyle(color: Colors.transparent),
                gridBorderData: const BorderSide(color: Color(0xFFD1D5DB), width: 0.5),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRecommendationItem(String text, Color accent) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE5E7EB)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.01),
            blurRadius: 5,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(2),
            decoration: BoxDecoration(
              color: const Color(0xFFD1FAE5),
              borderRadius: BorderRadius.circular(6),
            ),
            child: const Icon(Icons.check, color: Color(0xFF059669), size: 16),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(fontSize: 14, height: 1.45, color: Color(0xFF374151)),
            ),
          ),
        ],
      ),
    );
  }
}
