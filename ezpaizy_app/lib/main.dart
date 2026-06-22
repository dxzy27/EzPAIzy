import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'app/router.dart';
import 'app/theme.dart';
import 'providers/auth_provider.dart';

late AuthProvider _authProvider;

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  _authProvider = AuthProvider();
  await _authProvider.loadToken();
  runApp(
    ChangeNotifierProvider.value(
      value: _authProvider,
      child: const EzPAIzyApp(),
    ),
  );
}

class EzPAIzyApp extends StatelessWidget {
  const EzPAIzyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      title: 'EzPAIzy',
      theme: AppTheme.theme,
      routerConfig: AppRouter.router(_authProvider),
      debugShowCheckedModeBanner: false,
    );
  }
}
