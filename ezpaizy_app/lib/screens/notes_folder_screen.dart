import 'dart:async';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../services/api_service.dart';
import '../app/theme.dart';

class NotesFolderScreen extends StatefulWidget {
  final String topic;
  const NotesFolderScreen({super.key, required this.topic});

  @override
  State<NotesFolderScreen> createState() => _NotesFolderScreenState();
}

class _NotesFolderScreenState extends State<NotesFolderScreen> {
  List<dynamic> _notes = [];
  bool _loading = true;
  String _searchQuery = '';
  String _typeFilter = ''; // '', 'quiz', 'flashcard', 'content'

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
    });
    try {
      final res = await ApiService.getFolderNotes(widget.topic);
      setState(() {
        _notes = res;
        _loading = false;
      });
    } catch (_) {
      setState(() {
        _loading = false;
      });
    }
  }

  List<dynamic> get _filteredNotes {
    return _notes.where((note) {
      final title = (note['title'] ?? '').toString().toLowerCase();
      final content = (note['content'] ?? '').toString().toLowerCase();
      final type = (note['resource_type'] ?? 'content').toString();

      final matchesQuery = _searchQuery.isEmpty ||
          title.contains(_searchQuery) ||
          content.contains(_searchQuery);

      final matchesType = _typeFilter.isEmpty || type == _typeFilter;

      return matchesQuery && matchesType;
    }).toList();
  }

  Future<void> _deleteNote(int id) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Delete Note'),
        content: const Text('Are you sure you want to delete this study note?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Delete'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    try {
      final success = await ApiService.deleteNote(id);
      if (success) {
        _load();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Note deleted successfully'), backgroundColor: Colors.red),
          );
        }
      }
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        title: Text(
          widget.topic,
          style: const TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 16),
        ),
        elevation: 0,
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Color(0xFF64748B)),
          onPressed: () => context.go('/dashboard'),
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                // Banner
                Container(
                  width: double.infinity,
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(
                      colors: [Color(0xFF6D28D9), Color(0xFF4F46E5)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                  ),
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.2),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Text(
                          '📂 TOPIC FOLDER',
                          style: TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold),
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        widget.topic,
                        style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'You have ${_notes.length} study note${_notes.length != 1 ? 's' : ''} in this folder',
                        style: TextStyle(color: Colors.white.withOpacity(0.85), fontSize: 13),
                      ),
                    ],
                  ),
                ),

                // Filters Card
                if (_notes.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.all(12),
                    child: Card(
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      elevation: 1,
                      child: Padding(
                        padding: const EdgeInsets.all(12),
                        child: Column(
                          children: [
                            TextField(
                              onChanged: (val) {
                                setState(() {
                                  _searchQuery = val.toLowerCase().trim();
                                });
                              },
                              style: const TextStyle(fontSize: 13),
                              decoration: InputDecoration(
                                hintText: 'Search notes by title or content...',
                                prefixIcon: const Icon(Icons.search, size: 18),
                                contentPadding: const EdgeInsets.symmetric(vertical: 8),
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                                isDense: true,
                              ),
                            ),
                            const SizedBox(height: 8),
                            DropdownButtonFormField<String>(
                              initialValue: _typeFilter,
                              onChanged: (val) {
                                setState(() {
                                  _typeFilter = val ?? '';
                                });
                              },
                              style: const TextStyle(fontSize: 13, color: Colors.black87),
                              decoration: InputDecoration(
                                prefixIcon: const Icon(Icons.filter_list, size: 18),
                                contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                                isDense: true,
                              ),
                              items: const [
                                DropdownMenuItem(value: '', child: Text('All Note Types')),
                                DropdownMenuItem(value: 'quiz', child: Text('📝 Quizzes')),
                                DropdownMenuItem(value: 'flashcard', child: Text('🃏 Flashcards')),
                                DropdownMenuItem(value: 'content', child: Text('📄 Other Materials')),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),

                Expanded(
                  child: _notes.isEmpty
                      ? _buildEmptyState()
                      : _filteredNotes.isEmpty
                          ? _buildNoResultsState()
                          : ListView.builder(
                              padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                              itemCount: _filteredNotes.length,
                              itemBuilder: (context, idx) {
                                return _NoteCard(
                                  note: _filteredNotes[idx],
                                  onSave: _load,
                                  onDelete: () => _deleteNote(_filteredNotes[idx]['id']),
                                );
                              },
                            ),
                ),
              ],
            ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(30),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.folder_open_outlined, size: 64, color: Colors.grey),
            const SizedBox(height: 16),
            const Text(
              'This folder is empty',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
            ),
            const SizedBox(height: 6),
            const Text(
              "You haven't saved any notes for this topic yet. Go to your learning materials or quizzes and write some notes!",
              textAlign: TextAlign.center,
              style: TextStyle(color: Color(0xFF64748B), fontSize: 13),
            ),
            const SizedBox(height: 20),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                ElevatedButton(
                  onPressed: () => context.go('/contents'),
                  style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primary),
                  child: const Text('Browse Materials'),
                ),
                const SizedBox(width: 8),
                OutlinedButton(
                  onPressed: () => context.go('/quizzes'),
                  child: const Text('Quizzes'),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildNoResultsState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(30),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.search_off_outlined, size: 48, color: Colors.grey),
            const SizedBox(height: 12),
            const Text(
              'No matching notes found',
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
            ),
            const SizedBox(height: 4),
            const Text(
              'Try adjusting your search query or filter selection.',
              style: TextStyle(color: Color(0xFF64748B), fontSize: 13),
            ),
          ],
        ),
      ),
    );
  }
}

