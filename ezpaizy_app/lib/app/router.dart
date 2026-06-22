import 'package:go_router/go_router.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
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
    if (loc.startsWith('/contents')) return 2;
    if (loc.startsWith('/flashcards')) return 3;
    if (loc.startsWith('/progress') || loc.startsWith('/revision')) return 4;
    if (loc.startsWith('/learning')) return 5;
    return 0;
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final user = auth.user;
    
    return Scaffold(
      body: Row(
        children: [
          NavigationRail(
            selectedIndex: _selectedIndex(context),
            onDestinationSelected: (i) {
              switch (i) {
                case 0: context.go('/dashboard'); break;
                case 1: context.go('/quizzes'); break;
                case 2: context.go('/contents'); break;
                case 3: context.go('/flashcards'); break;
                case 4: context.go('/progress'); break;
                case 5: context.go('/learning-profile'); break;
              }
            },
            labelType: NavigationRailLabelType.all,
            backgroundColor: Colors.white,
            minWidth: 90,
            leading: Column(
              children: [
                const SizedBox(height: 20),
                CircleAvatar(
                  radius: 25,
                  backgroundColor: Colors.deepPurpleAccent,
                  child: Text(
                    (user?['name'] != null && (user!['name'] as String).isNotEmpty)
                        ? user['name'][0].toUpperCase()
                        : 'U',
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                  ),
                ),
                const SizedBox(height: 10),
                Text(
                  user?['name'] ?? 'Student',
                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 20),
              ],
            ),
            trailing: Align(
              alignment: Alignment.bottomCenter,
              child: Padding(
                padding: const EdgeInsets.only(bottom: 20),
                child: IconButton(
                  onPressed: () {
                    auth.logout();
                    context.go('/login');
                  },
                  icon: const Icon(Icons.logout, color: Colors.redAccent),
                  tooltip: 'Sign out',
                ),
              ),
            ),
            destinations: const [
              NavigationRailDestination(
                icon: Icon(Icons.home_outlined),
                selectedIcon: Icon(Icons.home),
                label: Text('Home'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.quiz_outlined),
                selectedIcon: Icon(Icons.quiz),
                label: Text('Quizzes'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.menu_book_outlined),
                selectedIcon: Icon(Icons.menu_book),
                label: Text('Materials'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.style_outlined),
                selectedIcon: Icon(Icons.style),
                label: Text('Flashcards'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.bar_chart_outlined),
                selectedIcon: Icon(Icons.bar_chart),
                label: Text('Progress'),
              ),
              NavigationRailDestination(
                icon: Icon(Icons.assignment_ind_outlined),
                selectedIcon: Icon(Icons.assignment_ind),
                label: Text('Learning Style'),
              ),
            ],
          ),
          const VerticalDivider(thickness: 1, width: 1),
          Expanded(child: child),
        ],
      ),
    );
  }
}
