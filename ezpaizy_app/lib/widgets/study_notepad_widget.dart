import 'dart:async';
import 'package:flutter/material.dart';
import '../services/api_service.dart';

class StudyNotepadWidget extends StatefulWidget {
  final String resourceType; // 'content', 'quiz', 'flashcard'
  final int resourceId;
  final String topic;
  final String defaultTitle;

  const StudyNotepadWidget({
    super.key,
    required this.resourceType,
    required this.resourceId,
    required this.topic,
    required this.defaultTitle,
  });

  @override
  State<StudyNotepadWidget> createState() => _StudyNotepadWidgetState();
}

class _StudyNotepadWidgetState extends State<StudyNotepadWidget> {
  final _titleController = TextEditingController();
  final _contentController = TextEditingController();
  
  // ignore: unused_field
  int? _noteId;
  bool _loading = true;
  String _saveStatus = 'Auto-saved';
  Color _statusColor = Colors.white70;
  Timer? _debounceTimer;

  @override
  void initState() {
    super.initState();
    _loadNote();
  }

  @override
  void dispose() {
    _debounceTimer?.cancel();
    _titleController.dispose();
    _contentController.dispose();
    super.dispose();
  }

  Future<void> _loadNote() async {
    try {
      final res = await ApiService.getResourceNote(widget.resourceType, widget.resourceId);
      if (res != null) {
        setState(() {
          _noteId = res['id'];
          _titleController.text = res['title'] ?? '';
          _contentController.text = res['content'] ?? '';
          _loading = false;
        });
      } else {
        setState(() {
          _titleController.text = widget.defaultTitle;
          _contentController.text = '';
          _loading = false;
        });
      }
    } catch (_) {
      setState(() {
        _loading = false;
      });
    }
  }

  void _onFieldsChanged() {
    setState(() {
      _saveStatus = 'Unsaved changes';
      _statusColor = Colors.amber.shade200;
    });
    _debounceTimer?.cancel();
    _debounceTimer = Timer(const Duration(milliseconds: 1500), _saveNote);
  }

  Future<void> _saveNote() async {
    final title = _titleController.text.trim();
    final content = _contentController.text.trim();
    if (title.isEmpty) return;

    setState(() {
      _saveStatus = 'Saving...';
      _statusColor = Colors.white70;
    });

    try {
      final res = await ApiService.saveNote(
        topic: widget.topic,
        title: title,
        content: content,
        resourceType: widget.resourceType,
        resourceId: widget.resourceId,
      );

      if (res['success'] == true) {
        setState(() {
          _saveStatus = 'Auto-saved';
          _statusColor = Colors.white70;
          if (res['note'] != null) {
            _noteId = res['note']['id'];
          }
        });
      } else {
        setState(() {
          _saveStatus = 'Save failed';
          _statusColor = Colors.red.shade200;
        });
      }
    } catch (_) {
      setState(() {
        _saveStatus = 'Connection error';
        _statusColor = Colors.red.shade200;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const SizedBox(
        height: 150,
        child: Center(child: CircularProgressIndicator(color: Colors.green)),
      );
    }

    return Center(
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 800),
        child: Padding(
          padding: const EdgeInsets.only(top: 32, bottom: 24, left: 16, right: 16),
          child: Card(
            elevation: 3,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            clipBehavior: Clip.antiAlias,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Header (bg-success green)
                Container(
                  color: const Color(0xFF198754), // Bootstrap bg-success
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.edit_note, color: Colors.white, size: 20),
                          SizedBox(width: 6),
                          Text(
                            'Study Notepad',
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                            ),
                          ),
                        ],
                      ),
                      Text(
                        _saveStatus,
                        style: TextStyle(
                          color: _statusColor,
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
                
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Topic row
                      Row(
                        children: [
                          const Text(
                            'TOPIC: ',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              color: Colors.grey,
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF1F5F9),
                              borderRadius: BorderRadius.circular(4),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                            ),
                            child: Text(
                              widget.topic.toUpperCase(),
                              style: const TextStyle(
                                fontSize: 9,
                                fontWeight: FontWeight.bold,
                                color: Color(0xFF334155),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),

                      // Title input
                      const Text(
                        'NOTE TITLE',
                        style: TextStyle(
                          fontSize: 9,
                          fontWeight: FontWeight.bold,
                          color: Colors.grey,
                          letterSpacing: 0.5,
                        ),
                      ),
                      const SizedBox(height: 4),
                      TextField(
                        controller: _titleController,
                        onChanged: (_) => _onFieldsChanged(),
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                        ),
                        decoration: InputDecoration(
                          hintText: 'Title of your note...',
                          contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(6),
                            borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),

                      // Content input
                      const Text(
                        'ACRONYMS & NOTES',
                        style: TextStyle(
                          fontSize: 9,
                          fontWeight: FontWeight.bold,
                          color: Colors.grey,
                          letterSpacing: 0.5,
                        ),
                      ),
                      const SizedBox(height: 4),
                      TextField(
                        controller: _contentController,
                        maxLines: 6,
                        onChanged: (_) => _onFieldsChanged(),
                        style: const TextStyle(fontSize: 12, height: 1.4),
                        decoration: InputDecoration(
                          hintText: 'Write your study acronyms, summaries, and key points here...',
                          contentPadding: const EdgeInsets.all(10),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(6),
                            borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),

                      // Save button
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton.icon(
                          onPressed: _saveNote,
                          icon: const Icon(Icons.cloud_upload, size: 16),
                          label: const Text('Save Note', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF198754),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 10),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
