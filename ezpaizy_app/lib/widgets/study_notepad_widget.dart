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

    return Card(
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
                        color: Color(0xFF64748B),
                      ),
                    ),
                    const SizedBox(width: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.grey.shade100,
                        borderRadius: BorderRadius.circular(4),
                        border: Border.all(color: Colors.grey.shade300),
                      ),
                      child: Text(
                        widget.topic.toUpperCase(),
                        style: const TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF1E293B),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),

                // Note Title field
                const Text(
                  'NOTE TITLE',
                  style: TextStyle(
                    fontSize: 9,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF64748B),
                  ),
                ),
                const SizedBox(height: 4),
                TextField(
                  controller: _titleController,
                  onChanged: (_) => _onFieldsChanged(),
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                  decoration: InputDecoration(
                    hintText: 'Title of your note...',
                    contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                    isDense: true,
                  ),
                ),
                const SizedBox(height: 12),

                // Acronyms & Notes content field
                const Text(
                  'ACRONYMS & NOTES',
                  style: TextStyle(
                    fontSize: 9,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF64748B),
                  ),
                ),
                const SizedBox(height: 4),
                TextField(
                  controller: _contentController,
                  onChanged: (_) => _onFieldsChanged(),
                  maxLines: 8,
                  minLines: 5,
                  style: const TextStyle(fontSize: 13, color: Color(0xFF334155), height: 1.5),
                  decoration: InputDecoration(
                    hintText: 'Write your study acronyms, summaries, and key points here...',
                    contentPadding: const EdgeInsets.all(12),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                    fillColor: const Color(0xFFFCFDFD),
                    filled: true,
                  ),
                ),
                const SizedBox(height: 14),

                // Manual Save note button
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
    );
  }
}
