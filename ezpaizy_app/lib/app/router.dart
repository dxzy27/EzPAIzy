import 'package:go_router/go_router.dart';
import 'package:flutter/material.dart';
import '../providers/auth_provider.dart';
import '../screens/login_screen.dart';
import '../screens/dashboard_screen.dart';
import '../screens/quizzes_screen.dart';
import '../screens/take_quiz_screen.dart';
import '../screens/contents_screen.dart';
import '../screens/content_detail_screen.dart';
import '../screens/flashcards_screen.dart';
import '../screens/flashcard_practice_screen.dart';
import '../screens/flashcard_study_screen.dart';
import '../screens/progress_screen.dart';
import '../screens/revision_screen.dart';
import '../screens/daily_quran_screen.dart';
import '../screens/learning_style_screen.dart';
import '../screens/learning_profile_screen.dart';

class AppRouter {
  static GoRouter router(AuthProvider auth) => GoRouter(
        initialLocation: auth.token != null ? '/dashboard' : '/login',
        redirect: (context, state) {
          final loggedIn = auth.token != null;
          final onLogin = state.matchedLocation == '/login';
          if (!loggedIn && !onLogin) return '/login';
          if (loggedIn && onLogin) return '/dashboard';
          return null;
        },
        routes: [
          GoRoute(
            path: '/login',
            builder: (_, _) => const LoginScreen(),
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
                path: '/daily-quran',
                builder: (_, _) => const DailyQuranScreen(),
              ),
              GoRoute(
                path: '/learning-style',
                builder: (_, _) => const LearningStyleScreen(),
              ),
              GoRoute(
                path: '/learning-profile',
                builder: (_, _) => const LearningProfileScreen(),
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
    if (loc.startsWith('/progress') || loc.startsWith('/revision')) return 3;
    if (loc.startsWith('/daily-quran')) return 4;
    if (loc.startsWith('/contents') || loc.startsWith('/learning')) return 0; // fallback to home
    return 0;
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
              context.go('/progress');
              break;
            case 4:
              context.go('/daily-quran');
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
            icon: Icon(Icons.quiz_outlined),
            activeIcon: Icon(Icons.quiz),
            label: 'Quizzes',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.style_outlined),
            activeIcon: Icon(Icons.style),
            label: 'Flashcards',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.bar_chart_outlined),
            activeIcon: Icon(Icons.bar_chart),
            label: 'Progress',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.auto_stories_outlined),
            activeIcon: Icon(Icons.auto_stories),
            label: 'Quran',
          ),
        ],
      ),
    );
  }
}
