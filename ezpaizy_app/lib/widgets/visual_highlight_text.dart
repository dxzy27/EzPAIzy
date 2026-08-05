import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

enum VisualStyleType {
  none,
  highlight,
  underline,
}

class VisualHighlight {
  final int start;
  final int end;
  final VisualStyleType type;

  VisualHighlight({
    required this.start,
    required this.end,
    required this.type,
  });

  Map<String, dynamic> toJson() => {
        'start': start,
        'end': end,
        'type': type.name,
      };

  factory VisualHighlight.fromJson(Map<String, dynamic> json) => VisualHighlight(
        start: json['start'] as int,
        end: json['end'] as int,
        type: VisualStyleType.values.byName(json['type'] as String),
      );
}

class VisualHighlightText extends StatefulWidget {
  final String text;
  final String storageKey;
  final TextStyle style;
  final TextAlign textAlign;

  const VisualHighlightText({
    super.key,
    required this.text,
    required this.storageKey,
    required this.style,
    this.textAlign = TextAlign.center,
  });

  @override
  State<VisualHighlightText> createState() => _VisualHighlightTextState();
}

class _VisualHighlightTextState extends State<VisualHighlightText> {
  List<VisualHighlight> _highlights = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadHighlights();
  }

  @override
  void didUpdateWidget(covariant VisualHighlightText oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.storageKey != widget.storageKey || oldWidget.text != widget.text) {
      _loadHighlights();
    }
  }

  Future<void> _loadHighlights() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final data = prefs.getString(widget.storageKey);
      if (data != null) {
        final List<dynamic> decoded = jsonDecode(data);
        setState(() {
          _highlights = decoded
              .map((item) => VisualHighlight.fromJson(item as Map<String, dynamic>))
              .toList();
          _loading = false;
        });
      } else {
        setState(() {
          _highlights = [];
          _loading = false;
        });
      }
    } catch (_) {
      setState(() => _loading = false);
    }
  }

  Future<void> _saveHighlights() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final encoded = jsonEncode(_highlights.map((h) => h.toJson()).toList());
      await prefs.setString(widget.storageKey, encoded);
    } catch (_) {}
  }

  void _applyStyle(int start, int end, VisualStyleType type) {
    if (start < 0 || end > widget.text.length || start >= end) return;
    
    setState(() {
      // Remove any existing highlights that overlap with this range
      _highlights.removeWhere((h) =>
          (start >= h.start && start < h.end) ||
          (end > h.start && end <= h.end) ||
          (h.start >= start && h.end <= end));

      if (type != VisualStyleType.none) {
        _highlights.add(VisualHighlight(start: start, end: end, type: type));
      }
    });
    _saveHighlights();
  }

  void _clearStyle(int start, int end) {
    _applyStyle(start, end, VisualStyleType.none);
  }

  List<TextSpan> _buildTextSpans() {
    if (widget.text.isEmpty) return [];
    
    // Character-by-character style mapping to resolve overlaps cleanly
    final charStyles = List<VisualStyleType>.filled(widget.text.length, VisualStyleType.none);
    
    for (final h in _highlights) {
      final actualStart = h.start.clamp(0, widget.text.length);
      final actualEnd = h.end.clamp(0, widget.text.length);
      for (int i = actualStart; i < actualEnd; i++) {
        charStyles[i] = h.type;
      }
    }

    final List<TextSpan> spans = [];
    int start = 0;
    
    while (start < widget.text.length) {
      final type = charStyles[start];
      int end = start;
      while (end < widget.text.length && charStyles[end] == type) {
        end++;
      }
      
      final chunkText = widget.text.substring(start, end);
      TextStyle chunkStyle = widget.style;
      
      if (type == VisualStyleType.highlight) {
        chunkStyle = chunkStyle.copyWith(
          backgroundColor: Colors.yellow.withOpacity(0.4),
        );
      } else if (type == VisualStyleType.underline) {
        chunkStyle = chunkStyle.copyWith(
          decoration: TextDecoration.underline,
          decorationColor: Colors.blue,
          decorationThickness: 2.0,
        );
      }

      spans.add(TextSpan(text: chunkText, style: chunkStyle));
      start = end;
    }
    
    return spans;
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return Text(widget.text, style: widget.style, textAlign: widget.textAlign);
    }

    final spans = _buildTextSpans();
    
    return SelectableText.rich(
      TextSpan(children: spans),
      textAlign: widget.textAlign,
      contextMenuBuilder: (context, editableTextState) {
        final selection = editableTextState.textEditingValue.selection;
        final isSelected = !selection.isCollapsed;

        final buttonItems = editableTextState.contextMenuButtonItems;

        if (isSelected) {
          // Add custom highlighting button
          buttonItems.add(
            ContextMenuButtonItem(
              label: 'Highlight 🖍️',
              onPressed: () {
                _applyStyle(selection.start, selection.end, VisualStyleType.highlight);
                editableTextState.hideToolbar();
              },
            ),
          );

          // Add custom underline button
          buttonItems.add(
            ContextMenuButtonItem(
              label: 'Underline ➖',
              onPressed: () {
                _applyStyle(selection.start, selection.end, VisualStyleType.underline);
                editableTextState.hideToolbar();
              },
            ),
          );

          // Add custom clear button
          buttonItems.add(
            ContextMenuButtonItem(
              label: 'Clear 🧼',
              onPressed: () {
                _clearStyle(selection.start, selection.end);
                editableTextState.hideToolbar();
              },
            ),
          );
        }

        return AdaptiveTextSelectionToolbar.buttonItems(
          anchors: editableTextState.contextMenuAnchors,
          buttonItems: buttonItems,
        );
      },
    );
  }
}
