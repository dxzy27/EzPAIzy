import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:fl_chart/fl_chart.dart';
import '../services/api_service.dart';
import '../app/theme.dart';

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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Your Learning Profile'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => context.go('/learning-style'),
            tooltip: 'Retake Assessment',
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _profile == null
              ? _buildEmptyState()
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Persona Card
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(24),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [AppTheme.primary, AppTheme.primaryLight],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(20),
                          boxShadow: [
                            BoxShadow(
                              color: AppTheme.primary.withOpacity(0.3),
                              blurRadius: 10,
                              offset: const Offset(0, 5),
                            ),
                          ],
                        ),
                        child: Column(
                          children: [
                            const Text(
                              'IDENTIFIED PERSONA',
                              style: TextStyle(
                                color: Colors.white70,
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                letterSpacing: 1.2,
                              ),
                            ),
                            const SizedBox(height: 12),
                            Text(
                              _profile!['persona'] ?? 'Unknown Learner',
                              textAlign: TextAlign.center,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 28,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 12),
                            const Text(
                              'Based on your assessment results, we have tailored your study plan.',
                              textAlign: TextAlign.center,
                              style: TextStyle(color: Colors.white70, fontSize: 14),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 30),

                      // Visualization Section (Radar Chart)
                      const Text(
                        'Learning Traits Visualization',
                        style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 16),
                      Container(
                        height: 300,
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.05),
                              blurRadius: 10,
                              offset: const Offset(0, 5),
                            ),
                          ],
                        ),
                        child: RadarChart(
                          RadarChartData(
                            dataSets: [
                              RadarDataSet(
                                fillColor: AppTheme.primary.withOpacity(0.2),
                                borderColor: AppTheme.primary,
                                entryRadius: 3,
                                dataEntries: [
                                  RadarEntry(value: (_profile!['q1'] == 'auditory' || _profile!['q2'] == 'auditory' ? 90.0 : 30.0)),
                                  RadarEntry(value: (_profile!['q1'] == 'visual' || _profile!['q3'] == 'visual' ? 90.0 : 40.0)),
                                  RadarEntry(value: (_profile!['q2'] == 'competitive' || _profile!['q3'] == 'competitive' ? 90.0 : 20.0)),
                                  RadarEntry(value: (_profile!['q4'] == 'slow' || _profile!['q5'] == 'slow' ? 90.0 : 30.0)),
                                  RadarEntry(value: (_profile!['q4'] == 'fast' || _profile!['q5'] == 'fast' ? 90.0 : 30.0)),
                                ],
                              ),
                            ],
                            radarBackgroundColor: Colors.transparent,
                            borderData: FlBorderData(show: false),
                            radarBorderData: const BorderSide(color: Colors.grey, width: 0.5),
                            titlePositionPercentageOffset: 0.15,
                            titleTextStyle: const TextStyle(color: Colors.black54, fontSize: 10, fontWeight: FontWeight.bold),
                            getTitle: (index, angle) {
                              switch (index) {
                                case 0: return const RadarChartTitle(text: 'Auditory');
                                case 1: return const RadarChartTitle(text: 'Visual');
                                case 2: return const RadarChartTitle(text: 'Competitive');
                                case 3: return const RadarChartTitle(text: 'Detailed');
                                case 4: return const RadarChartTitle(text: 'Quick');
                                default: return const RadarChartTitle(text: '');
                              }
                            },
                            tickCount: 5,
                            ticksTextStyle: const TextStyle(color: Colors.transparent, fontSize: 10),
                            gridBorderData: const BorderSide(color: Colors.grey, width: 0.5),
                          ),
                        ),
                      ),
                      const SizedBox(height: 30),

                      const Text(
                        'Personalized Recommendations',
                        style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 16),

                      // Recommendations List
                      ...(_profile!['recommendations'] as List).map((rec) => _buildRecommendationItem(rec)),

                      const SizedBox(height: 30),
                      SizedBox(
                        width: double.infinity,
                        child: OutlinedButton.icon(
                          onPressed: () => context.go('/learning-style'),
                          icon: const Icon(Icons.history_edu),
                          label: const Text('Retake Assessment'),
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 15),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                        ),
                      ),
                    ],
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
              style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 10),
            const Text(
              'Complete the learning style assessment to get your personalized recommendations.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey),
            ),
            const SizedBox(height: 30),
            ElevatedButton(
              onPressed: () => context.go('/learning-style'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primary,
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

  Widget _buildRecommendationItem(String text) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.check_circle, color: Colors.green, size: 22),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(fontSize: 15, height: 1.4),
            ),
          ),
        ],
      ),
    );
  }
}
