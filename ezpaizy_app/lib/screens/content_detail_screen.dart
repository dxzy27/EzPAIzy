import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import '../services/api_service.dart';
import '../providers/auth_provider.dart';
import '../widgets/study_notepad_widget.dart';
import 'package:syncfusion_flutter_pdfviewer/pdfviewer.dart';

class ContentDetailScreen extends StatefulWidget {
  final int contentId;
  const ContentDetailScreen({super.key, required this.contentId});

  @override
  State<ContentDetailScreen> createState() => _ContentDetailScreenState();
}

class _ContentDetailScreenState extends State<ContentDetailScreen> {
  Map<String, dynamic>? content;
  bool loading = true;
  bool _showPreview = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final d = await ApiService.getContentDetail(widget.contentId);
      setState(() { content = d; loading = false; });
    } catch (_) {
      setState(() => loading = false);
    }
  }

  String _formatDate(String? raw) {
    if (raw == null) return '';
    try {
      final dt = DateTime.parse(raw);
      return DateFormat('MMM d, yyyy HH:mm').format(dt);
    } catch (_) {
      return '';
    }
  }

  Future<void> _downloadFile(String filePath) async {
    final urlString = filePath.startsWith('http')
        ? filePath
        : 'https://ezpaizy.app/storage/$filePath';
        
    // Wrap PDF files in Google Docs viewer so they display inside the in-app webview on both Android & iOS
    final viewUrl = urlString.toLowerCase().endsWith('.pdf')
        ? 'https://docs.google.com/gview?embedded=true&url=${Uri.encodeComponent(urlString)}'
        : urlString;

    final uri = Uri.parse(viewUrl);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.inAppBrowserView);
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Could not open file URL')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.read<AuthProvider>();
    final isReadWrite = auth.user?['learning_style'] == 'read_write';
    final dateStr = _formatDate(content?['created_at']);
    final filePath = content?['file_path'] as String?;
    final fileName = (content?['original_filename'] ?? '${content?['title'] ?? "document"}.pdf') as String;

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
            child: loading
                ? const Center(child: CircularProgressIndicator())
                : content == null
                    ? const Center(child: Text('Failed to load content', style: TextStyle(fontFamily: 'Outfit')))
                    : SingleChildScrollView(
                        padding: const EdgeInsets.all(20),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Header Row (Back Button + Title + Subtitle)
                            Row(
                              crossAxisAlignment: CrossAxisAlignment.center,
                              children: [
                                Material(
                                  color: Colors.transparent,
                                  child: InkWell(
                                    borderRadius: BorderRadius.circular(20),
                                    onTap: () => context.pop(),
                                    child: Container(
                                      width: 36,
                                      height: 36,
                                      decoration: BoxDecoration(
                                        shape: BoxShape.circle,
                                        border: Border.all(color: const Color(0xFFCBD5E1)),
                                        color: Colors.white,
                                      ),
                                      child: const Icon(Icons.arrow_back, size: 18, color: Color(0xFF475569)),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        content!['title'] ?? '',
                                        style: const TextStyle(
                                          fontSize: 22,
                                          fontWeight: FontWeight.w800,
                                          color: Color(0xFF1E293B),
                                          fontFamily: 'Outfit',
                                        ),
                                      ),
                                      if (dateStr.isNotEmpty) ...[
                                        const SizedBox(height: 2),
                                        Text(
                                          'Created: $dateStr',
                                          style: const TextStyle(
                                            fontSize: 12,
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
                            const SizedBox(height: 20),

                            // Main White Container Card matching web layout
                            Container(
                              width: double.infinity,
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(color: const Color(0xFFE2E8F0)),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withOpacity(0.03),
                                    blurRadius: 15,
                                    offset: const Offset(0, 5),
                                  ),
                                ],
                              ),
                              padding: const EdgeInsets.all(24),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  // Section Header: Content
                                  const Text(
                                    'Content',
                                    style: TextStyle(
                                      fontSize: 15,
                                      fontWeight: FontWeight.bold,
                                      color: Color(0xFF334155),
                                      fontFamily: 'Outfit',
                                    ),
                                  ),
                                  const SizedBox(height: 16),

                                  // Inner Attachment Box (PDF / File Preview Box)
                                  Container(
                                    width: double.infinity,
                                    padding: const EdgeInsets.all(24),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFF8FAFC),
                                      borderRadius: BorderRadius.circular(16),
                                      border: Border.all(color: const Color(0xFFE2E8F0)),
                                    ),
                                    child: Column(
                                      children: [
                                        // PDF Icon
                                        const Icon(
                                          Icons.picture_as_pdf_rounded,
                                          size: 48,
                                          color: Color(0xFFEF4444),
                                        ),
                                        const SizedBox(height: 12),

                                        // PDF File Name
                                        Text(
                                          fileName,
                                          textAlign: TextAlign.center,
                                          style: const TextStyle(
                                            fontSize: 15,
                                            fontWeight: FontWeight.bold,
                                            color: Color(0xFF1E293B),
                                            fontFamily: 'Outfit',
                                          ),
                                        ),
                                        const SizedBox(height: 16),

                                        // Buttons Row: [Read] & [Get PDF]
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            // Read Button (Green)
                                            ElevatedButton.icon(
                                              onPressed: () {
                                                if (filePath != null) {
                                                  setState(() {
                                                    _showPreview = !_showPreview;
                                                  });
                                                }
                                              },
                                              icon: const Icon(Icons.menu_book, size: 16),
                                              label: const Text('Read'),
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor: const Color(0xFF10B981), // Emerald green
                                                foregroundColor: Colors.white,
                                                elevation: 0,
                                                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                                textStyle: const TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontFamily: 'Outfit',
                                                  fontSize: 13,
                                                ),
                                              ),
                                            ),
                                            const SizedBox(width: 12),

                                            // Get PDF Button (Blue Outlined)
                                            OutlinedButton.icon(
                                              onPressed: () {
                                                if (filePath != null) {
                                                  _downloadFile(filePath);
                                                }
                                              },
                                              icon: const Icon(Icons.download, size: 16),
                                              label: const Text('Get PDF'),
                                              style: OutlinedButton.styleFrom(
                                                foregroundColor: const Color(0xFF3B82F6),
                                                side: const BorderSide(color: Color(0xFF3B82F6), width: 1.5),
                                                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                                textStyle: const TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontFamily: 'Outfit',
                                                  fontSize: 13,
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),

                                  if (_showPreview && filePath != null && filePath.toLowerCase().endsWith('.pdf')) ...[
                                    const SizedBox(height: 24),
                                    const Text(
                                      'Material Preview',
                                      style: TextStyle(
                                        fontSize: 15,
                                        fontWeight: FontWeight.bold,
                                        color: Color(0xFF334155),
                                        fontFamily: 'Outfit',
                                      ),
                                    ),
                                    const SizedBox(height: 16),
                                    Container(
                                      height: 500,
                                      width: double.infinity,
                                      decoration: BoxDecoration(
                                        border: Border.all(color: const Color(0xFFE2E8F0)),
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: ClipRRect(
                                        borderRadius: BorderRadius.circular(12),
                                        child: SfPdfViewer.network(
                                          filePath.startsWith('http')
                                              ? filePath
                                              : 'https://ezpaizy.app/storage/$filePath',
                                        ),
                                      ),
                                    ),
                                  ],

                                  const SizedBox(height: 24),
                                  const Divider(color: Color(0xFFE2E8F0), height: 1),
                                  const SizedBox(height: 24),

                                  // Section Header: Description
                                  const Text(
                                    'Description',
                                    style: TextStyle(
                                      fontSize: 15,
                                      fontWeight: FontWeight.bold,
                                      color: Color(0xFF334155),
                                      fontFamily: 'Outfit',
                                    ),
                                  ),
                                  const SizedBox(height: 12),

                                  // Content / Description Text Body
                                  Text(
                                    (content!['content'] ?? '').toString().trim().isNotEmpty
                                        ? content!['content']
                                        : 'No additional description provided.',
                                    style: const TextStyle(
                                      fontSize: 14,
                                      height: 1.6,
                                      color: Color(0xFF475569),
                                      fontFamily: 'Outfit',
                                    ),
                                  ),
                                ],
                              ),
                            ),

                            // Study Notepad if Read/Write student
                            if (isReadWrite) ...[
                              const SizedBox(height: 24),
                              StudyNotepadWidget(
                                resourceType: 'content',
                                resourceId: widget.contentId,
                                topic: content!['topic'] ?? 'General',
                                defaultTitle: 'Notes: ${content!['title'] ?? ''}',
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
}
