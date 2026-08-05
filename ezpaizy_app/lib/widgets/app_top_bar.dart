import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import 'profile_dropdown_helper.dart';

class AppTopBar extends StatelessWidget {
  final bool showBackButton;
  final VoidCallback? onBack;

  const AppTopBar({
    super.key,
    this.showBackButton = false,
    this.onBack,
  });

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final user = auth.user;
    final name = (user?['name'] ?? 'Student').toString();
    final initial = name.isNotEmpty ? name[0].toUpperCase() : 'S';
    final learningStyle = user?['learning_style'];

    Color accentColor = const Color(0xFF14B8A6);
    if (learningStyle == 'auditory') {
      accentColor = const Color(0xFFE5B181);
    } else if (learningStyle == 'read_write') {
      accentColor = const Color(0xFF7D6867);
    } else if (learningStyle == 'visual') {
      accentColor = const Color(0xFF06B6D4);
    } else if (learningStyle == 'kinesthetic') {
      accentColor = const Color(0xFFD946EF);
    }

    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            children: [
              if (showBackButton) ...[
                Material(
                  color: Colors.transparent,
                  child: InkWell(
                    borderRadius: BorderRadius.circular(20),
                    onTap: onBack ??
                        () {
                          if (context.canPop()) {
                            context.pop();
                          } else {
                            context.go('/dashboard');
                          }
                        },
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
                const SizedBox(width: 10),
              ],
              GestureDetector(
                onTap: () => context.go('/dashboard'),
                child: Image.asset(
                  'assets/images/newlogo.png',
                  height: 38,
                  errorBuilder: (context, error, stackTrace) => const Icon(Icons.school, color: Color(0xFF3B82F6), size: 32),
                ),
              ),
            ],
          ),
          GestureDetector(
            onTapDown: (details) => showProfileDropdown(context, details.globalPosition),
            child: Container(
              width: 36,
              height: 36,
              decoration: BoxDecoration(
                color: accentColor,
                borderRadius: BorderRadius.circular(10),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.1),
                    blurRadius: 4,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              alignment: Alignment.center,
              child: Text(
                initial,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                  fontSize: 14,
                  fontFamily: 'Outfit',
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
