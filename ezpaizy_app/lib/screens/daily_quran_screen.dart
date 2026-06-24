import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import '../services/api_service.dart';

class DailyQuranScreen extends StatefulWidget {
  const DailyQuranScreen({super.key});

  @override
  State<DailyQuranScreen> createState() => _DailyQuranScreenState();
}

class _DailyQuranScreenState extends State<DailyQuranScreen> {
  Map<String, dynamic>? data;
  bool loading = true;
  String currentMood = '';
  String currentMode = 'normal'; // normal or memorize
  
  // Memorize mode state
  List<String> _words = [];
  int _currentChunkIndex = 0;
  final int _chunkSize = 3;
  bool _revealText = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      loading = true;
      currentMood = '';
    });
    try {
      final res = await ApiService.getDailyQuran();
      setState(() {
        data = res;
        _initMemorizeMode(res['arabic'] ?? '');
        loading = false;
      });
    } catch (_) {
      setState(() {
        loading = false;
      });
    }
  }

  Future<void> _selectMood(String mood) async {
    setState(() {
      loading = true;
      currentMood = mood;
    });
    try {
      final res = await ApiService.getQuranByMood(mood);
      setState(() {
        data = res;
        _initMemorizeMode(res['arabic'] ?? '');
        loading = false;
      });
    } catch (_) {
      setState(() {
        loading = false;
      });
    }
  }

  void _initMemorizeMode(String arabic) {
    if (arabic.isEmpty) {
      _words = [];
    } else {
      _words = arabic.trim().split(RegExp(r'\s+'));
    }
    _currentChunkIndex = 0;
    _revealText = false;
  }

  String get _currentArabicChunk {
    if (_words.isEmpty) return '';
    final start = _currentChunkIndex * _chunkSize;
    final end = (start + _chunkSize) > _words.length ? _words.length : (start + _chunkSize);
    return _words.sublist(start, end).join(' ');
  }

  int get _totalChunks {
    if (_words.isEmpty) return 0;
    return (_words.length / _chunkSize).ceil();
  }

  Future<void> _launchAudio(String? url) async {
    if (url == null || url.isEmpty) return;
    final uri = Uri.parse(url);
    bool launched = false;

    final modes = [
      LaunchMode.platformDefault,
      LaunchMode.inAppBrowserView,
      LaunchMode.externalApplication,
    ];

    for (final mode in modes) {
      try {
        if (await canLaunchUrl(uri)) {
          launched = await launchUrl(uri, mode: mode);
          if (launched) break;
        } else {
          launched = await launchUrl(uri, mode: mode);
          if (launched) break;
        }
      } catch (_) {
        // try next mode
      }
    }

    if (!launched && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Could not play recitation audio.')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.transparent,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 0,
        title: const Text(
          'Daily Quran',
          style: TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 18),
        ),
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Color(0xFF64748B)),
          onPressed: () => context.go('/dashboard'),
        ),
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
            : data == null
                ? const Center(child: Text('Failed to load verse', style: TextStyle(color: Color(0xFF64748B))))
                : RefreshIndicator(
                    onRefresh: _load,
                    child: SingleChildScrollView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        children: [
                          // ── Top Header (Matching Web Header Design) ──
                          Container(
                            width: double.infinity,
                            decoration: BoxDecoration(
                              gradient: const LinearGradient(
                                colors: [Color(0xFF0F0F2D), Color(0xFF1A1A3C)],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
                            child: Column(
                              children: [
                                Image.asset(
                                  'assets/images/logo.png',
                                  height: 48,
                                  color: Colors.white.withOpacity(0.8),
                                  errorBuilder: (_, __, ___) => const Icon(Icons.auto_stories, color: Colors.white70, size: 40),
                                ),
                                const SizedBox(height: 12),
                                Text(
                                  currentMood.isEmpty
                                      ? 'AYAH OF THE DAY'
                                      : 'VERSE FOR ${currentMood.toUpperCase()}',
                                  style: TextStyle(
                                    color: Colors.white.withOpacity(0.6),
                                    fontSize: 11,
                                    fontWeight: FontWeight.bold,
                                    letterSpacing: 1.5,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 16),

                          // ── Mood & Mode Selectors Row (Matching Web controls) ──
                          Container(
                            padding: const EdgeInsets.all(12),
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
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                // Mood row
                                const Text(
                                  'MOOD:',
                                  style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
                                ),
                                const SizedBox(height: 6),
                                SingleChildScrollView(
                                  scrollDirection: Axis.horizontal,
                                  child: Row(
                                    children: [
                                      _moodButton('happy', '😊 Happy'),
                                      _moodButton('sad', '😔 Sad'),
                                      _moodButton('anxious', '😰 Anxious'),
                                      _moodButton('unmotivated', '😐 Unmotivated'),
                                      _moodButton('lost', '🧭 Lost'),
                                    ],
                                  ),
                                ),
                                const Divider(height: 20),

                                // Mode row
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    const Text(
                                      'MODE:',
                                      style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
                                    ),
                                    Row(
                                      children: [
                                        _modeButton('normal', 'Normal'),
                                        const SizedBox(width: 8),
                                        _modeButton('memorize', 'Memorize'),
                                      ],
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 20),

                          // ── Card Content ──
                          Container(
                            width: double.infinity,
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(20),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withOpacity(0.02),
                                  blurRadius: 12,
                                  offset: const Offset(0, 4),
                                ),
                              ],
                            ),
                            padding: const EdgeInsets.all(24),
                            child: currentMode == 'normal' ? _buildNormalContent() : _buildMemorizeContent(),
                          ),
                          const SizedBox(height: 20),
                        ],
                      ),
                    ),
                  ),
      ),
    );
  }

  Widget _moodButton(String mood, String label) {
    final isSelected = currentMood == mood;
    return GestureDetector(
      onTap: () => _selectMood(mood),
      child: Container(
        margin: const EdgeInsets.only(right: 8),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF3B82F6) : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: isSelected ? const Color(0xFF3B82F6) : const Color(0xFFD1D5DB)),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isSelected ? Colors.white : const Color(0xFF4B5563),
            fontSize: 12,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    );
  }

  Widget _modeButton(String mode, String label) {
    final isSelected = currentMode == mode;
    return GestureDetector(
      onTap: () {
        setState(() {
          currentMode = mode;
          _revealText = false;
        });
      },
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF0F0F2D) : Colors.white,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: isSelected ? const Color(0xFF0F0F2D) : const Color(0xFFCBD5E1)),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isSelected ? Colors.white : const Color(0xFF4B5563),
            fontSize: 11,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    );
  }

  Widget _buildNormalContent() {
    return Column(
      children: [
        // Bismillah
        const Text(
          'بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ',
          textAlign: TextAlign.center,
          style: TextStyle(
            color: Color(0xFFFFC107),
            fontSize: 18,
            fontFamily: 'serif',
          ),
        ),
        const SizedBox(height: 20),

        // Arabic text
        Text(
          data!['arabic'] ?? '',
          textAlign: TextAlign.right,
          textDirection: TextDirection.rtl,
          style: const TextStyle(
            color: Color(0xFF0F0F2D),
            fontSize: 24,
            height: 2.2,
            fontFamily: 'serif',
          ),
        ),
        const SizedBox(height: 12),

        // Reference Badge
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
          decoration: BoxDecoration(
            color: const Color(0xFFF3F4F6),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: const Color(0xFFE5E7EB)),
          ),
          child: Text(
            data!['surah'] ?? '',
            style: const TextStyle(color: Color(0xFF6B7280), fontSize: 11, fontWeight: FontWeight.bold),
          ),
        ),
        const Divider(height: 40, color: Color(0xFFE5E7EB)),

        // English Translation
        const Text(
          'ENGLISH',
          style: TextStyle(color: Color(0xFF9CA3AF), fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 1.0),
        ),
        const SizedBox(height: 6),
        Text(
          '"${data!['verse'] ?? ''}"',
          textAlign: TextAlign.center,
          style: const TextStyle(
            color: Color(0xFF374151),
            fontSize: 14.5,
            height: 1.6,
            fontStyle: FontStyle.italic,
          ),
        ),
        const SizedBox(height: 24),

        // Malay Translation
        if (data!['translation'] != null) ...[
          const Text(
            'BAHASA MELAYU',
            style: TextStyle(color: Color(0xFF9CA3AF), fontSize: 10, fontWeight: FontWeight.bold, letterSpacing: 1.0),
          ),
          const SizedBox(height: 6),
          Text(
            '"${data!['translation']}"',
            textAlign: TextAlign.center,
            style: const TextStyle(
              color: Color(0xFF374151),
              fontSize: 14.5,
              height: 1.6,
              fontStyle: FontStyle.italic,
            ),
          ),
        ],

        // Recitation Player button
        if (data!['audio'] != null) ...[
          const Divider(height: 40, color: Color(0xFFE5E7EB)),
          GestureDetector(
            onTap: () => _launchAudio(data!['audio']),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              decoration: BoxDecoration(
                color: const Color(0xFFF9FAFB),
                borderRadius: BorderRadius.circular(30),
                border: Border.all(color: const Color(0xFFE5E7EB)),
              ),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircleAvatar(
                    radius: 18,
                    backgroundColor: Color(0xFF3B82F6),
                    child: Icon(Icons.play_arrow, color: Colors.white, size: 20),
                  ),
                  SizedBox(width: 12),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Listen to Recitation',
                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF1F2937)),
                      ),
                      Text(
                        'Mishary Rashid Alafasy',
                        style: TextStyle(fontSize: 10, color: Color(0xFF6B7280)),
                      ),
                    ],
                  ),
                  SizedBox(width: 12),
                ],
              ),
            ),
          ),
        ],
      ],
    );
  }

  Widget _buildMemorizeContent() {
    if (_words.isEmpty) {
      return const Center(child: Text('No text available for memorization.'));
    }

    return Column(
      children: [
        // Memorization Display card
        GestureDetector(
          onTap: () {
            setState(() {
              _revealText = !_revealText;
            });
          },
          child: Container(
            width: double.infinity,
            constraints: const BoxConstraints(minHeight: 160),
            decoration: BoxDecoration(
              color: const Color(0xFFF9FAFB),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFE5E7EB)),
            ),
            child: Center(
              child: _revealText
                  ? Padding(
                      padding: const EdgeInsets.all(20),
                      child: Text(
                        _currentArabicChunk,
                        textAlign: TextAlign.center,
                        textDirection: TextDirection.rtl,
                        style: const TextStyle(
                          color: Color(0xFF0F0F2D),
                          fontSize: 26,
                          height: 2.2,
                          fontFamily: 'serif',
                        ),
                      ),
                    )
                  : const Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.visibility_off_outlined, size: 36, color: Color(0xFF9CA3AF)),
                        SizedBox(height: 8),
                        Text(
                          'Tap to Reveal',
                          style: TextStyle(color: Color(0xFF9CA3AF), fontSize: 13, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
            ),
          ),
        ),
        const SizedBox(height: 20),

        // Show Malay translation below to aid memorization
        if (data!['translation'] != null) ...[
          Text(
            '"${data!['translation']}"',
            textAlign: TextAlign.center,
            style: const TextStyle(
              color: Color(0xFF6B7280),
              fontSize: 13.5,
              height: 1.55,
              fontStyle: FontStyle.italic,
            ),
          ),
        ],
        const Divider(height: 36, color: Color(0xFFE5E7EB)),

        // Memorize navigation controllers
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            OutlinedButton(
              onPressed: _currentChunkIndex > 0
                  ? () {
                      setState(() {
                        _currentChunkIndex--;
                        _revealText = false;
                      });
                    }
                  : null,
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: Color(0xFFE5E7EB)),
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
              child: const Row(
                children: [
                  Icon(Icons.arrow_back, size: 14),
                  SizedBox(width: 4),
                  Text('Previous'),
                ],
              ),
            ),
            Text(
              '${_currentChunkIndex + 1} / $_totalChunks',
              style: const TextStyle(color: Color(0xFF6B7280), fontWeight: FontWeight.bold, fontSize: 13),
            ),
            ElevatedButton(
              onPressed: () {
                if (_currentChunkIndex < _totalChunks - 1) {
                  setState(() {
                    _currentChunkIndex++;
                    _revealText = false;
                  });
                } else {
                  // Finish and reset
                  setState(() {
                    _currentChunkIndex = 0;
                    _revealText = false;
                  });
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Great job! Memorization session completed.'),
                      backgroundColor: Colors.green,
                    ),
                  );
                }
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: _currentChunkIndex == _totalChunks - 1 ? Colors.green : const Color(0xFF3B82F6),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
              child: Row(
                children: [
                  Text(_currentChunkIndex == _totalChunks - 1 ? 'Finish' : 'Next'),
                  const SizedBox(width: 4),
                  Icon(_currentChunkIndex == _totalChunks - 1 ? Icons.check : Icons.arrow_forward, size: 14),
                ],
              ),
            ),
          ],
        ),
      ],
    );
  }
}
