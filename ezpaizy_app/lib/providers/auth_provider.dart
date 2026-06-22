import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  String? token;
  Map<String, dynamic>? user;
  bool isLoading = false;
  String? error;

  Future<void> loadToken() async {
    final prefs = await SharedPreferences.getInstance();
    token = prefs.getString('token');
    notifyListeners();
  }

  Future<bool> login(String email, String password) async {
    isLoading = true;
    error = null;
    notifyListeners();

    try {
      final data = await ApiService.login(email, password);
      if (data['token'] != null) {
        token = data['token'];
        user = data['user'];
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('token', token!);
        isLoading = false;
        notifyListeners();
        return true;
      }
      error = data['message'] ?? 'Login failed';
    } catch (e) {
      error = 'Connection error. Is your server running?';
    }

    isLoading = false;
    notifyListeners();
    return false;
  }

  Future<void> logout() async {
    try {
      await ApiService.logout();
    } catch (_) {}
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    token = null;
    user = null;
    notifyListeners();
  }
}
