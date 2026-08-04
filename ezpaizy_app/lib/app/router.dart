import 'package:go_router/go_router.dart';
import 'package:flutter/material.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import 'package:provider/provider.dart';
import '../screens/login_screen.dart';
import '../screens/dashboard_screen.dart';
import '../screens/quizzes_screen.dart';
import '../screens/quiz_folder_screen.dart';
import '../screens/take_quiz_screen.dart';
import '../screens/contents_screen.dart';
import '../screens/content_folder_screen.dart';
import '../screens/content_detail_screen.dart';
import '../screens/flashcards_screen.dart';
import '../screens/flashcard_folder_screen.dart';
import '../screens/flashcard_practice_screen.dart';
import '../screens/flashcard_study_screen.dart';
import '../screens/progress_screen.dart';
import '../screens/revision_screen.dart';

import '../screens/learning_style_screen.dart';
import '../screens/learning_profile_screen.dart';
import '../screens/notes_folder_screen.dart';
import '../screens/register_screen.dart';

class AppRouter {
  static GoRouter router(AuthProvider auth) => GoRouter(
        initialLocation: auth.token != null ? '/dashboard' : '/login',
        redirect: (context, state) {
          final loggedIn = auth.token != null;
          final onLogin = state.matchedLocation == '/login';
          final onRegister = state.matchedLocation == '/register';
          if (!loggedIn && !onLogin && !onRegister) return '/login';
          if (loggedIn && (onLogin || onRegister)) return '/dashboard';
          return null;
        },
        routes: [
          GoRoute(
            path: '/login',
            builder: (_, _) => const LoginScreen(),
          ),
          GoRoute(
            path: '/register',
            builder: (_, _) => const RegisterScreen(),
          ),
          ShellRoute(
            builder: (context, state, child) =>
                ScaffoldWithNav(child: child),
            routes: [
              GoRoute(
                path: '/dashboard',
                builder: (_, _) => const DashboardScreen(),
              ),
              GoRoute(
                path: '/quizzes',
                builder: (_, _) => const QuizzesScreen(),
              ),
              GoRoute(
                path: '/quizzes/folder/:topic',
                builder: (_, state) => QuizFolderScreen(
                  topic: Uri.decodeComponent(state.pathParameters['topic']!),
                ),
              ),
              GoRoute(
                path: '/quiz/:id',
                builder: (_, state) => TakeQuizScreen(
                  quizId: int.parse(state.pathParameters['id']!),
                ),
              ),
              GoRoute(
                path: '/contents',
                builder: (_, _) => const ContentsScreen(),
              ),
              GoRoute(
                path: '/contents/folder/:topic',
                builder: (_, state) => ContentFolderScreen(
                  topic: Uri.decodeComponent(state.pathParameters['topic']!),
                ),
              ),
              GoRoute(
                path: '/contents/:id',
                builder: (_, state) => ContentDetailScreen(
                  contentId: int.parse(state.pathParameters['id']!),
                ),
              ),
              GoRoute(
                path: '/flashcards',
                builder: (_, _) => const FlashcardsScreen(),
              ),
              GoRoute(
                path: '/flashcards/folder/:topic',
                builder: (_, state) => FlashcardFolderScreen(
                  topic: Uri.decodeComponent(state.pathParameters['topic']!),
                ),
              ),
              GoRoute(
                path: '/flashcards/:id',
                builder: (_, state) => FlashcardPracticeScreen(
                  setId: int.parse(state.pathParameters['id']!),
                ),
              ),
              GoRoute(
                path: '/flashcards/:id/study',
                builder: (_, state) => FlashcardStudyScreen(
                  setId: int.parse(state.pathParameters['id']!),
                ),
              ),
              GoRoute(
                path: '/progress',
                builder: (_, _) => const ProgressScreen(),
              ),
              GoRoute(
                path: '/revision',
                builder: (_, _) => const RevisionScreen(),
              ),

              GoRoute(
                path: '/learning-style',
                builder: (_, _) => const LearningStyleScreen(),
              ),
              GoRoute(
                path: '/learning-profile',
                builder: (_, _) => const LearningProfileScreen(),
              ),
              GoRoute(
                path: '/notes/folder/:topic',
                builder: (_, state) => NotesFolderScreen(
                  topic: state.pathParameters['topic']!,
                ),
              ),
            ],
          ),
        ],
      );
}

class ScaffoldWithNav extends StatelessWidget {
  final Widget child;
  const ScaffoldWithNav({super.key, required this.child});

  int _selectedIndex(BuildContext context) {
    final loc = GoRouterState.of(context).matchedLocation;
    if (loc.startsWith('/quizzes') || loc.startsWith('/quiz')) return 1;
    if (loc.startsWith('/flashcards')) return 2;
    if (loc.startsWith('/learning-style')) return 3;
    if (loc.startsWith('/dashboard')) return 0;
    return 4; // Highlight 'More' for everything else
  }

  void _showMoreMenu(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        final auth = Provider.of<AuthProvider>(context, listen: false);
        return SafeArea(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const SizedBox(height: 8),
              Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey[300],
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(height: 16),
              ListTile(
                leading: const Icon(Icons.article_outlined, color: Color(0xFF3B82F6)),
                title: const Text('Materials', style: TextStyle(fontWeight: FontWeight.w600, fontFamily: 'Outfit')),
                onTap: () {
                  Navigator.pop(ctx);
                  context.go('/contents');
                },
               ),
              const Divider(),
              ListTile(
                leading: const Icon(Icons.logout, color: Colors.redAccent),
                title: const Text('Sign Out', style: TextStyle(fontWeight: FontWeight.w600, color: Colors.redAccent, fontFamily: 'Outfit')),
                onTap: () async {
                  Navigator.pop(ctx);
                  await ApiService.logout();
                  auth.logout();
                  if (context.mounted) {
                    context.go('/login');
                  }
                },
              ),
              const SizedBox(height: 12),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: child,
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _selectedIndex(context),
        type: BottomNavigationBarType.fixed,
        selectedItemColor: Theme.of(context).colorScheme.primary,
        unselectedItemColor: Colors.grey,
        selectedLabelStyle: const TextStyle(fontFamily: 'Outfit', fontWeight: FontWeight.bold),
        unselectedLabelStyle: const TextStyle(fontFamily: 'Outfit'),
        onTap: (i) {
          if (i == 4) {
            _showMoreMenu(context);
          } else {
            switch (i) {
              case 0:
                context.go('/dashboard');
                break;
              case 1:
                context.go('/quizzes');
                break;
              case 2:
                context.go('/flashcards');
                break;
              case 3:
                context.go('/learning-style');
                break;
            }
          }
        },
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.home_outlined),
            activeIcon: Icon(Icons.home),
            label: 'Home',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.edit_note_outlined),
            activeIcon: Icon(Icons.edit_note),
            label: 'Quizzes',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.assignment_outlined),
            activeIcon: Icon(Icons.assignment),
            label: 'Flashcards',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.psychology_outlined),
            activeIcon: Icon(Icons.psychology),
            label: 'Diagnosis',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.more_horiz_outlined),
            activeIcon: Icon(Icons.more_horiz),
            label: 'More',
          ),
        ],
      ),
    );
  }
}
