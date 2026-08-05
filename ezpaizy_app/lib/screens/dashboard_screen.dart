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
  List<dynamic> noteFolders = [];
  List<dynamic> revisionList = [];

  final PageController _carouselController = PageController();
  int _carouselIndex = 0;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _carouselController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      loading = true;
      error = null;
    });
    try {
      final d = await ApiService.getDashboard();
      List<dynamic> folders = [];
      List<dynamic> revisions = [];

      final style = (d['user']?['learning_style'] ?? d['profile']?['learning_style']) as String?;
      if (style == 'read_write') {
        folders = await ApiService.getNoteFolders();
      }
      revisions = await ApiService.getRevision();

      setState(() {
        data = d;
        noteFolders = folders;
        revisionList = revisions;
        loading = false;
      });

      if (d['user'] != null && mounted) {
        Provider.of<AuthProvider>(context, listen: false).setUser(d['user']);
      }

      final auth = Provider.of<AuthProvider>(context, listen: false);
      if (style == null && !auth.hasDismissedDiagnosis && mounted) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          _showDiagnosisDialog();
        });
      }
    } catch (e) {
      setState(() {
        error = 'Failed to load dashboard';
        loading = false;
      });
    }
  }

  void _showDiagnosisDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (BuildContext context) {
        return Dialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(20),
          ),
          elevation: 16,
          backgroundColor: Colors.transparent,
          child: Container(
            constraints: const BoxConstraints(maxWidth: 400),
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF7C3AED), Color(0xFF4F46E5)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const SizedBox(height: 8),
                const Text(
                  '🧠',
                  style: TextStyle(fontSize: 56),
                ),
                const SizedBox(height: 16),
                const Text(
                  'Discover Your Learning Style',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                    fontFamily: 'Outfit',
                  ),
                ),
                const SizedBox(height: 12),
                const Text(
                  'Take a 16-question diagnosis to determine your learning styles to study how you learn best.',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: Colors.white70,
                    fontSize: 14,
                    height: 1.5,
                    fontFamily: 'Outfit',
                  ),
                ),
                const SizedBox(height: 24),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () {
                      Navigator.of(context).pop();
                      context.go('/learning-style');
                    },
                    icon: const Icon(Icons.assignment, color: Color(0xFF5B21B6), size: 18),
                    label: const Text(
                      'Start Diagnosis',
                      style: TextStyle(
                        color: Color(0xFF5B21B6),
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                        fontFamily: 'Outfit',
                      ),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.white,
                      foregroundColor: const Color(0xFF5B21B6),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                      elevation: 0,
                    ),
                  ),
                ),
                const SizedBox(height: 8),
                TextButton(
                  onPressed: () {
                    Navigator.of(context).pop();
                    Provider.of<AuthProvider>(context, listen: false).dismissDiagnosis();
                  },
                  style: TextButton.styleFrom(
                    foregroundColor: Colors.white70,
                    padding: const EdgeInsets.symmetric(vertical: 8),
                  ),
                  child: const Text(
                    'Maybe later',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      fontFamily: 'Outfit',
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  void _showProfileDropdown(BuildContext context, Offset position, String name, Color accentColor, AuthProvider auth) {
    final RenderBox overlay = Overlay.of(context).context.findRenderObject() as RenderBox;
    showMenu(
      context: context,
      position: RelativeRect.fromRect(
        Rect.fromLTWH(position.dx - 180, position.dy + 12, 180, 200),
        Offset.zero & overlay.size,
      ),
      elevation: 8,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: const BorderSide(color: Color(0xFFE2E8F0), width: 1),
      ),
      items: <PopupMenuEntry<String>>[
        PopupMenuItem(
          enabled: false,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                name,
                style: const TextStyle(
                  fontWeight: FontWeight.w800,
                  fontSize: 15,
                  color: Color(0xFF1E293B),
                  fontFamily: 'Outfit',
                ),
              ),
              const SizedBox(height: 2),
              const Text(
                'Student',
                style: TextStyle(
                  fontSize: 11,
                  color: Color(0xFF94A3B8),
                  fontFamily: 'Outfit',
                ),
              ),
            ],
          ),
        ),
        const PopupMenuDivider(),
        PopupMenuItem(
          value: 'profile',
          child: Row(
            children: [
              Icon(Icons.person_outline, size: 18, color: Colors.grey[700]),
              const SizedBox(width: 8),
              const Text('Profile', style: TextStyle(fontSize: 13, fontFamily: 'Outfit', fontWeight: FontWeight.w500)),
            ],
          ),
        ),
        PopupMenuItem(
          value: 'revision',
          child: Row(
            children: [
              Icon(Icons.star_outline_rounded, size: 18, color: Colors.amber[800]),
              const SizedBox(width: 8),
              const Text('My Revision List', style: TextStyle(fontSize: 13, fontFamily: 'Outfit', fontWeight: FontWeight.w600)),
            ],
          ),
        ),
        PopupMenuItem(
          value: 'progress',
          child: Row(
            children: [
              Icon(Icons.bar_chart_outlined, size: 18, color: Colors.grey[700]),
              const SizedBox(width: 8),
              const Text('My Progress', style: TextStyle(fontSize: 13, fontFamily: 'Outfit', fontWeight: FontWeight.w500)),
            ],
          ),
        ),
        const PopupMenuDivider(),
        PopupMenuItem(
          value: 'signout',
          child: Row(
            children: const [
              Icon(Icons.logout, size: 18, color: Colors.redAccent),
              SizedBox(width: 8),
              Text(
                'Sign out',
                style: TextStyle(
                  fontSize: 13,
                  fontFamily: 'Outfit',
                  color: Colors.redAccent,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
        ),
      ],
    ).then((value) async {
      if (value == 'profile') {
        context.go('/profile');
      } else if (value == 'revision') {
        context.go('/revision');
      } else if (value == 'progress') {
        context.go('/progress');
      } else if (value == 'signout') {
        await ApiService.logout();
        auth.logout();
        if (context.mounted) {
          context.go('/login');
        }
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.read<AuthProvider>();
    final style = (data?['user']?['learning_style'] ?? data?['profile']?['learning_style']) as String?;
    final name = data?['user']?['name'] as String? ?? 'Student';
    final firstName = name.split(' ')[0];

    // Per-style config
    Color accentColor = const Color(0xFF14B8A6);
    Color accentLightColor = const Color(0xFFF0FDFA);
    Color accentTextColor = const Color(0xFF134E4A);
    String tipIcon = '💡';
    String tipTitle = 'Study Tip';
    String tipText = 'Complete the learning style diagnosis to get personalized recommendations.';

    if (style == 'read_write') {
      accentColor = const Color(0xFF7D6867);
      accentLightColor = const Color(0xFFFAF6F6);
      accentTextColor = const Color(0xFF453938);
      tipIcon = '✍️';
      tipTitle = 'Read/Write Study Tip';
      tipText = 'Use the Notepad next to your materials and quizzes to jot down summaries and acronyms.';
    } else if (style == 'auditory') {
      accentColor = const Color(0xFFE5B181);
      accentLightColor = const Color(0xFFFFF7ED);
      accentTextColor = const Color(0xFF7C2D12);
      tipIcon = '🎵';
      tipTitle = 'Auditory Study Tip';
      tipText = 'After reading any material today, close it and say aloud — in your own words — what you just learned.';
    } else if (style == 'visual') {
      accentColor = const Color(0xFF06B6D4);
      accentLightColor = const Color(0xFFECFEFF);
      accentTextColor = const Color(0xFF083344);
      tipIcon = '👁️';
      tipTitle = 'Visual Study Tip';
      tipText = 'You can highlight or underline the text that you read in flashcards, quizzes and other materials.';
    } else if (style == 'kinesthetic') {
      accentColor = const Color(0xFFD946EF);
      accentLightColor = const Color(0xFFFDF4FF);
      accentTextColor = const Color(0xFF701A75);
      tipIcon = '🤸';
      tipTitle = 'Kinaesthetic Study Tip';
      tipText = 'Interact directly with your study tools! Use swipe flashcards and timed challenges.';
    }

    final isWide = MediaQuery.of(context).size.width > 900;

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
          color: const Color(0xFFF1F5F9).withOpacity(0.15), // Very light overlay for readability
          child: SafeArea(
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
                          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
                          child: Center(
                            child: ConstrainedBox(
                              constraints: const BoxConstraints(maxWidth: 800),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  // Top Nav Bar
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Image.asset(
                                        'assets/images/newlogo.png',
                                        height: 40,
                                        errorBuilder: (_, __, ___) => const Icon(Icons.school, color: Color(0xFF3B82F6)),
                                      ),
                                      Row(
                                        children: [
                                          // Revision List Button (Star Icon)
                                          IconButton(
                                            onPressed: () => context.go('/revision'),
                                            tooltip: 'My Revision List',
                                            icon: Container(
                                              width: 36,
                                              height: 36,
                                              decoration: BoxDecoration(
                                                color: const Color(0xFFFFF8E1),
                                                borderRadius: BorderRadius.circular(10),
                                                border: Border.all(color: const Color(0xFFFFE082)),
                                              ),
                                              child: const Icon(Icons.star_rounded, color: Color(0xFFFFA000), size: 20),
                                            ),
                                          ),
                                          const SizedBox(width: 6),
                                          GestureDetector(
                                            onTapDown: (details) {
                                              _showProfileDropdown(context, details.globalPosition, name, accentColor, auth);
                                            },
                                            child: Container(
                                              width: 36,
                                              height: 36,
                                              decoration: BoxDecoration(
                                                color: accentColor,
                                                borderRadius: BorderRadius.circular(10),
                                                boxShadow: [
                                                  BoxShadow(
                                                    color: Colors.black.withOpacity(0.1),
                                                    blurRadius: 4,
                                                    offset: const Offset(0, 2),
                                                  ),
                                                ],
                                              ),
                                              alignment: Alignment.center,
                                              child: Text(
                                                firstName.isNotEmpty ? firstName[0].toUpperCase() : 'S',
                                                style: const TextStyle(
                                                  color: Colors.white,
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 14,
                                                  fontFamily: 'Outfit',
                                                ),
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 24),

                                  // Greeting Header
                                  Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Text(
                                        '👋',
                                        style: TextStyle(fontSize: 32),
                                      ),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              'Assalamualaikum, $firstName',
                                              style: const TextStyle(
                                                fontSize: 22,
                                                fontWeight: FontWeight.w800,
                                                color: Color(0xFF1E293B),
                                                fontFamily: 'Outfit',
                                              ),
                                            ),
                                            const SizedBox(height: 4),
                                            const Text(
                                              'Ready to continue your learning today?',
                                              style: TextStyle(
                                                fontSize: 14,
                                                color: Color(0xFF64748B),
                                                fontFamily: 'Outfit',
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 24),

                                  // Study Tip Card
                                  if (style != null) ...[
                                    Container(
                                      padding: const EdgeInsets.all(20),
                                      decoration: BoxDecoration(
                                        color: accentLightColor,
                                        borderRadius: BorderRadius.circular(14),
                                        border: Border.all(color: accentColor),
                                      ),
                                      child: Row(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(tipIcon, style: const TextStyle(fontSize: 28)),
                                          const SizedBox(width: 16),
                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Text(
                                                  tipTitle.toUpperCase(),
                                                  style: TextStyle(
                                                    fontWeight: FontWeight.bold,
                                                    fontSize: 12,
                                                    color: accentTextColor,
                                                    letterSpacing: 0.5,
                                                    fontFamily: 'Outfit',
                                                  ),
                                                ),
                                                const SizedBox(height: 6),
                                                Text(
                                                  tipText,
                                                  style: TextStyle(
                                                    color: accentTextColor,
                                                    fontSize: 13,
                                                    height: 1.5,
                                                    fontFamily: 'Outfit',
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    const SizedBox(height: 24),
                                  ],

                                  // Stats Carousel
                                  _buildStatsCarousel(style, accentColor, accentLightColor, accentTextColor),
                                  const SizedBox(height: 32),

                                  // Recents Section
                                  _buildRecentsSection(),
                                  const SizedBox(height: 32),

                                  // Personalize Section
                                  _buildPersonalizeSection(),
                                  const SizedBox(height: 32),

                                  // Revision Section
                                  _buildRevisionSection(),
                                  const SizedBox(height: 24),
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

  Widget _buildStatsCarousel(String? style, Color accentColor, Color accentLightColor, Color accentTextColor) {
    final materialsCount = data?['materials_count'] ?? 0;
    final quizCount = data?['quiz_count'] ?? 0;
    final completedCount = data?['completed_count'] ?? 0;

    final List<Widget> slides = [
      _buildCarouselSlide(
        title: 'Available Content',
        count: '$materialsCount',
        color: const Color(0xFF14B8A6),
        imageAsset: 'assets/images/slideshow 1.png',
        actionArea: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            ElevatedButton(
              onPressed: () => context.go('/flashcards'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF4255FF),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              ),
              child: const Text('Flashcards', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, fontFamily: 'Outfit')),
            ),
            const SizedBox(width: 8),
            ElevatedButton(
              onPressed: () => context.go('/contents'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF10B981),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              ),
              child: const Text('Materials', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, fontFamily: 'Outfit')),
            ),
          ],
        ),
      ),
      _buildCarouselSlide(
        title: 'Available Quizzes',
        count: '$quizCount',
        color: const Color(0xFFF59E0B),
        imageAsset: 'assets/images/slideshow 2.png',
        actionArea: ElevatedButton(
          onPressed: () => context.go('/quizzes'),
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF4255FF),
            foregroundColor: Colors.white,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
          ),
          child: const Text('Browse Quizzes', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, fontFamily: 'Outfit')),
        ),
      ),
      _buildCarouselSlide(
        title: 'Quizzes Completed',
        count: '$completedCount',
        color: const Color(0xFF10B981),
        imageAsset: 'assets/images/slideshow 3.png',
        actionArea: style == null
            ? ElevatedButton(
                onPressed: () => context.go('/learning-style'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4255FF),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                ),
                child: const Text('Start Diagnosis', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, fontFamily: 'Outfit')),
              )
            : ElevatedButton(
                onPressed: () => context.go('/progress'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4255FF),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                ),
                child: const Text('View Progress', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, fontFamily: 'Outfit')),
              ),
      ),
    ];

    return Stack(
      alignment: Alignment.center,
      children: [
        Column(
          children: [
            Container(
              height: 180,
              child: PageView.builder(
                controller: _carouselController,
                itemCount: slides.length,
                onPageChanged: (idx) {
                  setState(() {
                    _carouselIndex = idx;
                  });
                },
                itemBuilder: (context, idx) {
                  return Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 24),
                    child: slides[idx],
                  );
                },
              ),
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(slides.length, (idx) {
                return Container(
                  margin: const EdgeInsets.symmetric(horizontal: 4),
                  width: 8,
                  height: 8,
                  decoration: BoxDecoration(
                    color: _carouselIndex == idx ? const Color(0xFF1F6E68) : Colors.grey.withOpacity(0.4),
                    shape: BoxShape.circle,
                  ),
                );
              }),
            ),
          ],
        ),
        Positioned(
          left: -4,
          child: IconButton(
            icon: const Icon(Icons.chevron_left, size: 28, color: Colors.black54),
            onPressed: () {
              if (_carouselIndex == 0) {
                _carouselController.animateToPage(
                  2,
                  duration: const Duration(milliseconds: 300),
                  curve: Curves.easeInOut,
                );
              } else {
                _carouselController.previousPage(
                  duration: const Duration(milliseconds: 300),
                  curve: Curves.easeInOut,
                );
              }
            },
          ),
        ),
        Positioned(
          right: -4,
          child: IconButton(
            icon: const Icon(Icons.chevron_right, size: 28, color: Colors.black54),
            onPressed: () {
              if (_carouselIndex == 2) {
                _carouselController.animateToPage(
                  0,
                  duration: const Duration(milliseconds: 300),
                  curve: Curves.easeInOut,
                );
              } else {
                _carouselController.nextPage(
                  duration: const Duration(milliseconds: 300),
                  curve: Curves.easeInOut,
                );
              }
            },
          ),
        ),
      ],
    );
  }

  Widget _buildCarouselSlide({
    required String title,
    required String count,
    required Color color,
    required String imageAsset,
    required Widget actionArea,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white.withOpacity(0.5)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1F6E68).withOpacity(0.05),
            blurRadius: 32,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: Row(
          children: [
            Expanded(
              flex: 6,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      count,
                      style: TextStyle(
                        fontSize: 48,
                        fontWeight: FontWeight.w800,
                        color: color,
                        height: 1.0,
                        fontFamily: 'Outfit',
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      title,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF1E293B),
                        fontFamily: 'Outfit',
                      ),
                    ),
                    const SizedBox(height: 12),
                    actionArea,
                  ],
                ),
              ),
            ),
            Container(
              width: 1.5,
              height: 90,
              color: Colors.black.withOpacity(0.1),
            ),
            Expanded(
              flex: 5,
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Image.asset(
                  imageAsset,
                  fit: BoxFit.contain,
                  errorBuilder: (_, __, ___) => const Icon(Icons.book, size: 64, color: Colors.grey),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRecentsSection() {
    final list = data?['new_materials'] as List? ?? [];
    if (list.isEmpty) {
      return const SizedBox.shrink();
    }

    // Combine quizzes and flashcards
    final items = list.take(4).toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Recents',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w800,
            color: Color(0xFF1E293B),
            fontFamily: 'Outfit',
          ),
        ),
        const SizedBox(height: 12),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: MediaQuery.of(context).size.width > 600 ? 2 : 1,
            crossAxisSpacing: 16,
            mainAxisSpacing: 12,
            childAspectRatio: 4.5,
          ),
          itemCount: items.length,
          itemBuilder: (context, idx) {
            final item = items[idx];
            final type = item['type'] as String? ?? 'Content';
            final title = item['title'] as String? ?? '';
            final isFlash = type == 'Flashcard';
            final topic = item['topic'] as String? ?? 'General';
            final itemId = item['id'];

            return InkWell(
              onTap: () {
                if (isFlash) {
                  context.go('/flashcards/$itemId');
                } else {
                  context.go('/contents/$itemId');
                }
              },
              child: Row(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: isFlash ? const Color(0xFF4255FF).withOpacity(0.12) : const Color(0xFF10B981).withOpacity(0.12),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(
                      isFlash ? Icons.style : Icons.description,
                      color: isFlash ? const Color(0xFF4255FF) : const Color(0xFF10B981),
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          title,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF1E293B),
                            fontFamily: 'Outfit',
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          isFlash ? '$topic • Flashcards • by Hamzah' : '$topic • Material • by Hamzah',
                          style: const TextStyle(
                            fontSize: 11,
                            color: Color(0xFF64748B),
                            fontFamily: 'Outfit',
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            );
          },
        ),
      ],
    );
  }

  Widget _buildPersonalizeSection() {
    final style = data?['diagnosis']?['primary_style'] as String?;
    final hasCompletedDiagnosis = style != null && style.isNotEmpty;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          Column(
            children: [
              Container(
                width: 64,
                height: 64,
                decoration: BoxDecoration(
                  color: const Color(0xFF4255FF).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(10),
                  child: Image.asset(
                    'assets/images/vark.png',
                    fit: BoxFit.contain,
                    errorBuilder: (_, __, ___) => const Icon(Icons.psychology, color: Color(0xFF4255FF), size: 36),
                  ),
                ),
              ),
              const SizedBox(height: 10),
              ElevatedButton(
                onPressed: () {
                  if (hasCompletedDiagnosis) {
                    context.go('/learning-profile');
                  } else {
                    context.go('/learning-style');
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: hasCompletedDiagnosis ? const Color(0xFF10B981) : const Color(0xFF4255FF),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                ),
                child: Text(
                  hasCompletedDiagnosis ? 'View Profile' : 'Start Diagnosis',
                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, fontFamily: 'Outfit'),
                ),
              ),
            ],
          ),
          const SizedBox(width: 20),
          Expanded(
            child: InkWell(
              onTap: () {
                if (hasCompletedDiagnosis) {
                  context.go('/learning-profile');
                } else {
                  context.go('/learning-style');
                }
              },
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    hasCompletedDiagnosis ? '🎯 Your VARK Learning Profile' : '🎯 Discover Your Learning Style',
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF1E293B),
                      fontFamily: 'Outfit',
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    hasCompletedDiagnosis
                        ? 'Tap here to view your complete VARK diagnosis breakdown, learning preferences, and recommended strategies.'
                        : 'Complete the VARK Questionnaire to customize materials to your personal study method.',
                    style: const TextStyle(
                      fontSize: 13,
                      color: Color(0xFF64748B),
                      height: 1.4,
                      fontFamily: 'Outfit',
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRevisionSection() {
    final hasRevision = revisionList.isNotEmpty;
    String revTitle = 'No saved materials';
    String revSubtitle = 'Mark materials with a star to revise them here.';

    if (hasRevision) {
      final first = revisionList.first;
      revTitle = first['title'] ?? 'Revision Item';
      revSubtitle = first['topic'] ?? 'Other Material';
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'REVISION LIST',
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w800,
            color: Color(0xFF64748B),
            letterSpacing: 0.5,
            fontFamily: 'Outfit',
          ),
        ),
        const SizedBox(height: 12),
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Colors.white.withOpacity(0.5)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.04),
                blurRadius: 16,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 64,
                    height: 64,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF59E0B).withOpacity(0.12),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: const Icon(Icons.star, color: Color(0xFFF59E0B), size: 28),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'To-Revise',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF1E293B),
                            fontFamily: 'Outfit',
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          hasRevision ? 'Recently saved:' : revTitle,
                          style: TextStyle(
                            fontSize: 13,
                            color: hasRevision ? const Color(0xFF64748B) : const Color(0xFF64748B),
                            fontFamily: 'Outfit',
                          ),
                        ),
                        if (hasRevision) ...[
                          const SizedBox(height: 4),
                          Text(
                            revTitle,
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF1E293B),
                              fontFamily: 'Outfit',
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            revSubtitle,
                            style: const TextStyle(
                              fontSize: 11,
                              color: Color(0xFF64748B),
                              fontFamily: 'Outfit',
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () => context.go('/revision'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFFF9E0B),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(99)),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                  child: const Text(
                    'Go to Revision List',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, fontFamily: 'Outfit'),
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
