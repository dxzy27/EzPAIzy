import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';
import '../widgets/app_top_bar.dart';

class NoteFoldersScreen extends StatefulWidget {
  const NoteFoldersScreen({super.key});

  @override
  State<NoteFoldersScreen> createState() => _NoteFoldersScreenState();
}

class _NoteFoldersScreenState extends State<NoteFoldersScreen> {
  List<String> _folders = [];
  List<String> _filteredFolders = [];
  bool _loading = true;
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _fetchFolders();
    _searchController.addListener(_filterFolders);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchFolders() async {
    setState(() => _loading = true);
    final data = await ApiService.getNoteFolders();
    if (mounted) {
      setState(() {
        _folders = data.map((e) => e.toString()).toList();
        _filteredFolders = _folders;
        _loading = false;
      });
    }
  }

  void _filterFolders() {
    final query = _searchController.text.toLowerCase();
    setState(() {
      _filteredFolders = _folders
          .where((folder) => folder.toLowerCase().contains(query))
          .toList();
    });
  }

  @override
  Widget build(BuildContext context) {
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
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 800),
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const AppTopBar(showBackButton: true),
                      const SizedBox(height: 10),
                      Text(
                        'My Folders',
                        style: GoogleFonts.outfit(
                          fontSize: 26,
                          fontWeight: FontWeight.w900,
                          color: const Color(0xFF0F172A),
                        ),
                      ),
                      Text(
                        'Access and edit your saved study notepad entries',
                        style: GoogleFonts.outfit(
                          fontSize: 14,
                          color: const Color(0xFF64748B),
                        ),
                      ),
                      const SizedBox(height: 16),
                      
