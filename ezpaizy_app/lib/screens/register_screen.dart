import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../services/api_service.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _nameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _addressCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _confirmPassCtrl = TextEditingController();
  
  String? _role;
  String? _className;
  List<String> _classes = [];
  bool _isFetchingClasses = true;
  
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _phoneCtrl.dispose();
    _addressCtrl.dispose();
    _passCtrl.dispose();
    _confirmPassCtrl.dispose();
    super.dispose();
  }

  @override
  void initState() {
    super.initState();
    _fetchClasses();
  }

  Future<void> _fetchClasses() async {
    final classes = await ApiService.getClasses();
    if (mounted) {
      setState(() {
        _classes = classes;
        _isFetchingClasses = false;
      });
    }
  }

  Future<void> _register() async {
    final name = _nameCtrl.text.trim();
    final email = _emailCtrl.text.trim();
    final phone = _phoneCtrl.text.trim();
    final address = _addressCtrl.text.trim();
    final password = _passCtrl.text;
    final confirmPassword = _confirmPassCtrl.text;

    if (name.isEmpty || email.isEmpty || phone.isEmpty || address.isEmpty || _role == null || _className == null || password.isEmpty || confirmPassword.isEmpty) {
      setState(() {
        _errorMessage = 'All fields are required.';
      });
      return;
    }

    if (!email.contains('@')) {
      setState(() {
        _errorMessage = 'Please enter a valid email address.';
      });
      return;
    }

    if (password.length < 8) {
      setState(() {
        _errorMessage = 'Password must be at least 8 characters long.';
      });
      return;
    }

    if (password != confirmPassword) {
      setState(() {
        _errorMessage = 'Passwords do not match.';
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final res = await ApiService.register(
        name: name,
        email: email,
        phoneNumber: phone,
        address: address,
        role: _role!,
        className: _className!,
        password: password,
        passwordConfirmation: confirmPassword,
      );

      if (res['success'] == true) {
        if (mounted) {
          showDialog(
            context: context,
            barrierDismissible: false,
            builder: (ctx) => AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: const Row(
                children: [
                  Icon(Icons.check_circle, color: Colors.green, size: 28),
                  SizedBox(width: 8),
                  Text('Success! 🎉', style: TextStyle(fontWeight: FontWeight.bold)),
                ],
              ),
              content: const Text(
                'Your account has been created successfully. You can now log in.',
                style: TextStyle(fontSize: 14),
              ),
              actions: [
                TextButton(
                  onPressed: () {
                    Navigator.of(ctx).pop();
                    context.go('/login');
                  },
                  child: const Text('Go to Login', style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF14B8A6))),
                ),
              ],
            ),
          );
        }
      } else {
        String errorMsg = res['message'] ?? 'Registration failed.';
        if (res['errors'] != null && res['errors'] is Map) {
          final errs = res['errors'] as Map;
          final messages = <String>[];
          errs.forEach((key, val) {
            if (val is List) {
              messages.addAll(val.map((e) => e.toString()));
            } else {
              messages.add(val.toString());
            }
          });
          if (messages.isNotEmpty) {
            errorMsg = messages.join('\n');
          }
        }
        setState(() {
          _errorMessage = errorMsg;
          _isLoading = false;
        });
      }
    } catch (_) {
      setState(() {
        _errorMessage = 'Connection error. Please try again.';
        _isLoading = false;
      });
    }
  }

  Widget _buildIntroSide() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisSize: MainAxisSize.min,
      children: [
        RichText(
          text: const TextSpan(
            text: 'Learn Smarter with ',
            style: TextStyle(
              fontSize: 38,
              fontWeight: FontWeight.w800,
              color: Colors.white,
              fontFamily: 'Outfit',
              height: 1.15,
            ),
            children: [
              TextSpan(
                text: 'Personalized Islamic Learning',
                style: TextStyle(color: Color(0xFF60A5FA)),
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),
        const Text(
          'Discover learning materials tailored to your learning style, generate AI-powered quizzes, and track your progress in one place.',
          style: TextStyle(
            fontSize: 16,
            color: Colors.white70,
            height: 1.65,
            fontFamily: 'Outfit',
          ),
        ),
        const SizedBox(height: 24),
        Wrap(
          spacing: 12,
          runSpacing: 12,
          children: [
            _buildVarkBadge('👁 Visual', const Color(0xFF06B6D4)),
            _buildVarkBadge('🎧 Auditory', const Color(0xFFF97316)),
            _buildVarkBadge('📖 Read/Write', const Color(0xFFEAB308)),
            _buildVarkBadge('🏃 Kinaesthetic', const Color(0xFFEC4899)),
          ],
        ),
        const SizedBox(height: 32),
        _buildFeatureItem('Personalized learning paths'),
        const SizedBox(height: 12),
        _buildFeatureItem('AI Quiz Generation'),
        const SizedBox(height: 12),
        _buildFeatureItem('Track Your Progress'),
        const SizedBox(height: 32),
        const Text(
          'Start your personalized learning journey today.',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: Color(0xFF60A5FA),
            fontFamily: 'Outfit',
          ),
        ),
      ],
    );
  }

  Widget _buildVarkBadge(String text, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        border: Border.all(color: color.withOpacity(0.3), width: 1),
        borderRadius: BorderRadius.circular(99),
      ),
      child: Text(
        text,
        style: TextStyle(
          color: color,
          fontSize: 13,
          fontWeight: FontWeight.w600,
          fontFamily: 'Outfit',
        ),
      ),
    );
  }

  Widget _buildFeatureItem(String text) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        const Icon(Icons.check_circle, color: Color(0xFF60A5FA), size: 18),
        const SizedBox(width: 10),
        Text(
          text,
          style: const TextStyle(
            fontSize: 15,
            fontWeight: FontWeight.w600,
            color: Colors.white,
            fontFamily: 'Outfit',
          ),
        ),
      ],
    );
  }

  Widget _buildCardSide() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 32),
      decoration: BoxDecoration(
        color: const Color(0xFFFAFBFC),
        borderRadius: BorderRadius.circular(20),
        border: const Border(
          top: BorderSide(
            color: Color(0xFF14B8A6), // Teal accent border top matching web
            width: 5,
          ),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.18),
            blurRadius: 40,
            offset: const Offset(0, 16),
          ),
          BoxShadow(
            color: const Color(0xFF14B8A6).withOpacity(0.15),
            blurRadius: 60,
          ),
        ],
      ),
      child: Column(
        children: [
          // Badge: SIGN UP
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
            decoration: BoxDecoration(
              color: const Color(0xFFF0FDFA), // Light teal background
              borderRadius: BorderRadius.circular(20),
            ),
            child: const Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(Icons.person_add, color: Color(0xFF0D9488), size: 14),
                SizedBox(width: 6),
                Text(
                  'SIGN UP',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF0D9488), // Teal text
                    letterSpacing: 0.5,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // MRSM Logo
          Image.asset(
            'assets/images/mrsm.png',
            height: 70,
            errorBuilder: (context, error, stackTrace) => const SizedBox(),
          ),
          const SizedBox(height: 12),

          // Heading
          RichText(
            textAlign: TextAlign.center,
            text: const TextSpan(
              text: 'Create an ',
              style: TextStyle(
                fontSize: 28,
                fontWeight: FontWeight.w800,
                color: Color(0xFF1E293B),
                fontFamily: 'Outfit',
              ),
              children: [
                TextSpan(
                  text: 'Account',
                  style: TextStyle(color: Color(0xFF0D9488)), // Teal word
                ),
              ],
            ),
          ),
          const SizedBox(height: 6),
          const Text(
            'Join us and start your learning journey',
            style: TextStyle(
              fontSize: 14,
              color: Color(0xFF64748B),
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 32),

          // Name field
          _CustomInputField(
            controller: _nameCtrl,
            hintText: 'Full Name',
            prefixIcon: Icons.person_outline,
            keyboardType: TextInputType.name,
          ),
          const SizedBox(height: 16),

          // Email field
          _CustomInputField(
            controller: _emailCtrl,
            hintText: 'Email Address',
            prefixIcon: Icons.email_outlined,
            keyboardType: TextInputType.emailAddress,
          ),
          const SizedBox(height: 16),

          // Phone Number field
          _CustomInputField(
            controller: _phoneCtrl,
            hintText: 'Phone Number',
            prefixIcon: Icons.phone_outlined,
            keyboardType: TextInputType.phone,
          ),
          const SizedBox(height: 16),

          // Address field
          _CustomInputField(
            controller: _addressCtrl,
            hintText: 'Home Address',
            prefixIcon: Icons.home_outlined,
            keyboardType: TextInputType.streetAddress,
          ),
          const SizedBox(height: 16),

          // Register As dropdown
          _CustomDropdownField(
            value: _role,
            hintText: 'Register As...',
            items: const ['student', 'teacher'],
            displayItems: const ['Student', 'Teacher'],
            prefixIcon: Icons.badge_outlined,
            onChanged: (val) {
              if (val != null) {
                setState(() => _role = val);
              }
            },
          ),
          const SizedBox(height: 16),

          // Class Name dropdown
          _isFetchingClasses
              ? const Center(child: CircularProgressIndicator())
              : _CustomDropdownField(
                  value: _className,
                  hintText: 'Select Class...',
                  items: _classes,
                  displayItems: _classes,
                  prefixIcon: Icons.class_outlined,
                  onChanged: (val) {
                    if (val != null) {
                      setState(() => _className = val);
                    }
                  },
                ),
          const SizedBox(height: 16),

          // Password & Confirm Row
          Row(
            children: [
              Expanded(
                child: _CustomInputField(
                  controller: _passCtrl,
                  hintText: 'Password',
                  prefixIcon: Icons.lock_outline,
                  obscure: true,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _CustomInputField(
                  controller: _confirmPassCtrl,
                  hintText: 'Confirm',
                  prefixIcon: Icons.lock_outline,
                  obscure: true,
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),

          // Error Display
          if (_errorMessage != null) ...[
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: const Color(0xFFFEF2F2),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFFCA5A5)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.error_outline, color: Color(0xFFEF4444), size: 18),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      _errorMessage!,
                      style: const TextStyle(color: Color(0xFFB91C1C), fontSize: 13),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
          ],

          // Submit button
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton(
              onPressed: _isLoading ? null : _register,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF14B8A6), // Teal background matching web
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
              child: _isLoading
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5),
                    )
                  : const Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Spacer(),
                        Text(
                          'CREATE ACCOUNT',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                            letterSpacing: 0.5,
                          ),
                        ),
                        Spacer(),
                        Icon(Icons.arrow_forward, size: 16),
                      ],
                    ),
            ),
          ),

          const SizedBox(height: 24),
          Center(
            child: GestureDetector(
              onTap: () => context.go('/login'),
              child: RichText(
                text: const TextSpan(
                  text: "Already have an account? ",
                  style: TextStyle(
                    fontSize: 13,
                    color: Color(0xFF64748B),
                    fontFamily: 'Outfit',
                  ),
                  children: [
                    TextSpan(
                      text: 'Sign in here',
                      style: TextStyle(
                        color: Color(0xFF0D9488), // Teal redirect link matching web
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isWide = MediaQuery.of(context).size.width > 991;

    return Scaffold(
      extendBodyBehindAppBar: true,
      appBar: AppBar(
        backgroundColor: Colors.white.withOpacity(0.08),
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: false, // Left-aligned brand logo matching web
        titleSpacing: 24,
        shape: Border(
          bottom: BorderSide(
            color: Colors.white.withOpacity(0.12),
            width: 1,
          ),
        ),
        title: Image.asset(
          'assets/images/newlogo.png',
          height: 40,
          errorBuilder: (context, error, stackTrace) => const Text(
            'EzPAIzy',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Colors.white,
              fontFamily: 'Outfit',
            ),
          ),
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 24.0),
            child: Center(
              child: ElevatedButton(
                onPressed: () => context.go('/login'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.white.withOpacity(0.6),
                  foregroundColor: const Color(0xFF1E293B),
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(6),
                    side: BorderSide(color: Colors.white.withOpacity(0.5)),
                  ),
                  padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 8),
                  minimumSize: const Size(0, 0),
                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                ),
                child: const Text(
                  'Log in',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    fontFamily: 'Outfit',
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
          image: DecorationImage(
            image: AssetImage('assets/images/signup_bg.png'), // Match web signup background
            fit: BoxFit.cover,
            opacity: 0.70, // 0.70 overlay opacity matching web
          ),
          gradient: LinearGradient(
            colors: [
              Color(0xFF0C4150),
              Color(0xFF083344),
            ],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 100),
              child: ConstrainedBox(
                constraints: BoxConstraints(maxWidth: isWide ? 1040 : 520),
                child: isWide
                    ? Row(
                        crossAxisAlignment: CrossAxisAlignment.center,
                        children: [
                          Expanded(
                            flex: 11,
                            child: _buildIntroSide(),
                          ),
                          const SizedBox(width: 60),
                          Expanded(
                            flex: 9,
                            child: _buildCardSide(),
                          ),
                        ],
                      )
                    : Column(
                        children: [
                          _buildCardSide(),
                        ],
                      ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _CustomInputField extends StatelessWidget {
  final TextEditingController controller;
  final String hintText;
  final bool obscure;
  final IconData prefixIcon;
  final TextInputType? keyboardType;

  const _CustomInputField({
    required this.controller,
    required this.hintText,
    required this.prefixIcon,
    this.obscure = false,
    this.keyboardType,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFE2E8F0), width: 1.5),
      ),
      child: TextField(
        controller: controller,
        obscureText: obscure,
        keyboardType: keyboardType,
        style: const TextStyle(fontSize: 14, color: Color(0xFF334155)),
        decoration: InputDecoration(
          hintText: hintText,
          hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 14),
          prefixIcon: Icon(prefixIcon, color: const Color(0xFF94A3B8), size: 18),
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(vertical: 12, horizontal: 12),
        ),
      ),
    );
  }
}

class _CustomDropdownField extends StatelessWidget {
  final String? value;
  final String hintText;
  final List<String> items;
  final List<String> displayItems;
  final IconData prefixIcon;
  final ValueChanged<String?> onChanged;

  const _CustomDropdownField({
    required this.value,
    required this.hintText,
    required this.items,
    required this.displayItems,
    required this.prefixIcon,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 0),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFE2E8F0), width: 1.5),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<String>(
          value: value,
          isExpanded: true,
          hint: Row(
            children: [
              Icon(prefixIcon, color: const Color(0xFF94A3B8), size: 18),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  hintText,
                  style: const TextStyle(color: Color(0xFF94A3B8), fontSize: 14),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          icon: const Icon(Icons.arrow_drop_down, color: Color(0xFF94A3B8)),
          onChanged: onChanged,
          items: List.generate(items.length, (index) {
            return DropdownMenuItem<String>(
              value: items[index],
              child: Row(
                children: [
                  Icon(prefixIcon, color: const Color(0xFF94A3B8), size: 18),
                  const SizedBox(width: 8),
                  Text(
                    displayItems[index],
                    style: const TextStyle(fontSize: 14, color: Color(0xFF334155)),
                  ),
                ],
              ),
            );
          }),
        ),
      ),
    );
  }
}
