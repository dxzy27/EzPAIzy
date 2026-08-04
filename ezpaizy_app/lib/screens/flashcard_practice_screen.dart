import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import '../services/api_service.dart';

class FlashcardPracticeScreen extends StatefulWidget {
  final int setId;
  const FlashcardPracticeScreen({super.key, required this.setId});

  @override
  State<FlashcardPracticeScreen> createState() => _FlashcardPracticeScreenState();
}

class _FlashcardPracticeScreenState extends State<FlashcardPracticeScreen>
    with SingleTickerProviderStateMixin {
  Map<String, dynamic>? set;
  List<dynamic> allCards = [];
  bool loading = true;

  int currentIndex = 0;
  bool isFlipped = false;
  bool isSubmitting = false;

  late AnimationController _flipCtrl;
  late Animation<double> _flipAnim;

  @override
  void initState() {
    super.initState();
    _flipCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 400),
    );
    _flipAnim = Tween(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _flipCtrl, curve: Curves.easeInOut),
    );
    _load();
  }

  @override
  void dispose() {
    _flipCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    try {
      final d = await ApiService.getFlashcardDetail(widget.setId);
      setState(() {
        set = d;
        allCards = List<dynamic>.from(d['flashcards'] ?? []);
        loading = false;
      });
    } catch (_) {
      setState(() => loading = false);
    }
  }

  void _flip() {
    if (isFlipped) {
      _flipCtrl.reverse();
    } else {
      _flipCtrl.forward();
    }
    setState(() => isFlipped = !isFlipped);
  }

  Future<void> _submitReview(int quality) async {
    if (isSubmitting) return;
    setState(() => isSubmitting = true);

    final card = allCards[currentIndex];
    try {
      await ApiService.submitFlashcardReview(card['id'], quality);

      if (currentIndex < allCards.length - 1) {
        setState(() {
          currentIndex++;
          isFlipped = false;
        });
        _flipCtrl.reset();
      } else {
        if (mounted) {
          showDialog(
            context: context,
            barrierDismissible: false,
            builder: (ctx) => AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: Text('All Done! 🎉', style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
              content: Text(
                "You've reviewed all flashcards in this set. Great job!",
                style: GoogleFonts.outfit(),
              ),
              actions: [
                TextButton(
                  onPressed: () {
                    Navigator.of(ctx).pop();
                    context.pop();
                  },
                  child: Text('Back to Sets', style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          );
        }
      }
    } catch (_) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Failed to submit review')),
      );
    } finally {
      setState(() => isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (loading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    if (set == null || allCards.isEmpty) {
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
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.menu_book, size: 64, color: Color(0xFFCBD5E1)),
                    const SizedBox(height: 12),
                    Text(
                      'No cards in this set.',
                      style: GoogleFonts.outfit(color: const Color(0xFF64748B), fontSize: 16),
                    ),
                    const SizedBox(height: 20),
                    ElevatedButton(
                      onPressed: () => context.pop(),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0F9D58),
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      ),
                      child: Text('Go Back', style: GoogleFonts.outfit()),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      );
    }

    final card = allCards[currentIndex];

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
            child: SingleChildScrollView(
              child: Column(
                children: [
                  // Header Area matching Web layout
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        // Exit Study Button
                        InkWell(
                          onTap: () => context.pop(),
                          borderRadius: BorderRadius.circular(8),
                          child: Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            child: Row(
                              children: [
                                const Icon(Icons.arrow_back, size: 16, color: Color(0xFF64748B)),
                                const SizedBox(width: 4),
                                Text(
                                  'Exit Study',
                                  style: GoogleFonts.outfit(
                                    fontSize: 13,
                                    fontWeight: FontWeight.bold,
                                    color: const Color(0xFF64748B),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),

                        // Set Title
                        Expanded(
                          child: Center(
                            child: Text(
                              set!['title'] ?? '',
                              textAlign: TextAlign.center,
                              style: GoogleFonts.outfit(
                                fontSize: 16,
                                fontWeight: FontWeight.w900,
                                color: const Color(0xFF0F172A),
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ),

                        // Progress indicator
                        Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              '${currentIndex + 1} / ${allCards.length}',
                              style: GoogleFonts.outfit(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: const Color(0xFF64748B),
                              ),
                            ),
                            const SizedBox(width: 8),
                            SizedBox(
                              width: 60,
                              height: 3,
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(2),
                                child: LinearProgressIndicator(
                                  value: (currentIndex + 1) / allCards.length,
                                  backgroundColor: const Color(0xFFE2E8F0),
                                  valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFF3B82F6)),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 24), // Compact Web Spacing instead of expanding Spacer

                  // Card Widget with Flip Animation
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 24),
                    child: Center(
                      child: ConstrainedBox(
                        constraints: const BoxConstraints(maxWidth: 600), // Prevent infinite stretch on wide screens
                        child: SizedBox(
                          height: 260,
                          width: double.infinity,
                          child: GestureDetector(
                            onTap: _flip,
                            child: AnimatedBuilder(
                              animation: _flipAnim,
                              builder: (_, __) {
                                final angle = _flipAnim.value * 3.14159;
                                final showFront = _flipAnim.value <= 0.5;
                                return Transform(
                                  alignment: Alignment.center,
                                  transform: Matrix4.identity()
                                    ..setEntry(3, 2, 0.0015)
                                    ..rotateY(angle),
                                  child: showFront
                                      ? _buildCardFace(
                                          label: 'QUESTION',
                                          text: card['term'] ?? '',
                                          hint: 'Click anywhere to reveal',
                                          cardBgColor: const Color(0xFFDDDDDD), // Grey matching web front
                                        )
                                      : Transform(
                                          alignment: Alignment.center,
                                          transform: Matrix4.identity()..rotateY(3.14159),
                                          child: _buildCardFace(
                                            label: 'ANSWER',
                                            text: card['definition'] ?? '',
                                            hint: 'Click anywhere to hide',
                                            cardBgColor: const Color(0xFFEDE9E6), // Warm light grey matching web back
                                          ),
                                        ),
                                );
                              },
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),

                  const SizedBox(height: 28), // Compact Web Spacing instead of expanding Spacer

                  // Rating & Feedback controls underneath (Always visible)
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 24),
                    child: Center(
                      child: ConstrainedBox(
                        constraints: const BoxConstraints(maxWidth: 600),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              'How well did you remember this?',
                              style: GoogleFonts.outfit(
                                fontSize: 13,
                                fontWeight: FontWeight.w700,
                                color: const Color(0xFF475569),
                              ),
                            ),
                            const SizedBox(height: 16),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                // Red X Button
                                GestureDetector(
                                  onTap: () => _submitReview(1),
                                  child: Container(
                                    width: 56,
                                    height: 56,
                                    decoration: BoxDecoration(
                                      color: Colors.white,
                                      borderRadius: BorderRadius.circular(12),
                                      border: Border.all(color: const Color(0xFFE2E8F0)),
                                      boxShadow: [
                                        BoxShadow(
                                          color: Colors.black.withOpacity(0.02),
                                          blurRadius: 4,
                                          offset: const Offset(0, 2),
                                        ),
                                      ],
                                    ),
                                    child: const Center(
                                      child: Icon(Icons.close, color: Color(0xFFEF4444), size: 24),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 24),
                                // Green Check Button
                                GestureDetector(
                                  onTap: () => _submitReview(5),
                                  child: Container(
                                    width: 56,
                                    height: 56,
                                    decoration: BoxDecoration(
                                      color: Colors.white,
                                      borderRadius: BorderRadius.circular(12),
                                      border: Border.all(color: const Color(0xFFE2E8F0)),
                                      boxShadow: [
                                        BoxShadow(
                                          color: Colors.black.withOpacity(0.02),
                                          blurRadius: 4,
                                          offset: const Offset(0, 2),
                                        ),
                                      ],
                                    ),
                                    child: const Center(
                                      child: Icon(Icons.check, color: Color(0xFF22C55E), size: 24),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  // Builder helper for clean card matching web mockup
  Widget _buildCardFace({
    required String label,
    required String text,
    required String hint,
    required Color cardBgColor,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: cardBgColor,
        borderRadius: BorderRadius.circular(6), // Sharp 6px radius matching web
        border: Border.all(color: const Color(0xFFCBD5E1)), // Solid slate-300 border
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.08), // Web shadow color
            blurRadius: 30, // 30px blur
            offset: const Offset(0, 8), // 8px Y offset
          ),
        ],
      ),
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Label
          Text(
            label,
            style: GoogleFonts.outfit(
              fontSize: 11,
              fontWeight: FontWeight.bold,
              color: const Color(0xFF64748B),
              letterSpacing: 1.0,
            ),
          ),
          const SizedBox(height: 12),
          Divider(
            color: const Color(0xFF0F172A).withOpacity(0.15), // Faint dark divider
            height: 1,
            thickness: 1,
          ),
          
          // Question or Answer Text
          Expanded(
            child: Center(
              child: SingleChildScrollView(
                child: Builder(
                  builder: (context) {
                    final isList = RegExp(r'(?:\s+|^)\d+\.\s').hasMatch(text);
                    return Text(
                      text,
                      textAlign: isList ? TextAlign.left : TextAlign.center, // Left align lists to match web indentation
                      style: GoogleFonts.outfit(
                        fontSize: 22, // 22px matching web 1.5rem scaling
                        fontWeight: FontWeight.bold, // Bold text
                        color: const Color(0xFF0F172A),
                        height: 1.6, // 1.6 line height
                      ),
                    );
                  }
                ),
              ),
            ),
          ),

          Divider(
            color: const Color(0xFF0F172A).withOpacity(0.15), // Matching bottom divider
            height: 1,
            thickness: 1,
          ),
          const SizedBox(height: 12),

          // Bottom Hint
          Center(
            child: Text(
              hint,
              style: GoogleFonts.outfit(
                fontSize: 11,
                fontWeight: FontWeight.bold, // Bold footer hint
                color: const Color(0xFF64748B), // Slate matching header
              ),
            ),
          ),
        ],
      ),
    );
  }
}