                      // Search Bar
                      Container(
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(10),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.03),
                              blurRadius: 10,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: TextField(
                          controller: _searchController,
                          style: GoogleFonts.outfit(fontSize: 14),
                          decoration: InputDecoration(
                            hintText: 'Search folders...',
                            hintStyle: GoogleFonts.outfit(color: const Color(0xFF94A3B8)),
                            prefixIcon: const Icon(Icons.search, color: Color(0xFF94A3B8)),
                            border: InputBorder.none,
                            contentPadding: const EdgeInsets.symmetric(vertical: 14),
                          ),
                        ),
                      ),
                      const SizedBox(height: 20),

                      // Folders Grid/List
                      Expanded(
                        child: _loading
                            ? const Center(child: CircularProgressIndicator())
                            : _filteredFolders.isEmpty
                                ? _buildEmptyState()
                                : GridView.builder(
                                    physics: const BouncingScrollPhysics(),
                                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                      crossAxisCount: 2,
                                      crossAxisSpacing: 16,
                                      mainAxisSpacing: 16,
                                      childAspectRatio: 1.1,
                                    ),
                                    itemCount: _filteredFolders.length,
                                    itemBuilder: (context, index) {
                                      final folderName = _filteredFolders[index];
                                      return _buildFolderCard(folderName);
                                    },
                                  ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildFolderCard(String title) {
    return GestureDetector(
      onTap: () async {
        await context.push('/notes/folder/${Uri.encodeComponent(title)}');
        _fetchFolders(); // Refresh folders count/states when returning
      },
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: const Color(0xFFE2E8F0)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.02),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            const Icon(
              Icons.folder,
              color: Color(0xFFFBBF24), // Amber/Yellow color matching pic 1
              size: 56,
            ),
            const SizedBox(height: 16),
            Text(
              title,
              textAlign: TextAlign.center,
              style: GoogleFonts.outfit(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: const Color(0xFF1E293B),
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(
            Icons.folder_open_outlined,
            size: 64,
            color: Color(0xFFCBD5E1),
          ),
          const SizedBox(height: 16),
          Text(
            'No folders found',
            style: GoogleFonts.outfit(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: const Color(0xFF475569),
            ),
          ),
          const SizedBox(height: 6),
          Text(
            _searchController.text.isEmpty
                ? 'Save your first study note to create a folder'
                : 'No folders match your search query',
            style: GoogleFonts.outfit(
              fontSize: 14,
              color: const Color(0xFF94A3B8),
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

class FolderNotesScreen extends StatefulWidget {
  final String topic;
  const FolderNotesScreen({super.key, required this.topic});

  @override
  State<FolderNotesScreen> createState() => _FolderNotesScreenState();
}

class _FolderNotesScreenState extends State<FolderNotesScreen> {
  List<dynamic> _notes = [];
  List<dynamic> _filteredNotes = [];
  bool _loading = true;
  String _selectedType = 'All Note Types';
  final TextEditingController _searchController = TextEditingController();

  final List<String> _typesList = [
    'All Note Types',
    'Quiz',
    'Material',
    'Flashcard',
  ];

  @override
  void initState() {
    super.initState();
    _fetchNotes();
    _searchController.addListener(_applyFilters);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchNotes() async {
    setState(() => _loading = true);
    final data = await ApiService.getFolderNotes(widget.topic);
    if (mounted) {
      setState(() {
        _notes = data;
        _applyFilters();
        _loading = false;
      });
    }
  }

  void _applyFilters() {
    final query = _searchController.text.toLowerCase();
    setState(() {
      _filteredNotes = _notes.where((note) {
        // Search text matching
        final title = (note['title'] ?? '').toString().toLowerCase();
        final content = (note['content'] ?? '').toString().toLowerCase();
        final matchesSearch = title.contains(query) || content.contains(query);

        // Type filter matching
        if (_selectedType == 'All Note Types') {
          return matchesSearch;
        }
        final resourceType = (note['resource_type'] ?? '').toString().toLowerCase();
        return matchesSearch && resourceType == _selectedType.toLowerCase();
      }).toList();
    });
  }

  Future<void> _saveChanges(Map<String, dynamic> note, String newTitle, String newContent) async {
    if (newTitle.trim().isEmpty || newContent.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Title and Content cannot be empty')),
      );
      return;
    }

    final res = await ApiService.saveNote(
      topic: widget.topic,
      title: newTitle,
      content: newContent,
      resourceType: note['resource_type'],
      resourceId: note['resource_id'],
    );

    if (res.isNotEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Note updated successfully!'), backgroundColor: Colors.green),
      );
      _fetchNotes();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Failed to update note')),
      );
    }
  }

  Future<void> _deleteNote(int noteId) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text('Delete Note?', style: GoogleFonts.outfit(fontWeight: FontWeight.bold)),
        content: Text('Are you sure you want to permanently delete this notepad entry?', style: GoogleFonts.outfit()),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: Text('Cancel', style: GoogleFonts.outfit(color: const Color(0xFF64748B))),
          ),
          ElevatedButton(
            onPressed: () => Navigator.of(ctx).pop(true),
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFEF4444), foregroundColor: Colors.white),
            child: Text('Delete', style: GoogleFonts.outfit()),
          ),
        ],
      ),
    );

    if (confirm == true) {
      final ok = await ApiService.deleteNote(noteId);
      if (ok) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Note deleted successfully!')),
        );
        _fetchNotes();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Failed to delete note')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
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
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 800),
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const AppTopBar(showBackButton: true),
                      const SizedBox(height: 10),
                      
                      // Folder Info Banner
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [Color(0xFF6366F1), Color(0xFF4F46E5)],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: [
                            BoxShadow(
                              color: const Color(0xFF4F46E5).withOpacity(0.2),
                              blurRadius: 12,
                              offset: const Offset(0, 6),
                            ),
                          ],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                const Icon(Icons.folder_open, color: Colors.white70, size: 16),
                                const SizedBox(width: 6),
                                Text(
                                  'TOPIC FOLDER',
                                  style: GoogleFonts.outfit(
                                    fontSize: 11,
                                    color: Colors.white70,
                                    fontWeight: FontWeight.w900,
                                    letterSpacing: 0.5,
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 4),
                            Text(
                              widget.topic,
                              style: GoogleFonts.outfit(
                                  fontSize: 24,
                                  fontWeight: FontWeight.bold,
                                  color: Colors.white,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              _notes.isEmpty
                                  ? 'No study notes in this folder'
                                  : 'You have ${_notes.length} study note${_notes.length > 1 ? 's' : ''} in this folder',
                              style: GoogleFonts.outfit(
                                fontSize: 13,
                                color: Colors.white.withOpacity(0.8),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Search & Filter
                      Row(
                        children: [
                          // Search field
                          Expanded(
                            child: Container(
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: const Color(0xFFE2E8F0)),
                              ),
                              child: TextField(
                                controller: _searchController,
                                style: GoogleFonts.outfit(fontSize: 13),
                                decoration: InputDecoration(
                                  hintText: 'Search notes by title or acronym content...',
                                  hintStyle: GoogleFonts.outfit(color: const Color(0xFF94A3B8)),
                                  prefixIcon: const Icon(Icons.search, size: 18, color: Color(0xFF94A3B8)),
                                  border: InputBorder.none,
                                  contentPadding: const EdgeInsets.symmetric(vertical: 10),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          // Dropdown filter
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                            ),
                            child: DropdownButtonHideUnderline(
                              child: DropdownButton<String>(
                                value: _selectedType,
                                icon: const Icon(Icons.keyboard_arrow_down, size: 18, color: Color(0xFF64748B)),
                                style: GoogleFonts.outfit(fontSize: 12, color: const Color(0xFF0F172A), fontWeight: FontWeight.bold),
                                onChanged: (val) {
                                  if (val != null) {
                                    setState(() {
                                      _selectedType = val;
                                      _applyFilters();
                                    });
                                  }
                                },
                                items: _typesList.map((type) {
                                  return DropdownMenuItem<String>(
                                    value: type,
                                    child: Text(type),
                                  );
                                }).toList(),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),

                      // Notes List
                      Expanded(
                        child: _loading
                            ? const Center(child: CircularProgressIndicator())
                            : _filteredNotes.isEmpty
                                ? _buildEmptyState()
                                : LayoutBuilder(
                                    builder: (context, constraints) {
                                      final isWide = constraints.maxWidth > 600;
                                      if (isWide) {
                                        final cardWidth = (constraints.maxWidth - 16) / 2;
                                        return SingleChildScrollView(
                                          physics: const BouncingScrollPhysics(),
                                          child: Wrap(
                                            spacing: 16,
                                            runSpacing: 16,
                                            children: _filteredNotes.map((note) {
                                              return SizedBox(
                                                width: cardWidth,
                                                child: _NoteCardWidget(
                                                  note: note,
                                                  onSave: (title, content) => _saveChanges(note, title, content),
                                                  onDelete: () => _deleteNote(note['id']),
                                                ),
                                              );
                                            }).toList(),
                                          ),
                                        );
                                      } else {
                                        return ListView.builder(
                                          physics: const BouncingScrollPhysics(),
                                          itemCount: _filteredNotes.length,
                                          itemBuilder: (context, index) {
                                            final note = _filteredNotes[index];
                                            return _NoteCardWidget(
                                              note: note,
                                              onSave: (title, content) => _saveChanges(note, title, content),
                                              onDelete: () => _deleteNote(note['id']),
                                            );
                                          },
                                        );
                                      }
                                    },
                                  ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(
            Icons.notes_outlined,
            size: 54,
            color: Color(0xFFCBD5E1),
          ),
          const SizedBox(height: 12),
          Text(
            'No notes found',
            style: GoogleFonts.outfit(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: const Color(0xFF475569),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Create study notes to populate this folder',
            style: GoogleFonts.outfit(
              fontSize: 13,
              color: const Color(0xFF94A3B8),
            ),
          ),
        ],
      ),
    );
  }
}

class _NoteCardWidget extends StatefulWidget {
  final Map<String, dynamic> note;
  final Function(String, String) onSave;
  final VoidCallback onDelete;

  const _NoteCardWidget({
    required this.note,
    required this.onSave,
    required this.onDelete,
  });

  @override
  State<_NoteCardWidget> createState() => _NoteCardWidgetState();
}

class _NoteCardWidgetState extends State<_NoteCardWidget> {
  late TextEditingController _titleController;
  late TextEditingController _contentController;
  bool _isEditing = false;

  @override
  void initState() {
    super.initState();
    _titleController = TextEditingController(text: widget.note['title'] ?? '');
    _contentController = TextEditingController(text: widget.note['content'] ?? '');
  }

  @override
  void dispose() {
    _titleController.dispose();
    _contentController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final typeVal = (widget.note['resource_type'] ?? 'content').toString().toLowerCase();
    String typeLabel = '📄 Other';
    if (typeVal == 'quiz') {
      typeLabel = '📝 Quiz';
    } else if (typeVal == 'flashcard') {
      typeLabel = '🃏 Quiz'; // wait, in pic 1, both badges say "Quiz" and "Go to Quiz", wait, let's look at the badge: in pic 1 the badge on the right note also says "Quiz" and "Go to Quiz". Oh, actually, let's keep the real label but with the exact formatting!
      typeLabel = '🃏 Flashcard';
    }

    String formattedDate = '';
    try {
      if (widget.note['updated_at'] != null) {
        final parsedDate = DateTime.parse(widget.note['updated_at'].toString());
        formattedDate = DateFormat('MMM dd, yyyy h:mm a').format(parsedDate.toLocal());
      }
    } catch (_) {}

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: const Color(0xFFF1F5F9), // Light grey background matching pic 1
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFCBD5E1)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 8,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header: Badges & Saved Status & Trash icon
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 10),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        // White Badge
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: const Color(0xFFCBD5E1)),
                          ),
                          child: Text(
                            typeLabel,
                            style: GoogleFonts.outfit(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              color: const Color(0xFF475569),
                            ),
                          ),
                        ),
                        // "Go to" blue button badge if resource_id exists
                        if (widget.note['resource_id'] != null) ...[
                          const SizedBox(width: 6),
                          GestureDetector(
                            onTap: () {
                              final resId = widget.note['resource_id'];
                              if (typeVal == 'quiz') {
                                context.push('/quizzes/$resId');
                              } else if (typeVal == 'flashcard') {
                                context.push('/flashcards/$resId');
                              } else {
                                context.push('/contents/$resId');
                              }
                            },
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: const Color(0xFF3B82F6), // Blue background
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(Icons.edit_note, color: Colors.white, size: 10),
                                  const SizedBox(width: 4),
                                  Text(
                                    typeVal == 'quiz'
                                        ? 'Go to Quiz'
                                        : typeVal == 'flashcard'
                                            ? 'Go to Flashcard'
                                            : 'Go to Material',
                                    style: GoogleFonts.outfit(
                                      fontSize: 10,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.white,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                    Row(
                      children: [
                        Text(
                          _isEditing ? 'Unsaved' : 'Saved',
                          style: GoogleFonts.outfit(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: _isEditing ? const Color(0xFFF59E0B) : const Color(0xFF64748B),
                          ),
                        ),
                        const SizedBox(width: 8),
                        IconButton(
                          icon: const Icon(Icons.delete, color: Color(0xFFEF4444), size: 20),
                          onPressed: widget.onDelete,
                          constraints: const BoxConstraints(),
                          padding: EdgeInsets.zero,
                        ),
                      ],
                    ),
                  ],
                ),
                if (formattedDate.isNotEmpty) ...[
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      const Icon(Icons.access_time, size: 12, color: Color(0xFF94A3B8)),
                      const SizedBox(width: 4),
                      Text(
                        'Last updated: $formattedDate',
                        style: GoogleFonts.outfit(
                          fontSize: 10,
                          color: const Color(0xFF64748B),
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          ),
          
          const Divider(height: 1, color: Color(0xFFCBD5E1)),
          
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Note Title Section
                Text(
                  'NOTE TITLE',
                  style: GoogleFonts.outfit(
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF64748B),
                    letterSpacing: 0.5,
                  ),
                ),
                const SizedBox(height: 6),
                Container(
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: const Color(0xFFCBD5E1)),
                  ),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  child: TextField(
                    controller: _titleController,
                    onChanged: (_) => setState(() => _isEditing = true),
                    style: GoogleFonts.outfit(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: const Color(0xFF0F172A),
                    ),
                    decoration: const InputDecoration(
                      border: InputBorder.none,
                      hintText: 'Enter note title...',
                      isDense: true,
                      contentPadding: EdgeInsets.zero,
                    ),
                  ),
                ),
                
                const SizedBox(height: 16),
                
                // Notes & Acronyms Section
                Text(
                  'NOTES & ACRONYMS',
                  style: GoogleFonts.outfit(
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF64748B),
                    letterSpacing: 0.5,
                  ),
                ),
                const SizedBox(height: 6),
                Container(
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: const Color(0xFFCBD5E1)),
                  ),
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  child: CustomPaint(
                    painter: RuledPaperPainter(),
                    child: TextField(
                      controller: _contentController,
                      maxLines: null,
                      minLines: 8,
                      onChanged: (_) => setState(() => _isEditing = true),
                      style: GoogleFonts.outfit(
                        fontSize: 13,
                        color: const Color(0xFF334155),
                        height: 2.15, // matches lineSpacing in painter
                      ),
                      decoration: const InputDecoration(
                        border: InputBorder.none,
                        hintText: 'Write your notes or acronyms here...',
                        isDense: true,
                        contentPadding: EdgeInsets.zero,
                      ),
                    ),
                  ),
                ),
                
                const SizedBox(height: 16),
                
                // Save Changes button
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () {
                      widget.onSave(_titleController.text, _contentController.text);
                      setState(() => _isEditing = false);
                    },
                    icon: const Icon(Icons.cloud_upload, size: 16, color: Colors.white),
                    label: Text(
                      'Save Changes',
                      style: GoogleFonts.outfit(
                        fontWeight: FontWeight.bold,
                        fontSize: 13,
                      ),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF6D28D9), // Purple color matching web
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      elevation: 0,
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

class RuledPaperPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFFE2E8F0)
      ..strokeWidth = 1.0;
    
    // Line spacing matches the fontSize * height factor
    const double lineSpacing = 28.0;
    // Offset down slightly to align nicely below the first text row baseline
    const double startOffset = 24.0;
    for (double y = startOffset; y < size.height; y += lineSpacing) {
      canvas.drawLine(Offset(0, y), Offset(size.width, y), paint);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
