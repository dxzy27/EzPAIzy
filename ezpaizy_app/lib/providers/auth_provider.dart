import 'dart:convert';
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
    final userStr = prefs.getString('user');
    if (userStr != null) {
      try {
        user = jsonDecode(userStr);
      } catch (_) {}
    }
    notifyListeners();
  }

  Future<void> setUser(Map<String, dynamic> newUser) async {
    user = newUser;
    notifyListeners();
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('user', jsonEncode(newUser));
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
        if (user != null) {
          await prefs.setString('user', jsonEncode(user));
        }
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
    await prefs.remove('user');
    token = null;
    user = null;
    notifyListeners();
  }
}
