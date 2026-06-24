import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../services/api_service.dart';
import '../app/theme.dart';
import '../providers/auth_provider.dart';
import '../widgets/study_notepad_widget.dart';

class FlashcardStudyScreen extends StatefulWidget {
  final int setId;
  const FlashcardStudyScreen({super.key, required this.setId});

  @override
  State<FlashcardStudyScreen> createState() => _FlashcardStudyScreenState();
}

class _FlashcardStudyScreenState extends State<FlashcardStudyScreen>
    with SingleTickerProviderStateMixin {
  Map<String, dynamic>? set;
  bool loading = true;
  int currentIndex = 0;
  bool showAnswer = false;
  bool isAnswerRevealed = false;
  late AnimationController _flipCtrl;
  late Animation<double> _flipAnim;
  List<dynamic> _cards = [];
  bool isSubmitting = false;
  final TextEditingController _typeController = TextEditingController();
  final FocusNode _focusNode = FocusNode();

  @override
  void initState() {
    super.initState();
    _flipCtrl = AnimationController(
        vsync: this, duration: const Duration(milliseconds: 400));
    _flipAnim = Tween(begin: 0.0, end: 1.0).animate(
        CurvedAnimation(parent: _flipCtrl, curve: Curves.easeInOut));
    _load();
  }

  @override
  void dispose() {
    _flipCtrl.dispose();
    _typeController.dispose();
    _focusNode.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    try {
      final d = await ApiService.getDueFlashcards(widget.setId);
      setState(() {
        set = d['flashcard_set'];
        _cards = List<dynamic>.from(d['due_cards'] ?? []);
        loading = false;
      });
    } catch (_) {
      setState(() => loading = false);
    }
  }

  void _flip() {
    if (showAnswer) return; // Prevent flipping back while studying

    _flipCtrl.forward();
    setState(() {
      showAnswer = true;
      isAnswerRevealed = false;
      _typeController.clear();
    });
    
    // Auto focus the input after flip
    Future.delayed(const Duration(milliseconds: 500), () {
      if (mounted) _focusNode.requestFocus();
    });
  }

  void _checkTyping(String val) {
    final correct = (_cards[currentIndex]['definition'] as String).trim().toLowerCase();
    if (val.trim().toLowerCase() == correct) {
      setState(() => isAnswerRevealed = true);
      _focusNode.unfocus();
    }
  }

  void _giveUp() {
    setState(() => isAnswerRevealed = true);
    _focusNode.unfocus();
  }

  void _tryAgain() {
    setState(() {
      isAnswerRevealed = false;
      _typeController.clear();
    });
    _focusNode.requestFocus();
  }

  Future<void> _submitReview(int quality) async {
    if (isSubmitting) return;
    setState(() => isSubmitting = true);

    final card = _cards[currentIndex];
    try {
      await ApiService.submitFlashcardReview(card['id'], quality);

      if (currentIndex < _cards.length - 1) {
        setState(() {
          currentIndex++;
          showAnswer = false;
          isAnswerRevealed = false;
          _typeController.clear();
        });
        _flipCtrl.reset();
      } else {
        if (mounted) {
          showDialog(
            context: context,
            barrierDismissible: false,
            builder: (ctx) => AlertDialog(
              title: const Text('All Done! 🎉'),
              content: const Text(
                  "You've reviewed all due flashcards for today. Great job!"),
              actions: [
                TextButton(
                  onPressed: () {
                    Navigator.of(ctx).pop();
                    Navigator.of(context).pop();
                  },
                  child: const Text('Back to Sets'),
                ),
              ],
            ),
          );
        }
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Failed to submit review')),
      );
    } finally {
      setState(() => isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.read<AuthProvider>();
    final isReadWrite = auth.user?['learning_style'] == 'read_write';

    if (loading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }
    if (set == null) {
      return Scaffold(
          appBar: AppBar(title: const Text('Study Mode')),
          body: const Center(child: Text('Failed to load')));
    }

    if (_cards.isEmpty) {
      return Scaffold(
        appBar: AppBar(title: Text(set!['title'] ?? 'Study Mode')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.celebration, size: 64, color: Colors.orange),
              const SizedBox(height: 16),
              const Text("You're all caught up!",
                  style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              const Text("There are no cards due for review right now."),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () => Navigator.of(context).pop(),
                child: const Text('Go Back'),
              ),
            ],
          ),
        ),
      );
    }

    final card = _cards[currentIndex];

    return Scaffold(
      appBar: AppBar(
        title: Text('Study: ${set!['title'] ?? ''}'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Reviewing Card ${currentIndex + 1} of ${_cards.length}',
                  style: const TextStyle(
                      color: Colors.grey,
                      fontSize: 14,
                      fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: 8),
            LinearProgressIndicator(
              value: (currentIndex + 1) / _cards.length,
              color: AppTheme.primaryLight,
              backgroundColor: Colors.grey.shade200,
            ),
            const SizedBox(height: 30),

            // Flashcard
            SizedBox(
              height: 300,
              child: GestureDetector(
                onTap: _flip,
                child: AnimatedBuilder(
                  animation: _flipAnim,
                  builder: (_, child) {
                    final angle = _flipAnim.value * 3.14159;
                    final showFront = _flipAnim.value <= 0.5;
                    return Transform(
                      alignment: Alignment.center,
                      transform: Matrix4.identity()
                        ..setEntry(3, 2, 0.001)
                        ..rotateY(angle),
                      child: showFront
                          ? _cardFace(
                              label: 'TERM',
                              text: card['term'] ?? '',
                              color: AppTheme.primary,
                              icon: Icons.help_outline,
                            )
                          : Transform(
                              alignment: Alignment.center,
                              transform: Matrix4.identity()..rotateY(3.14159),
                              child: _cardFace(
                                label: 'DEFINITION',
                                isTypingMode: true,
                                text: card['definition'] ?? '',
                                color: isAnswerRevealed ? Colors.teal.shade700 : Colors.indigo.shade700,
                                icon: isAnswerRevealed ? Icons.check_circle_outline : Icons.keyboard,
                              ),
                            ),
                    );
                  },
                ),
              ),
            ),

            const SizedBox(height: 24),

            if (!showAnswer)
              const Text('Tap the card to reveal and start typing',
                  style: TextStyle(color: Colors.grey, fontSize: 14)),

            if (showAnswer && !isAnswerRevealed)
              Column(
                children: [
                  TextField(
                    controller: _typeController,
                    focusNode: _focusNode,
                    onChanged: _checkTyping,
                    textAlign: TextAlign.center,
                    decoration: InputDecoration(
                      hintText: 'Type the answer...',
                      filled: true,
                      fillColor: Colors.grey.shade100,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(15)),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextButton(
                    onPressed: _giveUp,
                    child: const Text('I give up, show answer', style: TextStyle(color: Colors.grey)),
                  ),
                ],
              ),

            if (isAnswerRevealed)
              Column(
                children: [
                  const Text('How well did you remember this?',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
                  const SizedBox(height: 16),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                    children: [
                      _ratingBtn('Again', Colors.red, 0),
                      _ratingBtn('Hard', Colors.orange, 3),
                      _ratingBtn('Good', Colors.green, 4),
                      _ratingBtn('Easy', Colors.blue, 5),
                    ],
                  ),
                  const SizedBox(height: 12),
                  TextButton.icon(
                    onPressed: _tryAgain,
                    icon: const Icon(Icons.refresh, size: 18),
                    label: const Text('Try Again'),
                  ),
                ],
              ),
            if (isReadWrite) ...[
              const SizedBox(height: 24),
              StudyNotepadWidget(
                resourceType: 'flashcard',
                resourceId: widget.setId,
                topic: set!['topic'] ?? 'General',
                defaultTitle: 'Notes: ${set!['title'] ?? ''}',
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _ratingBtn(String label, Color color, int quality) {
    return isSubmitting
        ? const CircularProgressIndicator()
        : ElevatedButton(
            onPressed: () => _submitReview(quality),
            style: ElevatedButton.styleFrom(
              backgroundColor: color,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: Text(label),
          );
  }

  Widget _cardFace({
    required String label,
    required String text,
    required Color color,
    required IconData icon,
    bool isTypingMode = false,
  }) {
    String placeholder = '';
    if (isTypingMode && !isAnswerRevealed) {
      placeholder = text.split(' ').map((word) => '_ ' * word.length).join('   ');
    }

    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
              color: color.withOpacity(0.3),
              blurRadius: 20,
              offset: const Offset(0, 8))
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(28),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, color: Colors.white54, size: 36),
            const SizedBox(height: 8),
            Text(label,
                style: const TextStyle(
                    color: Colors.white54,
                    fontSize: 12,
                    letterSpacing: 2,
                    fontWeight: FontWeight.bold)),
            const SizedBox(height: 20),
            if (isTypingMode && !isAnswerRevealed)
              Text(
                placeholder,
                textAlign: TextAlign.center,
                style: const TextStyle(
                    color: Colors.white70,
                    fontSize: 22,
                    letterSpacing: 4,
                    fontFamily: 'monospace',
                    fontWeight: FontWeight.bold),
              )
            else
              Text(
                text,
                textAlign: TextAlign.center,
                style: const TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.w600,
                    height: 1.5),
              ),
          ],
        ),
      ),
    );
  }
}

