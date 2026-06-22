import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../services/api_service.dart';
import '../app/theme.dart';

class LearningStyleScreen extends StatefulWidget {
  const LearningStyleScreen({super.key});

  @override
  State<LearningStyleScreen> createState() => _LearningStyleScreenState();
}

class _LearningStyleScreenState extends State<LearningStyleScreen> {
  // All answers start as null → nothing pre-selected
  final Map<String, String?> _answers = {
    'q1': null,
    'q2': null,
    'q3': null,
    'q4': null,
    'q5': null,
  };
  bool _loading = false;

  bool get _allAnswered => _answers.values.every((v) => v != null);

  Future<void> _submit() async {
    if (!_allAnswered) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please answer all 5 sections before submitting.'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    setState(() => _loading = true);
    final res = await ApiService.storeDiagnosis(
      _answers.map((k, v) => MapEntry(k, v!)),
    );
    setState(() => _loading = false);

    if (res != null && mounted) {
      context.go('/learning-profile');
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Failed to save assessment. Please try again.')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Learning Style Assessment'),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Complete this assessment to determine your unique learning profile.',
                    style: TextStyle(fontSize: 16, color: Colors.grey),
                  ),
                  const SizedBox(height: 30),

                  _sectionTitle('1. Information Absorption'),
                  _questionText('How do you prefer to absorb new information?'),
                  _radioOption('q1', 'auditory', 'Listening to explanations or reading aloud'),
                  _radioOption('q1', 'visual', 'Looking at pictures, diagrams, or color-coded notes'),
                  _radioOption('q1', 'competitive', 'Testing myself against a timer or score'),

                  const SizedBox(height: 24),
                  _sectionTitle('2. Study Engagement'),
                  _questionText('When studying for an exam, what keeps you engaged?'),
                  _radioOption('q2', 'auditory', 'Discussing topics or repeating facts verbally'),
                  _radioOption('q2', 'visual', 'Highlighting keywords and drawing mind maps'),
                  _radioOption('q2', 'competitive', 'Comparing progress and aiming for top rank'),

                  const SizedBox(height: 24),
                  _sectionTitle('3. Material Preferences'),
                  _questionText('What type of study tool do you find most helpful?'),
                  _radioOption('q3', 'auditory', 'Audio recordings or materials I can listen to'),
                  _radioOption('q3', 'visual', 'Flashcards with visual cues and structured texts'),
                  _radioOption('q3', 'competitive', 'Interactive quizzes with points and timers'),

                  const SizedBox(height: 24),
                  _sectionTitle('4. Learning Pacing'),
                  _questionText('How quickly do you grasp new and complex concepts?'),
                  _radioOption('q4', 'slow', 'I need time to digest information at my own pace'),
                  _radioOption('q4', 'fast', 'I pick up new concepts quickly and move on fast'),

                  const SizedBox(height: 24),
                  _sectionTitle('5. Assessment Pacing'),
                  _questionText('When taking a quiz, how do you handle the time limit?'),
                  _radioOption('q5', 'slow', 'I take my time to carefully read every option'),
                  _radioOption('q5', 'fast', 'I answer quickly and rely on my first instinct'),

                  const SizedBox(height: 12),

                  // Progress indicator
                  Row(
                    children: [
                      Expanded(
                        child: LinearProgressIndicator(
                          value: _answers.values.where((v) => v != null).length / 5,
                          color: AppTheme.primary,
                          backgroundColor: Colors.grey.shade200,
                          minHeight: 6,
                          borderRadius: BorderRadius.circular(4),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Text(
                        '${_answers.values.where((v) => v != null).length}/5',
                        style: TextStyle(
                          color: _allAnswered ? AppTheme.primary : Colors.grey,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 28),
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor:
                            _allAnswered ? AppTheme.primary : Colors.grey.shade400,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(25)),
                      ),
                      onPressed: _submit,
                      child: const Text('Submit Assessment',
                          style: TextStyle(
                              fontSize: 16, fontWeight: FontWeight.bold)),
                    ),
                  ),
                  const SizedBox(height: 20),
                ],
              ),
            ),
    );
  }

  Widget _sectionTitle(String title) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Text(
        title,
        style: TextStyle(
          fontSize: 14,
          fontWeight: FontWeight.bold,
          color: AppTheme.primary.withOpacity(0.8),
          letterSpacing: 1.1,
        ),
      ),
    );
  }

  Widget _questionText(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Text(
        text,
        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
      ),
    );
  }

  Widget _radioOption(String questionKey, String value, String label) {
    final isSelected = _answers[questionKey] == value;
    return GestureDetector(
      onTap: () => setState(() => _answers[questionKey] = value),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: BoxDecoration(
          color: isSelected
              ? AppTheme.primary.withOpacity(0.05)
              : Colors.grey.withOpacity(0.05),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isSelected ? AppTheme.primary : Colors.grey.shade200,
            width: isSelected ? 2 : 1,
          ),
        ),
        child: Row(
          children: [
            Icon(
              isSelected ? Icons.check_circle : Icons.circle_outlined,
              color: isSelected ? AppTheme.primary : Colors.grey.shade400,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                label,
                style: TextStyle(
                  fontSize: 15,
                  fontWeight:
                      isSelected ? FontWeight.bold : FontWeight.normal,
                  color: isSelected ? AppTheme.primary : Colors.black87,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
