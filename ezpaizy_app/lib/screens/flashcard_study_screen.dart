import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
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
  List<Map<String, dynamic>> _currentItems = [];
  String _typedVal = '';

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

  List<Map<String, dynamic>> _parseDefinitionItems(String definition) {
    final normalized = definition.trim();
    final regex = RegExp(r'(?:^|\s+)(\d+\.)\s+');
    final matches = regex.allMatches(normalized).toList();
    final List<Map<String, dynamic>> items = [];

    if (matches.isNotEmpty) {
      for (int i = 0; i < matches.length; i++) {
        final start = matches[i].end;
        final end = (i + 1 < matches.length) ? matches[i + 1].start : normalized.length;
        final text = normalized.substring(start, end).trim();
        items.add({
          'number': matches[i].group(1) ?? '',
          'text': text,
          'cleanText': text.toLowerCase().replaceAll(RegExp(r'[^a-z0-9]'), ''),
          'revealed': false,
        });
      }
    } else {
      items.add({
        'number': '',
        'text': normalized,
        'cleanText': normalized.toLowerCase().replaceAll(RegExp(r'[^a-z0-9]'), ''),
        'revealed': false,
      });
    }
    return items;
  }

  void _initCardItems() {
    if (_cards.isNotEmpty && currentIndex < _cards.length) {
      _currentItems = _parseDefinitionItems(_cards[currentIndex]['definition'] ?? '');
      _typedVal = '';
    }
  }

  Widget _buildPlaceholderWidget(List<Map<String, dynamic>> items, String typedVal) {
    final isList = items.length > 1 || (items.isNotEmpty && items[0]['number'].toString().isNotEmpty);
    final activeIndex = items.indexWhere((item) => !item['revealed']);

    return Column(
      crossAxisAlignment: isList ? CrossAxisAlignment.start : CrossAxisAlignment.center,
      children: List.generate(items.length, (idx) {
        final item = items[idx];
        
        Widget textWidget;
        if (item['revealed']) {
          textWidget = Text(
            item['text'],
            style: const TextStyle(
              color: Color(0xFF22C55E),
              fontWeight: FontWeight.bold,
              fontSize: 18,
            ),
          );
        } else if (idx == activeIndex && typedVal.isNotEmpty) {
          final correctText = item['text'] as String;
          String display = '';
          int tIdx = 0;
          
          for (int i = 0; i < correctText.length; i++) {
            final c = correctText[i];
            if (c == ' ') {
              display += '   ';
              if (tIdx < typedVal.length && typedVal[tIdx] == ' ') {
                tIdx++;
              }
            } else {
              if (tIdx < typedVal.length) {
                if (typedVal[tIdx] == ' ') {
                  display += '   ';
                } else {
                  display += typedVal[tIdx];
                }
                tIdx++;
              } else {
                if (RegExp(r'[a-zA-Z0-9]').hasMatch(c)) {
                  display += '_';
                } else {
                  display += c;
                }
              }
            }
          }
          
          textWidget = Text(
            display,
            style: const TextStyle(
              color: Color(0xFF64748B),
              fontFamily: 'monospace',
              letterSpacing: 2,
              fontSize: 18,
            ),
          );
        } else {
          final correctText = item['text'] as String;
          final underscores = correctText.split(RegExp(r'\s+')).map((word) {
            return word.split('').map((c) {
              return RegExp(r'[a-zA-Z0-9]').hasMatch(c) ? '_' : c;
            }).join('');
          }).join('   ');

          textWidget = Text(
            underscores,
            style: const TextStyle(
              color: Color(0xFF94A3B8),
              fontFamily: 'monospace',
              letterSpacing: 2,
              fontSize: 18,
            ),
          );
        }

        if (isList && item['number'].toString().isNotEmpty) {
          return Padding(
            padding: const EdgeInsets.symmetric(vertical: 4),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                SizedBox(
                  width: 35,
                  child: Text(
                    item['number'],
                    style: const TextStyle(
                      fontFamily: 'monospace',
                      fontWeight: FontWeight.bold,
                      fontSize: 18,
                      color: Color(0xFF334155),
                    ),
                  ),
                ),
                Expanded(child: textWidget),
              ],
            ),
          );
        } else {
          return Padding(
            padding: const EdgeInsets.symmetric(vertical: 4),
            child: textWidget,
          );
        }
      }),
    );
  }

  Future<void> _load() async {
    try {
      final d = await ApiService.getDueFlashcards(widget.setId);
      setState(() {
        set = d['flashcard_set'];
        _cards = List<dynamic>.from(d['due_cards'] ?? []);
        loading = false;
        _initCardItems();
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
    setState(() {
      _typedVal = val;
    });

    final cleanInput = val.trim().toLowerCase().replaceAll(RegExp(r'[^a-z0-9]'), '');
    if (cleanInput.isEmpty) return;

    // Full phrase match check
    final correctAllClean = _cards[currentIndex]['definition'].toString().toLowerCase().replaceAll(RegExp(r'[^a-z0-9]'), '');
    final correctItemsClean = _currentItems.map((item) => item['cleanText']).join('');

    if (cleanInput == correctAllClean || cleanInput == correctItemsClean) {
      setState(() {
        for (var item in _currentItems) {
          item['revealed'] = true;
        }
        isAnswerRevealed = true;
        _typedVal = '';
      });
      _typeController.clear();
      _focusNode.unfocus();
      return;
    }

    // Individual item check
    int matchedIndex = -1;
    for (int i = 0; i < _currentItems.length; i++) {
      if (!_currentItems[i]['revealed']) {
        final cleanText = _currentItems[i]['cleanText'];
        final cleanTextWithNumber = (_currentItems[i]['number'] + _currentItems[i]['text'])
            .toString()
            .toLowerCase()
            .replaceAll(RegExp(r'[^a-z0-9]'), '');

        if (cleanInput == cleanText || cleanInput == cleanTextWithNumber) {
          matchedIndex = i;
          break;
        }
      }
    }

    if (matchedIndex != -1) {
      setState(() {
        _currentItems[matchedIndex]['revealed'] = true;
        _typedVal = '';
        
        // Check if all are done
        final allDone = _currentItems.every((item) => item['revealed']);
        if (allDone) {
          isAnswerRevealed = true;
          _focusNode.unfocus();
        }
      });
      _typeController.clear();
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
          _initCardItems();
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
    final isKinesthetic = auth.user?['learning_style'] == 'kinesthetic';

    if (!isKinesthetic) {
      return Scaffold(
        appBar: AppBar(title: const Text('Review Mode')),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.lock, size: 64, color: Colors.orange),
                const SizedBox(height: 16),
                const Text(
                  'Exclusive Mode',
                  style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                const Text(
                  'Active Review Mode is customized specifically for Kinaesthetic Learners to support physical touch and swipe card interactions.',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 14, color: Colors.grey),
                ),
                const SizedBox(height: 24),
                ElevatedButton(
                  onPressed: () => Navigator.of(context).pop(),
                  child: const Text('Go Back'),
                ),
              ],
            ),
          ),
        ),
      );
    }

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

    Widget cardWidget = SizedBox(
      height: 350,
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
                      label: 'QUESTION',
                      color: Colors.white,
                      textColor: const Color(0xFF0F172A),
                      child: Text(
                        card['term'] ?? '',
                        style: GoogleFonts.outfit(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFF0F172A),
                        ),
                        textAlign: TextAlign.center,
                      ),
                      footerWidget: Text(
                        'Click anywhere to reveal',
                        style: GoogleFonts.outfit(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFF94A3B8),
                        ),
                      ),
                    )
                  : Transform(
                      alignment: Alignment.center,
                      transform: Matrix4.identity()..rotateY(3.14159),
                      child: _cardFace(
                        label: 'ANSWER',
                        color: Colors.white,
                        textColor: const Color(0xFF0F172A),
                        headerRightWidget: !isAnswerRevealed
                            ? OutlinedButton(
                                onPressed: () {
                                  setState(() {
                                    for (var item in _currentItems) {
                                      item['revealed'] = true;
                                    }
                                    isAnswerRevealed = true;
                                  });
                                },
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: const Color(0xFF64748B),
                                  side: const BorderSide(color: Color(0xFFCBD5E1)),
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                                ),
                                child: Text('Reveal', style: GoogleFonts.outfit(fontSize: 11)),
                              )
                            : null,
                        footerWidget: !isAnswerRevealed
                            ? TextField(
                                controller: _typeController,
                                focusNode: _focusNode,
                                onChanged: _checkTyping,
                                textAlign: TextAlign.center,
                                style: GoogleFonts.outfit(fontSize: 14),
                                decoration: InputDecoration(
                                  hintText: 'Type the exact answer...',
                                  hintStyle: GoogleFonts.outfit(color: const Color(0xFF94A3B8), fontSize: 13),
                                  contentPadding: const EdgeInsets.symmetric(vertical: 8),
                                  filled: true,
                                  fillColor: Colors.white,
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(6),
                                    borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                                  ),
                                  focusedBorder: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(6),
                                    borderSide: const BorderSide(color: Color(0xFF3B82F6), width: 1.5),
                                  ),
                                ),
                              )
                            : Text(
                                'Click anywhere to flip back',
                                style: GoogleFonts.outfit(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                  color: const Color(0xFF94A3B8),
                                ),
                              ),
                        child: _buildPlaceholderWidget(_currentItems, _typedVal),
                      ),
                    ),
            );
          },
        ),
      ),
    );

    if (isKinesthetic && showAnswer) {
      cardWidget = Dismissible(
        key: ValueKey<int>(card['id']),
        direction: DismissDirection.horizontal,
        onDismissed: (direction) {
          if (direction == DismissDirection.endToStart) {
            // Swiped Left: Again (0)
            _submitReview(0);
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Marked: Again 🔴'), duration: Duration(milliseconds: 700)),
            );
          } else {
            // Swiped Right: Easy (5)
            _submitReview(5);
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(content: Text('Marked: Easy 🟢'), duration: Duration(milliseconds: 700)),
            );
          }
        },
        background: Container(
          alignment: Alignment.centerLeft,
          padding: const EdgeInsets.only(left: 20),
          color: Colors.blue.withOpacity(0.2),
          child: const Icon(Icons.sentiment_very_satisfied, color: Colors.blue, size: 50),
        ),
        secondaryBackground: Container(
          alignment: Alignment.centerRight,
          padding: const EdgeInsets.only(right: 20),
          color: Colors.red.withOpacity(0.2),
          child: const Icon(Icons.sentiment_very_dissatisfied, color: Colors.red, size: 50),
        ),
        child: cardWidget,
      );
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
                              '${currentIndex + 1} / ${_cards.length}',
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
                                  value: (currentIndex + 1) / _cards.length,
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

                  const SizedBox(height: 24),

                  Center(
                    child: ConstrainedBox(
                      constraints: const BoxConstraints(maxWidth: 800),
                      child: _buildModeToggle(context, true),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Flashcard
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 24),
                    child: Center(
                      child: ConstrainedBox(
                        constraints: const BoxConstraints(maxWidth: 800),
                        child: cardWidget,
                      ),
                    ),
                  ),

                  const SizedBox(height: 24),

                  if (isAnswerRevealed)
                    Column(
                      children: [
                        Text(
                          'How well did you remember this?',
                          style: GoogleFonts.outfit(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
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
          ),
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
    required Color color,
    required Color textColor,
    required Widget child,
    Widget? headerRightWidget,
    Widget? footerWidget,
  }) {
    return Container(
      width: double.infinity,
      constraints: const BoxConstraints(minHeight: 350), // Standard high height
      decoration: BoxDecoration(
        color: color,
        border: Border.all(color: const Color(0xFFCBD5E1), width: 1.5),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.06),
            blurRadius: 16,
            offset: const Offset(0, 4),
          )
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            // Card Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  label,
                  style: GoogleFonts.outfit(
                    color: const Color(0xFF64748B),
                    fontSize: 12,
                    letterSpacing: 1.5,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                if (headerRightWidget != null) headerRightWidget,
              ],
            ),
            const SizedBox(height: 12),
            const Divider(color: Color(0xFFE2E8F0), height: 1),
            
            // Card Content
            Expanded(
              child: SingleChildScrollView(
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 20),
                  child: child,
                ),
              ),
            ),
            
            const Divider(color: Color(0xFFE2E8F0), height: 1),
            const SizedBox(height: 12),
            
            // Card Footer
            if (footerWidget != null) footerWidget,
          ],
        ),
      ),
    );
  }

  Widget _buildModeToggle(BuildContext context, bool isStudyMode) {
    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFFE2E8F0),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          Expanded(
            child: InkWell(
              onTap: () {
                if (isStudyMode) {
                  context.go('/flashcards/${widget.setId}');
                }
              },
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(8),
                bottomLeft: Radius.circular(8),
              ),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 10),
                decoration: BoxDecoration(
                  color: !isStudyMode ? const Color(0xFF3B82F6) : Colors.transparent,
                  borderRadius: const BorderRadius.only(
                    topLeft: Radius.circular(8),
                    bottomLeft: Radius.circular(8),
                  ),
                ),
                child: Text(
                  'Read Mode',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.outfit(
                    fontWeight: FontWeight.bold,
                    fontSize: 13,
                    color: !isStudyMode ? Colors.white : const Color(0xFF64748B),
                  ),
                ),
              ),
            ),
          ),
          Expanded(
            child: InkWell(
              onTap: () {
                if (!isStudyMode) {
                  context.go('/flashcards/${widget.setId}/study');
                }
              },
              borderRadius: const BorderRadius.only(
                topRight: Radius.circular(8),
                bottomRight: Radius.circular(8),
              ),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 10),
                decoration: BoxDecoration(
                  color: isStudyMode ? const Color(0xFF3B82F6) : Colors.transparent,
                  borderRadius: const BorderRadius.only(
                    topRight: Radius.circular(8),
                    bottomRight: Radius.circular(8),
                  ),
                ),
                child: Text(
                  'Review Mode',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.outfit(
                    fontWeight: FontWeight.bold,
                    fontSize: 13,
                    color: isStudyMode ? Colors.white : const Color(0xFF64748B),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