class _NoteCard extends StatefulWidget {
  final Map<String, dynamic> note;
  final VoidCallback onSave;
  final VoidCallback onDelete;

  const _NoteCard({
    required this.note,
    required this.onSave,
    required this.onDelete,
  });

  @override
  State<_NoteCard> createState() => _NoteCardState();
}

class _NoteCardState extends State<_NoteCard> {
  final _titleController = TextEditingController();
  final _contentController = TextEditingController();

  String _saveStatus = 'Saved';
  Color _statusColor = Colors.green;
  Timer? _debounceTimer;

  @override
  void initState() {
    super.initState();
    _titleController.text = widget.note['title'] ?? '';
    _contentController.text = widget.note['content'] ?? '';
  }

  @override
  void dispose() {
    _debounceTimer?.cancel();
    _titleController.dispose();
    _contentController.dispose();
    super.dispose();
  }

  void _onFieldsChanged() {
    setState(() {
      _saveStatus = 'Unsaved changes';
      _statusColor = Colors.amber.shade700;
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
      _statusColor = Colors.grey;
    });

    try {
      final res = await ApiService.saveNote(
        topic: widget.note['topic'] ?? 'General',
        title: title,
        content: content,
        resourceType: widget.note['resource_type'],
        resourceId: widget.note['resource_id'],
      );

      if (res['success'] == true) {
        setState(() {
          _saveStatus = 'Saved';
          _statusColor = Colors.green;
        });
        widget.onSave();
      } else {
        setState(() {
          _saveStatus = 'Save failed';
          _statusColor = Colors.red;
        });
      }
    } catch (_) {
      setState(() {
        _saveStatus = 'Connection error';
        _statusColor = Colors.red;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final resType = widget.note['resource_type'] ?? 'content';
    final difficulty = widget.note['difficulty'] as String?;
    final resId = widget.note['resource_id'] as int?;

    String typeLabel = '📄 Other Materials';
    Color typeColor = Colors.blue;
    IconData typeIcon = Icons.article_outlined;

    if (resType == 'quiz') {
      typeLabel = '📝 Quiz';
      typeColor = Colors.orange;
      typeIcon = Icons.quiz_outlined;
    } else if (resType == 'flashcard') {
      typeLabel = '🃏 Flashcards';
      typeColor = Colors.deepPurple;
      typeIcon = Icons.style_outlined;
    }

    return Card(
      elevation: 2,
      margin: const EdgeInsets.only(top: 14),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Card Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Wrap(
                    spacing: 6,
                    runSpacing: 4,
                    crossAxisAlignment: WrapCrossAlignment.center,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: typeColor.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(typeIcon, color: typeColor, size: 12),
                            const SizedBox(width: 4),
                            Text(
                              typeLabel,
                              style: TextStyle(color: typeColor, fontSize: 10, fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                      ),
                      if (difficulty != null)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.grey.shade100,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: Colors.grey.shade300),
                          ),
                          child: Text(
                            difficulty.toUpperCase(),
                            style: TextStyle(
                              color: difficulty == 'easy'
                                  ? Colors.green
                                  : difficulty == 'medium'
                                      ? Colors.orange
                                      : Colors.red,
                              fontSize: 9,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      if (resId != null)
                        InkWell(
                          onTap: () {
                            if (resType == 'quiz') {
                              context.go('/quiz/$resId');
                            } else if (resType == 'flashcard') {
                              context.go('/flashcards/$resId/study');
                            } else {
                              context.go('/contents/$resId');
                            }
                          },
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: AppTheme.primary.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: const Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.arrow_forward_ios, size: 8, color: AppTheme.primary),
                                SizedBox(width: 2),
                                Text(
                                  'Go to resource',
                                  style: TextStyle(color: AppTheme.primary, fontSize: 9, fontWeight: FontWeight.bold),
                                ),
                              ],
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
                
                Row(
                  children: [
                    Text(
                      _saveStatus,
                      style: TextStyle(color: _statusColor, fontSize: 11, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(width: 8),
                    IconButton(
                      icon: const Icon(Icons.delete_outline, color: Colors.red, size: 20),
                      onPressed: widget.onDelete,
                      padding: EdgeInsets.zero,
                      constraints: const BoxConstraints(),
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 12),

            // Note Title Field
            const Text(
              'NOTE TITLE',
              style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
            ),
            const SizedBox(height: 4),
            TextField(
              controller: _titleController,
              onChanged: (_) => _onFieldsChanged(),
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
              decoration: InputDecoration(
                fillColor: const Color(0xFFF8FAFC),
                filled: true,
                contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: BorderSide.none),
                isDense: true,
              ),
            ),
            const SizedBox(height: 12),

            // Note Content Field
            const Text(
              'NOTES & ACRONYMS',
              style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Color(0xFF64748B)),
            ),
            const SizedBox(height: 4),
            TextField(
              controller: _contentController,
              onChanged: (_) => _onFieldsChanged(),
              maxLines: 6,
              minLines: 4,
              style: const TextStyle(fontSize: 13, color: Color(0xFF334155), height: 1.5),
              decoration: InputDecoration(
                fillColor: const Color(0xFFFCFDFD),
                filled: true,
                contentPadding: const EdgeInsets.all(12),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: BorderSide(color: Colors.grey.shade300)),
              ),
            ),
            const SizedBox(height: 12),

            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _saveNote,
                icon: const Icon(Icons.save, size: 14),
                label: const Text('Save Changes', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF6D28D9),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
