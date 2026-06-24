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
  final _passCtrl = TextEditingController();
  final _confirmPassCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _addressCtrl = TextEditingController();
  
  String _className = '5A1';
  bool _obscurePass = true;
  bool _obscureConfirmPass = true;
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _passCtrl.dispose();
    _confirmPassCtrl.dispose();
    _phoneCtrl.dispose();
    _addressCtrl.dispose();
    super.dispose();
  }

  Future<void> _register() async {
    final name = _nameCtrl.text.trim();
    final email = _emailCtrl.text.trim();
    final password = _passCtrl.text;
    final confirmPassword = _confirmPassCtrl.text;
    final phone = _phoneCtrl.text.trim();
    final address = _addressCtrl.text.trim();

    if (name.isEmpty || email.isEmpty || password.isEmpty || confirmPassword.isEmpty || phone.isEmpty || address.isEmpty) {
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
        password: password,
        passwordConfirmation: confirmPassword,
        phoneNumber: phone,
        address: address,
        className: _className,
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
                'Your account has been created successfully. You can now log in to the application.',
                style: TextStyle(fontSize: 14),
              ),
              actions: [
                TextButton(
                  onPressed: () {
                    Navigator.of(ctx).pop();
                    context.go('/login');
                  },
                  child: const Text('Go to Login', style: TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF6D28D9))),
                ),
              ],
            ),
          );
        }
      } else {
        // Parse validation errors
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Create Account'),
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF1E293B),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Color(0xFF64748B)),
          onPressed: () => context.go('/login'),
        ),
      ),
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [
              Color(0xFFFAF5FF),
              Color(0xFFF3E8FF),
              Color(0xFFEDE9FE),
            ],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 440),
                child: Column(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 32),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(20),
                        border: const Border(
                          top: BorderSide(
                            color: Color(0xFF8B5CF6), // Violet top stripe
                            width: 5,
                          ),
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: const Color(0xFF8B5CF6).withOpacity(0.15),
                            blurRadius: 40,
                            offset: const Offset(0, 16),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Badge: STUDENT SIGN UP
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF5F3FF),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: const Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.person_add, color: Color(0xFF7C3AED), size: 13),
                                SizedBox(width: 4),
                                Text(
                                  'STUDENT SIGN UP',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    color: Color(0xFF7C3AED),
                                    letterSpacing: 0.5,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 18),

                          const Text(
                            'Get Started',
                            style: TextStyle(
                              fontSize: 26,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF1E293B),
                              fontFamily: 'Outfit',
                            ),
                          ),
                          const SizedBox(height: 4),
                          const Text(
                            'Enter your details to register as a student.',
                            style: TextStyle(
                              fontSize: 13.5,
                              color: Color(0xFF64748B),
                            ),
                          ),
                          const SizedBox(height: 24),

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
                            hintText: 'Address',
                            prefixIcon: Icons.home_outlined,
                            keyboardType: TextInputType.streetAddress,
                          ),
                          const SizedBox(height: 16),

                          // Class Name dropdown
                          _CustomDropdownField(
                            value: _className,
                            items: const ['5A1', '5A2', '5A3', '5B1', '5B2', '5B3'],
                            prefixIcon: Icons.class_outlined,
                            onChanged: (val) {
                              if (val != null) {
                                setState(() => _className = val);
                              }
                            },
                          ),
                          const SizedBox(height: 16),

                          // Password field
                          _CustomInputField(
                            controller: _passCtrl,
                            hintText: 'Password',
                            prefixIcon: Icons.lock_outline,
                            obscure: _obscurePass,
                            suffixIcon: GestureDetector(
                              onTap: () => setState(() => _obscurePass = !_obscurePass),
                              child: Icon(
                                _obscurePass ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                                color: const Color(0xFF94A3B8),
                                size: 20,
                              ),
                            ),
                          ),
                          const SizedBox(height: 16),

                          // Confirm Password field
                          _CustomInputField(
                            controller: _confirmPassCtrl,
                            hintText: 'Confirm Password',
                            prefixIcon: Icons.lock_clock_outlined,
                            obscure: _obscureConfirmPass,
                            suffixIcon: GestureDetector(
                              onTap: () => setState(() => _obscureConfirmPass = !_obscureConfirmPass),
                              child: Icon(
                                _obscureConfirmPass ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                                color: const Color(0xFF94A3B8),
                                size: 20,
                              ),
                            ),
                          ),
                          const SizedBox(height: 20),

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

                          // Sign Up button
                          SizedBox(
                            width: double.infinity,
                            height: 50,
                            child: ElevatedButton(
                              onPressed: _isLoading ? null : _register,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF7C3AED),
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
                                          'SIGN UP',
                                          style: TextStyle(
                                            fontSize: 15,
                                            fontWeight: FontWeight.bold,
                                            letterSpacing: 0.5,
                                          ),
                                        ),
                                        Spacer(),
                                        Icon(Icons.arrow_forward, size: 18),
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
                                    fontSize: 13.5,
                                    color: Color(0xFF64748B),
                                    fontFamily: 'Outfit',
                                  ),
                                  children: [
                                    TextSpan(
                                      text: 'Sign In',
                                      style: TextStyle(
                                        color: Color(0xFF7C3AED),
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
                    ),
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
  final Widget? suffixIcon;
  final TextInputType? keyboardType;

  const _CustomInputField({
    required this.controller,
    required this.hintText,
    required this.prefixIcon,
    this.obscure = false,
    this.suffixIcon,
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
        style: const TextStyle(fontSize: 15, color: Color(0xFF334155)),
        decoration: InputDecoration(
          hintText: hintText,
          hintStyle: const TextStyle(color: Color(0xFF94A3B8), fontSize: 15),
          prefixIcon: Icon(prefixIcon, color: const Color(0xFF94A3B8), size: 20),
          suffixIcon: suffixIcon,
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
        ),
      ),
    );
  }
}

class _CustomDropdownField extends StatelessWidget {
  final String value;
  final List<String> items;
  final IconData prefixIcon;
  final ValueChanged<String?> onChanged;

  const _CustomDropdownField({
    required this.value,
    required this.items,
    required this.prefixIcon,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 2),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFE2E8F0), width: 1.5),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<String>(
          value: value,
          isExpanded: true,
          icon: const Icon(Icons.arrow_drop_down, color: Color(0xFF94A3B8)),
          onChanged: onChanged,
          items: items.map<DropdownMenuItem<String>>((String val) {
            return DropdownMenuItem<String>(
              value: val,
              child: Row(
                children: [
                  Icon(prefixIcon, color: const Color(0xFF94A3B8), size: 20),
                  const SizedBox(width: 12),
                  Text(
                    val,
                    style: const TextStyle(fontSize: 15, color: Color(0xFF334155)),
                  ),
                ],
              ),
            );
          }).toList(),
        ),
      ),
    );
  }
}
