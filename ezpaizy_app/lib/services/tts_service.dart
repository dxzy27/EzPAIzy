import 'package:flutter_tts/flutter_tts.dart';

class TtsService {
  static final FlutterTts _flutterTts = FlutterTts();

  static Future<void> _init() async {
    print("TTS: Initializing TtsService...");
    try {
      // Directly attempt to set Malay language, checking if return value is 1 (success on Android/iOS) or true
      final malayResult = await _flutterTts.setLanguage("ms-MY");
      print("TTS: setLanguage ms-MY result: $malayResult");
      
      if (malayResult == 1 || malayResult == true) {
        print("TTS: Set language to ms-MY successfully");
      } else {
        // Fallback to id-ID (Indonesian), which sounds very close and is standard on Google TTS
        final indoResult = await _flutterTts.setLanguage("id-ID");
        print("TTS: setLanguage id-ID result: $indoResult");
        if (indoResult == 1 || indoResult == true) {
          print("TTS: Set language to id-ID successfully (Malay fallback)");
        } else {
          await _flutterTts.setLanguage("en-US");
          print("TTS: Fallback to en-US (no Malay/Indonesian engine available)");
        }
      }
      
      await _flutterTts.setSpeechRate(0.5);
      await _flutterTts.setVolume(1.0);
      await _flutterTts.setPitch(1.0);
      print("TTS: Initialized successfully");
    } catch (e) {
      print("TTS: Initialization failed with error: $e");
    }
  }

  static Future<void> speak(String text) async {
    print("TTS: speak requested for: '$text'");
    if (text.trim().isEmpty) return;
    await stop();
    await _init();
    try {
      String cleanText = text.replaceAll(RegExp(r'^(?:\d+\.)\s+'), '');
      print("TTS: speaking clean text: '$cleanText'");
      final result = await _flutterTts.speak(cleanText);
      print("TTS: speak result: $result");
    } catch (e) {
      print("TTS: speak failed with error: $e");
    }
  }

  static Future<void> stop() async {
    print("TTS: stop requested");
    try {
      await _flutterTts.stop();
    } catch (e) {
      print("TTS: stop failed: $e");
    }
  }
}
