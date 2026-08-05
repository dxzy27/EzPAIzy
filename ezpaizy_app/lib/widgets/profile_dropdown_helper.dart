import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';

void showProfileDropdown(BuildContext context, Offset globalPosition) {
  final auth = Provider.of<AuthProvider>(context, listen: false);
  final user = auth.user;
  final name = (user?['name'] ?? 'Student').toString();

  showMenu(
    context: context,
    position: RelativeRect.fromLTRB(
      globalPosition.dx - 160,
      globalPosition.dy + 10,
      globalPosition.dx,
      globalPosition.dy + 200,
    ),
    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
    elevation: 8,
    items: <PopupMenuEntry<dynamic>>[
      PopupMenuItem<dynamic>(
        enabled: false,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              name,
              style: const TextStyle(
                fontWeight: FontWeight.w800,
                fontSize: 15,
                color: Color(0xFF1E293B),
                fontFamily: 'Outfit',
              ),
            ),
            const SizedBox(height: 2),
            const Text(
              'Student',
              style: TextStyle(
                fontSize: 11,
                color: Color(0xFF94A3B8),
                fontFamily: 'Outfit',
              ),
            ),
          ],
        ),
      ),
      const PopupMenuDivider(),
      PopupMenuItem<dynamic>(
        value: 'profile',
        child: Row(
          children: [
            Icon(Icons.person_outline, size: 18, color: Colors.grey[700]),
            const SizedBox(width: 8),
            const Text('Profile', style: TextStyle(fontSize: 13, fontFamily: 'Outfit', fontWeight: FontWeight.w500)),
          ],
        ),
      ),
      PopupMenuItem<dynamic>(
        value: 'revision',
        child: Row(
          children: [
            Icon(Icons.star_outline_rounded, size: 18, color: Colors.amber[800]),
            const SizedBox(width: 8),
            const Text('My Revision List', style: TextStyle(fontSize: 13, fontFamily: 'Outfit', fontWeight: FontWeight.w600)),
          ],
        ),
      ),
      PopupMenuItem<dynamic>(
        value: 'progress',
        child: Row(
          children: [
            Icon(Icons.bar_chart_outlined, size: 18, color: Colors.grey[700]),
            const SizedBox(width: 8),
            const Text('My Progress', style: TextStyle(fontSize: 13, fontFamily: 'Outfit', fontWeight: FontWeight.w500)),
          ],
        ),
      ),
      const PopupMenuDivider(),
      PopupMenuItem<dynamic>(
        value: 'signout',
        child: Row(
          children: const [
            Icon(Icons.logout, size: 18, color: Colors.redAccent),
            SizedBox(width: 8),
            Text(
              'Sign out',
              style: TextStyle(
                fontSize: 13,
                fontFamily: 'Outfit',
                color: Colors.redAccent,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      ),
    ],
  ).then((value) async {
    if (value == 'profile') {
      context.go('/learning-profile');
    } else if (value == 'revision') {
      context.go('/revision');
    } else if (value == 'progress') {
      context.go('/progress');
    } else if (value == 'signout') {
      await ApiService.logout();
      auth.logout();
      if (context.mounted) {
        context.go('/login');
      }
    }
  });
}
