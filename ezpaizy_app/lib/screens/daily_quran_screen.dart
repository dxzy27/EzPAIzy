import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../app/theme.dart';

class DailyQuranScreen extends StatefulWidget {
  const DailyQuranScreen({super.key});

  @override
  State<DailyQuranScreen> createState() => _DailyQuranScreenState();
}

class _DailyQuranScreenState extends State<DailyQuranScreen> {
  Map<String, dynamic>? data;
  bool loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => loading = true);
    try {
      data = await ApiService.getDailyQuran();
    } catch (_) {}
    setState(() => loading = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Daily Quran'),
        backgroundColor: AppTheme.primary,
      ),
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFF1B5E20), Color(0xFF2E7D32), Color(0xFF388E3C)],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: loading
            ? const Center(
                child: CircularProgressIndicator(color: Colors.white))
            : data == null
                ? const Center(
                    child: Text('Failed to load verse',
                        style: TextStyle(color: Colors.white)))
                : RefreshIndicator(
                    onRefresh: _load,
                    child: SingleChildScrollView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        children: [
                          const SizedBox(height: 20),

                          // Arabic title
                          const Text(
                            'بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              color: AppTheme.gold,
                              fontSize: 20,
                              fontFamily: 'serif',
                            ),
                          ),
                          const SizedBox(height: 30),

                          // Verse card
                          Card(
                            color: Colors.white.withOpacity(0.1),
                            shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(20)),
                            child: Padding(
                              padding: const EdgeInsets.all(24),
                              child: Column(
                                children: [
                                  // Arabic text
                                  if (data!['arabic'] != null)
                                    Text(
                                      data!['arabic'],
                                      textAlign: TextAlign.right,
                                      textDirection: TextDirection.rtl,
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 26,
                                        height: 2.0,
                                        fontFamily: 'serif',
                                      ),
                                    ),

                                  const Divider(color: Colors.white24, height: 32),

                                  // Translation
                                  Text(
                                    data!['verse'] ??
                                        data!['translation'] ??
                                        '',
                                    textAlign: TextAlign.center,
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 16,
                                      height: 1.7,
                                      fontStyle: FontStyle.italic,
                                    ),
                                  ),

                                  const SizedBox(height: 16),

                                  // Surah info
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 16, vertical: 8),
                                    decoration: BoxDecoration(
                                      color: AppTheme.gold.withOpacity(0.2),
                                      borderRadius: BorderRadius.circular(20),
                                      border: Border.all(
                                          color: AppTheme.gold.withOpacity(0.5)),
                                    ),
                                    child: Text(
                                      data!['surah'] ?? '',
                                      style: const TextStyle(
                                          color: AppTheme.gold,
                                          fontWeight: FontWeight.bold),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),

                          const SizedBox(height: 30),

                          // Refresh button
                          OutlinedButton.icon(
                            onPressed: _load,
                            icon: const Icon(Icons.refresh, color: Colors.white),
                            label: const Text('New Verse',
                                style: TextStyle(color: Colors.white)),
                            style: OutlinedButton.styleFrom(
                                side: const BorderSide(color: Colors.white54)),
                          ),
                        ],
                      ),
                    ),
                  ),
      ),
    );
  }
}
