import 'package:flutter_tts/flutter_tts.dart';

class TtsService {
  static final FlutterTts _flutterTts = FlutterTts();
  static bool _isInitialized = false;

  static Future<void> _init() async {
    if (_isInitialized) return;
    try {
      await _flutterTts.setLanguage("ms-MY");
      await _flutterTts.setSpeechRate(0.5);
      await _flutterTts.setVolume(1.0);
      await _flutterTts.setPitch(1.0);
      _isInitialized = true;
    } catch (_) {}
  }

  static Future<void> speak(String text) async {
    if (text.trim().isEmpty) return;
    await stop();
    await _init();
    try {
      // Stripping markdown or numbers if needed, matching the web implementation
      String cleanText = text.replaceAll(RegExp(r'^(?:\d+\.)\s+'), '');
      await _flutterTts.speak(cleanText);
    } catch (_) {}
  }

  static Future<void> stop() async {
    try {
      await _flutterTts.stop();
    } catch (_) {}
  }
}
