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
import '../screens/student_profile_screen.dart';
import '../screens/notes_folder_screen.dart';
import '../screens/register_screen.dart';
import '../screens/daily_doa_screen.dart';

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
          GoRoute(
            path: '/quiz/:id',
            builder: (_, state) => TakeQuizScreen(
              quizId: int.parse(state.pathParameters['id']!),
            ),
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
                builder: (_, state) {
                  final index = int.tryParse(state.uri.queryParameters['index'] ?? '0') ?? 0;
                  return FlashcardPracticeScreen(
                    setId: int.parse(state.pathParameters['id']!),
                    initialIndex: index,
                  );
                },
              ),
              GoRoute(
                path: '/flashcards/set/:id',
                builder: (_, state) {
                  final index = int.tryParse(state.uri.queryParameters['index'] ?? '0') ?? 0;
                  return FlashcardPracticeScreen(
                    setId: int.parse(state.pathParameters['id']!),
                    initialIndex: index,
                  );
                },
              ),
              GoRoute(
                path: '/flashcards/:id/study',
                builder: (_, state) {
                  final index = int.tryParse(state.uri.queryParameters['index'] ?? '0') ?? 0;
                  return FlashcardStudyScreen(
                    setId: int.parse(state.pathParameters['id']!),
                    initialIndex: index,
                  );
                },
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
                path: '/daily-doa',
                builder: (_, _) => const DailyDoaScreen(),
              ),

              GoRoute(
                path: '/learning-style',
                builder: (_, state) {
                  final retake = state.uri.queryParameters['retake'] == 'true';
                  return LearningStyleScreen(retake: retake);
                },
              ),
              GoRoute(
                path: '/profile',
                builder: (_, _) => const StudentProfileScreen(),
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
    if (loc.startsWith('/contents')) return 3;
    if (loc.startsWith('/daily-doa')) return 4;
    if (loc.startsWith('/learning-style')) return 5;
    return 0; // Default to Home/Dashboard
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
        selectedLabelStyle: const TextStyle(fontFamily: 'Outfit', fontWeight: FontWeight.bold, fontSize: 11),
        unselectedLabelStyle: const TextStyle(fontFamily: 'Outfit', fontSize: 11),
        onTap: (i) {
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
              context.go('/contents');
              break;
            case 4:
              context.go('/daily-doa');
              break;
            case 5:
              context.go('/learning-style');
              break;
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
            icon: Icon(Icons.article_outlined),
            activeIcon: Icon(Icons.article),
            label: 'Materials',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.nights_stay_outlined),
            activeIcon: Icon(Icons.nights_stay),
            label: 'Daily Doa',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.psychology_outlined),
            activeIcon: Icon(Icons.psychology),
            label: 'Diagnosis',
          ),
        ],
      ),
    );
  }
}
