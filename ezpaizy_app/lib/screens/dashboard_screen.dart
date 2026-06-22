import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import '../app/theme.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  Map<String, dynamic>? data;
  bool loading = true;
  String? error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() { loading = true; error = null; });
    try {
      final d = await ApiService.getDashboard();
      setState(() { data = d; loading = false; });
    } catch (e) {
      setState(() { error = 'Failed to load dashboard'; loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.read<AuthProvider>();
    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await auth.logout();
              if (context.mounted) context.go('/login');
            },
          ),
        ],
      ),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : error != null
              ? Center(child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.wifi_off, size: 48, color: Colors.grey),
                    const SizedBox(height: 12),
                    Text(error!, style: const TextStyle(color: Colors.grey)),
                    const SizedBox(height: 16),
                    ElevatedButton(onPressed: _load, child: const Text('Retry')),
                  ],
                ))
              : RefreshIndicator(
                  onRefresh: _load,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Welcome
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [AppTheme.primary, AppTheme.primaryLight],
                            ),
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Row(
                            children: [
                              const CircleAvatar(
                                backgroundColor: Colors.white24,
                                child: Icon(Icons.person, color: Colors.white),
                              ),
                              const SizedBox(width: 12),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text('Welcome back!',
                                      style: TextStyle(color: Colors.white70, fontSize: 12)),
                                  Text(
                                    data?['user']?['name'] ?? 'Student',
                                    style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 18,
                                        fontWeight: FontWeight.bold),
                                  ),
                                  if (data?['persona'] != null)
                                    Container(
                                      margin: const EdgeInsets.only(top: 4),
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: Colors.white24,
                                        borderRadius: BorderRadius.circular(4),
                                      ),
                                      child: Text(
                                        data!['persona'],
                                        style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                                      ),
                                    ),
                                ],
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 20),

                        // Stats Row
                        Row(
                          children: [
                            _statCard('📝', 'Quizzes',
                                '${data?['quiz_count'] ?? 0}', Colors.blue),
                            const SizedBox(width: 10),
                            _statCard('📚', 'Materials',
                                '${data?['materials_count'] ?? 0}', Colors.green),
                            const SizedBox(width: 10),
                            _statCard('🏆', 'Done',
                                '${data?['completed_count'] ?? 0}', Colors.orange),
                          ],
                        ),
                        const SizedBox(height: 24),

                        const Text('Quick Access',
                            style: TextStyle(
                                fontSize: 16, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 12),

                        // Nav Grid
                        GridView.count(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          crossAxisCount: 3,
                          crossAxisSpacing: 10,
                          mainAxisSpacing: 10,
                          children: [
                            _navCard('Quizzes', Icons.quiz, Colors.blue,
                                () => context.go('/quizzes')),
                            _navCard('Materials', Icons.menu_book, Colors.teal,
                                () => context.go('/contents')),
                            _navCard('Flashcards', Icons.style, Colors.purple,
                                () => context.go('/flashcards')),
                            _navCard('Progress', Icons.bar_chart, Colors.orange,
                                () => context.go('/progress')),
                            _navCard('Revision', Icons.star, Colors.amber,
                                () => context.go('/revision')),
                            _navCard('Style', Icons.assignment_ind, Colors.indigo,
                                () => context.go('/learning-profile')),
                          ],
                        ),

                        // Recent Results
                        if ((data?['recent_results'] as List?)?.isNotEmpty == true) ...[
                          const SizedBox(height: 24),
                          const Text('Recent Results',
                              style: TextStyle(
                                  fontSize: 16, fontWeight: FontWeight.bold)),
                          const SizedBox(height: 10),
                          ...((data!['recent_results'] as List).take(3).map((p) {
                            final score = p['score'] ?? 0;
                            final color = score >= 70
                                ? Colors.green
                                : score >= 50
                                    ? Colors.orange
                                    : Colors.red;
                            return Card(
                              margin: const EdgeInsets.only(bottom: 8),
                              child: ListTile(
                                title: Text(p['quiz']?['title'] ?? 'Quiz',
                                    style: const TextStyle(fontWeight: FontWeight.w600)),
                                subtitle: Text(
                                    p['quiz']?['teacher']?['name'] ?? 'Teacher'),
                                trailing: Chip(
                                  label: Text('$score%',
                                      style: const TextStyle(
                                          color: Colors.white,
                                          fontWeight: FontWeight.bold)),
                                  backgroundColor: color,
                                ),
                              ),
                            );
                          })),
                        ],
                      ],
                    ),
                  ),
                ),
    );
  }

  Widget _statCard(String emoji, String label, String value, Color color) {
    return Expanded(
      child: Card(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
          child: Column(
            children: [
              Text(emoji, style: const TextStyle(fontSize: 22)),
              const SizedBox(height: 4),
              Text(value,
                  style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: color)),
              Text(label,
                  style: const TextStyle(fontSize: 11, color: Colors.grey),
                  textAlign: TextAlign.center),
            ],
          ),
        ),
      ),
    );
  }

  Widget _navCard(
      String label, IconData icon, Color color, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Card(
        color: color.withOpacity(0.08),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: color, size: 30),
            const SizedBox(height: 6),
            Text(label,
                style: TextStyle(
                    fontWeight: FontWeight.w600, color: color, fontSize: 12)),
          ],
        ),
      ),
    );
  }
}
