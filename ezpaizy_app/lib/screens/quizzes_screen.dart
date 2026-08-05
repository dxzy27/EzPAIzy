import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../widgets/profile_dropdown_helper.dart';
import '../widgets/app_top_bar.dart';

class QuizzesScreen extends StatefulWidget {
  const QuizzesScreen({super.key});

  @override
  State<QuizzesScreen> createState() => _QuizzesScreenState();
}

class _QuizzesScreenState extends State<QuizzesScreen> {
  List<dynamic> quizzes = [];
  List<String> topics = [];
  List<String> filteredTopics = [];
  bool loading = true;
  final _search = TextEditingController();

  @override
  void initState() {
    super.initState();
    _load();
    _search.addListener(_filter);
  }

  @override
  void dispose() {
    _search.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => loading = true);
    try {
      quizzes = await ApiService.getQuizzes();
      topics = await ApiService.getTopics('quiz');
      
      if (topics.isEmpty) {
        final Set<String> uniqueTopics = {};
        for (var q in quizzes) {
          if (q['topic'] != null && q['topic'].toString().trim().isNotEmpty) {
            uniqueTopics.add(q['topic'].toString().trim());
          } else {
            uniqueTopics.add('General');
          }
        }
        topics = uniqueTopics.toList()..sort();
      }
      filteredTopics = topics;
    } catch (_) {}
    setState(() => loading = false);
  }

  void _filter() {
    final q = _search.text.toLowerCase();
    setState(() {
      filteredTopics = topics
          .where((topic) => topic.toLowerCase().contains(q))
          .toList();
    });
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final userName = auth.user?['name'] ?? 'Student';
    final initial = userName.isNotEmpty ? userName[0].toUpperCase() : 'D';

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
          color: const Color(0xFFF1F5F9).withValues(alpha: 0.15), // matching dashboard overlay
          child: SafeArea(
            child: Center(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 800),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Top Navigation Bar
                    const Padding(
                      padding: EdgeInsets.fromLTRB(24, 20, 24, 0),
                      child: AppTopBar(),
                    ),

                    // Custom web-aligned Header
                    Padding(
                      padding: const EdgeInsets.fromLTRB(24, 20, 24, 8),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Available Quizzes',
                        style: TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.w900,
                          color: Color(0xFF1E293B),
                          fontFamily: 'Outfit',
                        ),
                      ),
                      const SizedBox(height: 2),
                      const Text(
                        'Test your knowledge with these quizzes',
                        style: TextStyle(
                          fontSize: 13,
                          color: Color(0xFF64748B),
                          fontFamily: 'Outfit',
                        ),
                      ),
                      const SizedBox(height: 20),
                      
                      // Search Bar styled like web input
                      TextField(
                        controller: _search,
                        decoration: InputDecoration(
                          hintText: 'Search topics...',
                          hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontFamily: 'Outfit'),
                          prefixIcon: const Icon(Icons.search, color: Color(0xFF94A3B8)),
                          filled: true,
                          fillColor: Colors.white,
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: const BorderSide(color: Color(0xFF3B82F6)),
                          ),
                          contentPadding: const EdgeInsets.symmetric(vertical: 10),
                        ),
                      ),
                      const SizedBox(height: 24),

                      // "TOPICS" Header label
                      const Text(
                        'TOPICS',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF64748B),
                          letterSpacing: 1.0,
                          fontFamily: 'Outfit',
                        ),
                      ),
                    ],
                  ),
                ),

                // Topics Grid
                Expanded(
                  child: loading
                      ? const Center(child: CircularProgressIndicator())
                      : filteredTopics.isEmpty
                          ? const Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.folder_off, size: 64, color: Color(0xFFCBD5E1)),
                                  SizedBox(height: 12),
                                  Text('No topics found', style: TextStyle(color: Color(0xFF64748B), fontFamily: 'Outfit')),
                                ],
                              ),
                            )
                          : RefreshIndicator(
                              onRefresh: _load,
                              child: GridView.builder(
                                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 8),
                                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                  crossAxisCount: 2,
                                  crossAxisSpacing: 16,
                                  mainAxisSpacing: 16,
                                  childAspectRatio: 1.3, // wider layout matching web card size ratio
                                ),
                                itemCount: filteredTopics.length,
                                itemBuilder: (context, index) {
                                  final topic = filteredTopics[index];

                                  return InkWell(
                                    onTap: () => context.push('/quizzes/folder/${Uri.encodeComponent(topic)}'),
                                    borderRadius: BorderRadius.circular(16),
                                    child: Container(
                                      decoration: BoxDecoration(
                                        color: Colors.white,
                                        borderRadius: BorderRadius.circular(16),
                                        border: Border.all(color: const Color(0xFFE2E8F0)),
                                        boxShadow: [
                                          BoxShadow(
                                            color: Colors.black.withOpacity(0.02),
                                            blurRadius: 8,
                                            offset: const Offset(0, 4),
                                          ),
                                        ],
                                      ),
                                      child: Column(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          // Yellow folder icon matching web
                                          const Icon(
                                            Icons.folder,
                                            size: 48,
                                            color: Color(0xFFFFC107), // matching yellow color
                                          ),
                                          const SizedBox(height: 8),
                                          Text(
                                            topic,
                                            style: const TextStyle(
                                              fontWeight: FontWeight.bold,
                                              fontSize: 14,
                                              color: Color(0xFF1E293B),
                                              fontFamily: 'Outfit',
                                            ),
                                            textAlign: TextAlign.center,
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ],
                                      ),
                                    ),
                                  );
                                },
                              ),
                            ),
                ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
