import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../app/theme.dart';

enum FlashcardMode { read, revision }

class FlashcardPracticeScreen extends StatefulWidget {
  final int setId;
  const FlashcardPracticeScreen({super.key, required this.setId});

  @override
  State<FlashcardPracticeScreen> createState() =>
      _FlashcardPracticeScreenState();
}

class _FlashcardPracticeScreenState extends State<FlashcardPracticeScreen>
    with SingleTickerProviderStateMixin {
  // ── Data ──────────────────────────────────────────────────────────────────
  Map<String, dynamic>? set;
  List<dynamic> allCards = [];
  bool loading = true;

  // ── State ─────────────────────────────────────────────────────────────────
  FlashcardMode mode = FlashcardMode.read;
  int currentIndex = 0;
  bool isFlipped = false;

  // Revision-mode substates
  bool isAnswerRevealed = false; // placeholder → revealed
  bool wasCorrect = false; // typed correctly vs gave up

  // ── Animation ─────────────────────────────────────────────────────────────
  late AnimationController _flipCtrl;
  late Animation<double> _flipAnim;

  // ── Typing ────────────────────────────────────────────────────────────────
  final TextEditingController _typeCtrl = TextEditingController();
  final FocusNode _focusNode = FocusNode();
  String _placeholderText = '';

  // ─────────────────────────────────────────────────────────────────────────

  @override
  void initState() {
    super.initState();
    _flipCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 450));
    _flipAnim = Tween(begin: 0.0, end: 1.0).animate(
        CurvedAnimation(parent: _flipCtrl, curve: Curves.easeInOut));
    _load();
  }

  @override
  void dispose() {
    _flipCtrl.dispose();
    _typeCtrl.dispose();
    _focusNode.dispose();
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

  // ── Mode switching ────────────────────────────────────────────────────────
  void _setMode(FlashcardMode m) {
    setState(() {
      mode = m;
      currentIndex = 0;
      _resetCard();
    });
  }

  void _resetCard() {
    isFlipped = false;
    isAnswerRevealed = false;
    wasCorrect = false;
    _typeCtrl.clear();
    _placeholderText = '';
    _flipCtrl.reset();
  }

  // ── Flip ──────────────────────────────────────────────────────────────────
  void _flip() {
    if (mode == FlashcardMode.read) {
      // Read mode: toggle freely
      if (isFlipped) {
        _flipCtrl.reverse();
      } else {
        _flipCtrl.forward();
      }
      setState(() => isFlipped = !isFlipped);
    } else {
      // Revision mode: only flip once; then lock
      if (!isFlipped) {
        _flipCtrl.forward();
        setState(() {
          isFlipped = true;
          _buildPlaceholder();
        });
        Future.delayed(const Duration(milliseconds: 500), () {
          if (mounted && !isAnswerRevealed) _focusNode.requestFocus();
        });
      }
    }
  }

  void _buildPlaceholder() {
    final card = allCards[currentIndex];
    final definition = (card['definition'] as String? ?? '');
    _placeholderText =
        definition.split(' ').map((w) => '_' * w.length).join('   ');
  }

  // ── Typing ────────────────────────────────────────────────────────────────
  void _onType(String val) {
    final card = allCards[currentIndex];
    final correct = (card['definition'] as String? ?? '').trim().toLowerCase();
    final definition = (card['definition'] as String? ?? '');

    // Build live placeholder (fill letters in as typed)
    String display = '';
    for (int i = 0; i < definition.length; i++) {
      if (i < val.length) {
        display += definition[i] == ' ' ? '   ' : val[i];
      } else {
        display += definition[i] == ' ' ? '   ' : '_';
      }
    }

    setState(() => _placeholderText = display);

    if (val.trim().toLowerCase() == correct) {
      _focusNode.unfocus();
      setState(() {
        isAnswerRevealed = true;
        wasCorrect = true;
      });
      // Auto-submit high score
      ApiService.submitFlashcardReview(card['id'], 5);
    }
  }

  void _giveUp() {
    final card = allCards[currentIndex];
    _focusNode.unfocus();
    setState(() {
      isAnswerRevealed = true;
      wasCorrect = false;
    });
    // Auto-submit low score
    ApiService.submitFlashcardReview(card['id'], 0);
  }

  void _tryAgain() {
    setState(() {
      isAnswerRevealed = false;
      wasCorrect = false;
      _typeCtrl.clear();
      _buildPlaceholder();
    });
    _focusNode.requestFocus();
  }

  // ── Navigation ────────────────────────────────────────────────────────────
  void _next() {
    if (currentIndex < allCards.length - 1) {
      setState(() {
        currentIndex++;
        _resetCard();
      });
    }
  }

  void _prev() {
    if (currentIndex > 0) {
      setState(() {
        currentIndex--;
        _resetCard();
      });
    }
  }

  void _shuffle() {
    setState(() {
      allCards.shuffle();
      currentIndex = 0;
      _resetCard();
    });
  }

  // ─────────────────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    if (loading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }
    if (set == null || allCards.isEmpty) {
      return Scaffold(
        appBar: AppBar(title: Text(set?['title'] ?? 'Flashcards')),
        body: const Center(child: Text('No cards in this set.')),
      );
    }

    final card = allCards[currentIndex];
    final isRead = mode == FlashcardMode.read;

    return Scaffold(
      appBar: AppBar(
        title: Text(set!['title'] ?? 'Flashcards'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            // ── Mode Toggle ────────────────────────────────────────────────
            Container(
              decoration: BoxDecoration(
                color: Colors.grey.shade100,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.shade300),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  _modeBtn(
                    label: 'Read Mode',
                    icon: Icons.menu_book,
                    selected: isRead,
                    onTap: () => _setMode(FlashcardMode.read),
                    selectedColor: Colors.blue,
                  ),
                  _modeBtn(
                    label: 'Revision Mode',
                    icon: Icons.psychology,
                    selected: !isRead,
                    onTap: () => _setMode(FlashcardMode.revision),
                    selectedColor: AppTheme.primary,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // ── Counter Badge ──────────────────────────────────────────────
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
              decoration: BoxDecoration(
                color: isRead ? Colors.grey.shade600 : AppTheme.primary,
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                '${isRead ? "Card" : "Reviewing Card"} ${currentIndex + 1} of ${allCards.length}',
                style: const TextStyle(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.bold),
              ),
            ),
            const SizedBox(height: 16),

            // ── Flashcard ──────────────────────────────────────────────────
            SizedBox(
              height: 320,
              child: GestureDetector(
                onTap: _flip,
                child: AnimatedBuilder(
                  animation: _flipAnim,
                  builder: (_, _) {
                    final angle = _flipAnim.value * 3.14159;
                    final showFront = _flipAnim.value <= 0.5;
                    return Transform(
                      alignment: Alignment.center,
                      transform: Matrix4.identity()
                        ..setEntry(3, 2, 0.001)
                        ..rotateY(angle),
                      child: showFront
                          ? _buildFront(card)
                          : Transform(
                              alignment: Alignment.center,
                              transform: Matrix4.identity()..rotateY(3.14159),
                              child: _buildBack(card),
                            ),
                    );
                  },
                ),
              ),
            ),
            const SizedBox(height: 20),

            // ── Below-card controls ────────────────────────────────────────
            if (isRead) ...[
              // Read mode: shuffle hint
              if (!isFlipped)
                const Text('Tap to flip',
                    style: TextStyle(color: Colors.grey, fontSize: 14)),
            ] else ...[
              // Revision mode: typing area or grading area
              if (!isFlipped)
                Text(
                  'Think of the answer, then tap the card to flip and type it.',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                      color: Colors.orange.shade700,
                      fontStyle: FontStyle.italic,
                      fontSize: 14),
                ),
              if (isFlipped && !isAnswerRevealed) ...[
                TextField(
                  controller: _typeCtrl,
                  focusNode: _focusNode,
                  onChanged: _onType,
                  textAlign: TextAlign.center,
                  autocorrect: false,
                  enableSuggestions: false,
                  decoration: InputDecoration(
                    hintText: 'Type the exact answer...',
                    filled: true,
                    fillColor: Colors.grey.shade100,
                    border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12)),
                    contentPadding: const EdgeInsets.symmetric(
                        horizontal: 16, vertical: 14),
                  ),
                ),
                const SizedBox(height: 8),
                OutlinedButton(
                  onPressed: _giveUp,
                  child: const Text('I give up, show answer'),
                ),
              ],
              if (isAnswerRevealed) ...[
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  decoration: BoxDecoration(
                    color: wasCorrect
                        ? Colors.green.shade50
                        : Colors.orange.shade50,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                        color: wasCorrect
                            ? Colors.green.shade200
                            : Colors.orange.shade200),
                  ),
                  child: Text(
                    wasCorrect
                        ? '✅ Perfect! Click Next when ready.'
                        : '⚠️ Answer Revealed. Click Next when ready.',
                    style: TextStyle(
                      color: wasCorrect
                          ? Colors.green.shade800
                          : Colors.orange.shade800,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                const SizedBox(height: 8),
                ElevatedButton.icon(
                  onPressed: _tryAgain,
                  icon: const Icon(Icons.refresh, size: 18),
                  label: const Text('Try Again'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.amber,
                    foregroundColor: Colors.black87,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ],
            ],
            const SizedBox(height: 20),

            // ── Navigation Row ─────────────────────────────────────────────
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                ElevatedButton.icon(
                  onPressed: currentIndex > 0 ? _prev : null,
                  icon: const Icon(Icons.chevron_left),
                  label: const Text('Previous'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.grey.shade600,
                    foregroundColor: Colors.white,
                    disabledBackgroundColor: Colors.grey.shade200,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10)),
                  ),
                ),
                ElevatedButton.icon(
                  onPressed: currentIndex < allCards.length - 1 ? _next : null,
                  icon: const Text('Next'),
                  label: const Icon(Icons.chevron_right),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.grey.shade600,
                    foregroundColor: Colors.white,
                    disabledBackgroundColor: Colors.grey.shade200,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ],
            ),

            // ── Shuffle (Read Mode only) ───────────────────────────────────
            if (isRead) ...[
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: _shuffle,
                  icon: const Icon(Icons.shuffle, size: 18),
                  label: const Text('Shuffle Cards'),
                  style: OutlinedButton.styleFrom(
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ),
            ],
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  // ── Card faces ────────────────────────────────────────────────────────────
  Widget _buildFront(Map<String, dynamic> card) {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: const Color(0xFF1ABC9C),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
              color: Colors.teal.withOpacity(0.4),
              blurRadius: 20,
              offset: const Offset(0, 8))
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(28),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Text('TERM',
                style: TextStyle(
                    color: Colors.white60,
                    fontSize: 11,
                    letterSpacing: 2,
                    fontWeight: FontWeight.bold)),
            const SizedBox(height: 16),
            Expanded(
              child: Center(
                child: Text(
                  card['term'] ?? '',
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                      color: Colors.white,
                      fontSize: 22,
                      fontWeight: FontWeight.w600,
                      height: 1.4),
                ),
              ),
            ),
            const SizedBox(height: 8),
            const Text('☝ Tap to flip',
                style: TextStyle(color: Colors.white60, fontSize: 12)),
          ],
        ),
      ),
    );
  }

  Widget _buildBack(Map<String, dynamic> card) {
    final isRevision = mode == FlashcardMode.revision;

    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: isAnswerRevealed
            ? const Color(0xFF16A085)
            : (isRevision ? const Color(0xFF2C3E7A) : const Color(0xFF16A085)),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
              color: Colors.teal.withOpacity(0.4),
              blurRadius: 20,
              offset: const Offset(0, 8))
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(28),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              isRevision ? card['term'] ?? '' : 'DEFINITION',
              style: const TextStyle(
                  color: Colors.white60,
                  fontSize: 11,
                  letterSpacing: 2,
                  fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            Expanded(
              child: Center(
                child: isRevision && !isAnswerRevealed
                    ? Text(
                        _placeholderText,
                        textAlign: TextAlign.center,
                        style: const TextStyle(
                          color: Colors.white70,
                          fontSize: 22,
                          fontWeight: FontWeight.bold,
                          fontFamily: 'monospace',
                          letterSpacing: 4,
                        ),
                      )
                    : Text(
                        card['definition'] ?? '',
                        textAlign: TextAlign.center,
                        style: const TextStyle(
                            color: Colors.white,
                            fontSize: 20,
                            fontWeight: FontWeight.w600,
                            height: 1.4),
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Mode toggle button ────────────────────────────────────────────────────
  Widget _modeBtn({
    required String label,
    required IconData icon,
    required bool selected,
    required VoidCallback onTap,
    required Color selectedColor,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(
          color: selected ? selectedColor : Colors.transparent,
          borderRadius: BorderRadius.circular(10),
        ),
        child: Row(
          children: [
            Icon(icon,
                color: selected ? Colors.white : Colors.grey.shade600,
                size: 16),
            const SizedBox(width: 6),
            Text(
              label,
              style: TextStyle(
                  color: selected ? Colors.white : Colors.grey.shade600,
                  fontWeight:
                      selected ? FontWeight.bold : FontWeight.normal,
                  fontSize: 13),
            ),
          ],
        ),
      ),
    );
  }
}
