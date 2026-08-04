import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // Live server URL
  static const String baseUrl = 'https://ezpaizy.app/api';

  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  static Future<Map<String, String>> _headers() async {
    final token = await getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  static Future<Map<String, dynamic>> login(
      String email, String password) async {
    try {
      print('DEBUG API: Logging in with email: $email');
      final res = await http.post(
        Uri.parse('$baseUrl/login'),
        headers: await _headers(),
        body: jsonEncode({'email': email, 'password': password}),
      );
      print('DEBUG API: Login Response status: ${res.statusCode}');
      print('DEBUG API: Login Response body: ${res.body}');
      final decoded = jsonDecode(res.body);
      return (decoded is Map<String, dynamic>) ? decoded : {};
    } catch (e) {
      print('DEBUG API: Login Error: $e');
      return {};
    }
  }

  static Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    required String phoneNumber,
    required String address,
    required String role,
    required String className,
  }) async {
    try {
      final res = await http.post(
        Uri.parse('$baseUrl/register'),
        headers: await _headers(),
        body: jsonEncode({
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': passwordConfirmation,
          'phone_number': phoneNumber,
          'address': address,
          'role': role,
          'class_name': className,
        }),
      );

      final decoded = jsonDecode(res.body);
      return (decoded is Map<String, dynamic>) ? decoded : {};
    } catch (_) {
      return {};
    }
  }

  static Future<List<String>> getClasses() async {
    try {
      final res = await http.get(
        Uri.parse('$baseUrl/classes'),
        headers: await _headers(),
      );
      final decoded = jsonDecode(res.body);
      if (decoded['success'] == true && decoded['data'] is List) {
        return List<String>.from(decoded['data']);
      }
      return [];
    } catch (_) {
      return [];
    }
  }

  static Future<void> logout() async {
    await http.post(
      Uri.parse('$baseUrl/logout'),
      headers: await _headers(),
    );
  }

  static Future<Map<String, dynamic>> getDashboard() async {
    try {
      print('DEBUG API: Fetching dashboard from $baseUrl/student/dashboard');
      final headers = await _headers();
      print('DEBUG API: Request headers: $headers');
      final res = await http.get(
        Uri.parse('$baseUrl/student/dashboard'),
        headers: headers,
      );
      print('DEBUG API: Response status: ${res.statusCode}');
      print('DEBUG API: Response body: ${res.body}');
      final decoded = jsonDecode(res.body);
      return (decoded is Map<String, dynamic>) ? decoded : {};
    } catch (e, stack) {
      print('DEBUG API: Error fetching dashboard: $e');
      print(stack);
      return {};
    }
  }

  static Future<List<String>> getQuizTopics() async {
    try {
      final res = await http.get(Uri.parse('$baseUrl/student/quiz-topics'),
          headers: await _headers());
      if (res.statusCode != 200) return [];
      final decoded = jsonDecode(res.body);
      if (decoded is List) {
        return List<String>.from(decoded.map((x) => x.toString()));
      }
      return [];
    } catch (_) {
      return [];
    }
  }

  static Future<List<dynamic>> getQuizzes() async {
    try {
      final res = await http.get(Uri.parse('$baseUrl/student/quizzes'),
          headers: await _headers());
      if (res.statusCode != 200) return [];
      final decoded = jsonDecode(res.body);
      return (decoded is List) ? decoded : [];
    } catch (_) {
      return [];
    }
  }

  static Future<Map<String, dynamic>> getQuizDetail(int id) async {
    try {
      final res = await http.get(Uri.parse('$baseUrl/student/quiz/$id'),
          headers: await _headers());
      if (res.statusCode != 200) return {};
      final decoded = jsonDecode(res.body);
      return (decoded is Map<String, dynamic>) ? decoded : {};
    } catch (_) {
      return {};
    }
  }

  static Future<Map<String, dynamic>> submitQuiz(
      int id, Map<String, dynamic> answers) async {
    try {
      final res = await http.post(
        Uri.parse('$baseUrl/student/quiz/$id/submit'),
        headers: await _headers(),
        body: jsonEncode({'answers': answers}),
      );
      final decoded = jsonDecode(res.body);
      return (decoded is Map<String, dynamic>) ? decoded : {};
    } catch (_) {
      return {};
    }
  }

  static Future<List<dynamic>> getContents() async {
    try {
      final res = await http.get(Uri.parse('$baseUrl/student/contents'),
          headers: await _headers());
      if (res.statusCode != 200) return [];
      final decoded = jsonDecode(res.body);
      return (decoded is List) ? decoded : [];
    } catch (_) {
      return [];
    }
  }

  static Future<Map<String, dynamic>> getContentDetail(int id) async {
    final res = await http.get(Uri.parse('$baseUrl/student/contents/$id'),
        headers: await _headers());
    return jsonDecode(res.body);
  }

  static Future<List<String>> getTopics() async {
    try {
      final res = await http.get(Uri.parse('$baseUrl/student/topics'),
          headers: await _headers());
      if (res.statusCode != 200) return [];
      final decoded = jsonDecode(res.body);
      if (decoded is List) {
        return List<String>.from(decoded.map((x) => x.toString()));
      }
      return [];
    } catch (_) {
      return [];
    }
  }

  static Future<List<dynamic>> getFlashcards() async {
    try {
      final res = await http.get(Uri.parse('$baseUrl/student/flashcards'),
          headers: await _headers());
      if (res.statusCode != 200) return [];
      final decoded = jsonDecode(res.body);
      return (decoded is List) ? decoded : [];
    } catch (_) {
      return [];
    }
  }

  static Future<Map<String, dynamic>> getFlashcardDetail(int id) async {
    final res = await http.get(Uri.parse('$baseUrl/student/flashcards/$id'),
        headers: await _headers());
    return jsonDecode(res.body);
  }

  static Future<Map<String, dynamic>> getDueFlashcards(int id) async {
    final res = await http.get(Uri.parse('$baseUrl/student/flashcards/$id/study'),
        headers: await _headers());
    return jsonDecode(res.body);
  }

  static Future<void> submitFlashcardReview(int flashcardId, int quality) async {
    await http.post(
      Uri.parse('$baseUrl/student/flashcards/$flashcardId/review'),
      headers: await _headers(),
      body: jsonEncode({'quality': quality}),
    );
  }

  static Future<List<dynamic>> getProgress() async {
    try {
      final res = await http.get(Uri.parse('$baseUrl/student/progress'),
          headers: await _headers());
      if (res.statusCode != 200) return [];
      final decoded = jsonDecode(res.body);
      return (decoded is List) ? decoded : [];
    } catch (_) {
      return [];
    }
  }

  static Future<Map<String, dynamic>> getProgressDetail(int progressId) async {
    try {
      final res = await http.get(
          Uri.parse('$baseUrl/student/progress/$progressId'),
          headers: await _headers());
      if (res.statusCode != 200) return {};
      final decoded = jsonDecode(res.body);
      return (decoded is Map<String, dynamic>) ? decoded : {};
    } catch (_) {
      return {};
    }
  }

  static Future<List<dynamic>> getRevision() async {
    try {
      final res = await http.get(Uri.parse('$baseUrl/student/revision'),
          headers: await _headers());
      if (res.statusCode != 200) return [];
      final decoded = jsonDecode(res.body);
      return (decoded is List) ? decoded : [];
    } catch (_) {
      return [];
    }
  }

  static Future<void> addFavorite(int contentId) async {
    await http.post(Uri.parse('$baseUrl/student/favorites/$contentId'),
        headers: await _headers());
  }

  static Future<void> removeFavorite(int contentId) async {
    await http.delete(Uri.parse('$baseUrl/student/favorites/$contentId'),
        headers: await _headers());
  }

  static Future<void> addFlashcardFavorite(int setId) async {
    await http.post(Uri.parse('$baseUrl/student/favorites/flashcard/$setId'),
        headers: await _headers());
  }

  static Future<void> removeFlashcardFavorite(int setId) async {
    await http.delete(Uri.parse('$baseUrl/student/favorites/flashcard/$setId'),
        headers: await _headers());
  }

  static Future<bool> resetFlashcardProgress(int setId) async {
    try {
      final res = await http.post(
        Uri.parse('$baseUrl/student/flashcards/$setId/reset'),
        headers: await _headers(),
      );
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  static Future<Map<String, dynamic>> getDailyQuran() async {
    final res = await http.get(Uri.parse('$baseUrl/student/daily-quran'),
        headers: await _headers());
    return jsonDecode(res.body);
  }

  static Future<Map<String, dynamic>?> getDiagnosis() async {
    try {
      final res = await http.get(Uri.parse('$baseUrl/student/diagnosis'),
          headers: await _headers());
      if (res.statusCode != 200) return null;
      return jsonDecode(res.body);
    } catch (_) {
      return null;
    }
  }

  static Future<Map<String, dynamic>?> storeDiagnosis(Map<String, dynamic> answers) async {
    try {
      final res = await http.post(
        Uri.parse('$baseUrl/student/diagnosis'),
        headers: await _headers(),
        body: jsonEncode(answers),
      );
      if (res.statusCode != 200) return null;
      return jsonDecode(res.body);
    } catch (_) {
      return null;
    }
  }

  static Future<bool> resetDiagnosis() async {
    try {
      final res = await http.post(
        Uri.parse('$baseUrl/student/diagnosis/reset'),
        headers: await _headers(),
      );
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  static Future<Map<String, dynamic>> getQuranByMood(String mood) async {
    try {
      final res = await http.get(
        Uri.parse('$baseUrl/student/quran-mood?mood=$mood'),
        headers: await _headers(),
      );
      if (res.statusCode != 200) return {};
      final decoded = jsonDecode(res.body);
      return (decoded is Map<String, dynamic>) ? decoded : {};
    } catch (_) {
      return {};
    }
  }

  static Future<List<dynamic>> getNoteFolders() async {
    try {
      final res = await http.get(Uri.parse('$baseUrl/student/notes/folders'),
          headers: await _headers());
      if (res.statusCode != 200) return [];
      final decoded = jsonDecode(res.body);
      return (decoded is List) ? decoded : [];
    } catch (_) {
      return [];
    }
  }

  static Future<List<dynamic>> getFolderNotes(String topic) async {
    try {
      final res = await http.get(Uri.parse('$baseUrl/student/notes/folder/${Uri.encodeComponent(topic)}'),
          headers: await _headers());
      if (res.statusCode != 200) return [];
      final decoded = jsonDecode(res.body);
      return (decoded is List) ? decoded : [];
    } catch (_) {
      return [];
    }
  }

  static Future<Map<String, dynamic>?> getResourceNote(String type, int id) async {
    try {
      final res = await http.get(Uri.parse('$baseUrl/student/notes/resource-note?resource_type=$type&resource_id=$id'),
          headers: await _headers());
      if (res.statusCode != 200) return null;
      return jsonDecode(res.body);
    } catch (_) {
      return null;
    }
  }

  static Future<Map<String, dynamic>> saveNote({
    required String topic,
    required String title,
    required String content,
    String? resourceType,
    int? resourceId,
  }) async {
    try {
      final Map<String, dynamic> body = {
        'topic': topic,
        'title': title,
        'content': content,
      };
      if (resourceType != null) body['resource_type'] = resourceType;
      if (resourceId != null) body['resource_id'] = resourceId;

      final res = await http.post(
        Uri.parse('$baseUrl/student/notes/save'),
        headers: await _headers(),
        body: jsonEncode(body),
      );
      final decoded = jsonDecode(res.body);
      return (decoded is Map<String, dynamic>) ? decoded : {};
    } catch (_) {
      return {};
    }
  }

  static Future<bool> deleteNote(int id) async {
    try {
      final res = await http.delete(Uri.parse('$baseUrl/student/notes/$id'),
          headers: await _headers());
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }
}
