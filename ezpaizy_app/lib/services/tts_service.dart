import 'package:flutter_tts/flutter_tts.dart';

class TtsService {
  static final FlutterTts _flutterTts = FlutterTts();
  static bool _isInitialized = false;

  static Future<void> _init() async {
    print("TTS: Initializing TtsService...");
    try {
      // Check if ms-MY (Malay) is available
      final isMalayAvailable = await _flutterTts.isLanguageAvailable("ms-MY");
      print("TTS: ms-MY availability: $isMalayAvailable");
      
      if (isMalayAvailable == true) {
        await _flutterTts.setLanguage("ms-MY");
        print("TTS: Set language to ms-MY");
      } else {
        // Fallback to id-ID (Indonesian), which sounds very close and is standard on Google TTS
        final isIndoAvailable = await _flutterTts.isLanguageAvailable("id-ID");
        print("TTS: id-ID availability: $isIndoAvailable");
        if (isIndoAvailable == true) {
          await _flutterTts.setLanguage("id-ID");
          print("TTS: Fallback to id-ID (Malay pronunciation compatible)");
        } else {
          await _flutterTts.setLanguage("en-US");
          print("TTS: Fallback to en-US (no Malay/Indonesian engine available)");
        }
      }
      
      await _flutterTts.setSpeechRate(0.5);
      await _flutterTts.setVolume(1.0);
      await _flutterTts.setPitch(1.0);
      _isInitialized = true;
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
