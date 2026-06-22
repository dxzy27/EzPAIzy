import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  bool _obscure = true;
  bool _keepSignedIn = false;

  Future<void> _login() async {
    final auth = context.read<AuthProvider>();
    final ok = await auth.login(_emailCtrl.text.trim(), _passCtrl.text.trim());
    if (ok && mounted) context.go('/dashboard');
  }

  @override
  void dispose() {
    _emailCtrl.dispose();
    _passCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 24),
            child: Center(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 420),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    // ── Avatar ──────────────────────────────────────────
                    SizedBox(
                      height: 220,
                      child: Image.asset(
                        'assets/images/student_avatar.png',
                        fit: BoxFit.contain,
                        errorBuilder: (_, _, _) => Container(
                          width: 90,
                          height: 90,
                          decoration: BoxDecoration(
                            color: const Color(0xFFEEF4FF),
                            shape: BoxShape.circle,
                            boxShadow: [
                              BoxShadow(
                                color: Colors.blue.withOpacity(0.15),
                                blurRadius: 20,
                                offset: const Offset(0, 6),
                              ),
                            ],
                          ),
                          child: const Center(
                            child: Text('🧑‍🎓',
                                style: TextStyle(fontSize: 46)),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 22),

                    // ── Title ────────────────────────────────────────────
                    const Text(
                      'Sign in',
                      style: TextStyle(
                        fontSize: 26,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF1A1A2E),
                        letterSpacing: 0.2,
                      ),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'to continue to your account',
                      style: TextStyle(
                        fontSize: 13,
                        color: Color(0xFF9E9E9E),
                      ),
                    ),
                    const SizedBox(height: 36),

                    // ── Email ────────────────────────────────────────────
                    _UnderlineField(
                      controller: _emailCtrl,
                      label: 'Email',
                      keyboardType: TextInputType.emailAddress,
                    ),
                    const SizedBox(height: 20),

                    // ── Password ─────────────────────────────────────────
                    _UnderlineField(
                      controller: _passCtrl,
                      label: 'Password',
                      obscure: _obscure,
                      suffix: IconButton(
                        icon: Icon(
                          _obscure ? Icons.remove_red_eye_outlined : Icons.visibility_off_outlined,
                          color: const Color(0xFFBDBDBD),
                          size: 20,
                        ),
                        onPressed: () => setState(() => _obscure = !_obscure),
                      ),
                    ),
                    const SizedBox(height: 20),

                    // ── Keep me signed in ────────────────────────────────
                    Row(
                      children: [
                        SizedBox(
                          width: 20,
                          height: 20,
                          child: Checkbox(
                            value: _keepSignedIn,
                            onChanged: (v) => setState(() => _keepSignedIn = v ?? false),
                            activeColor: const Color(0xFF4285F4),
                            side: const BorderSide(color: Color(0xFFBDBDBD)),
                            shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(3)),
                          ),
                        ),
                        const SizedBox(width: 10),
                        const Text(
                          'Keep me signed in',
                          style: TextStyle(
                            fontSize: 13,
                            color: Color(0xFF4285F4),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 28),

                    // ── Error message ────────────────────────────────────
                    if (auth.error != null) ...[
                      Container(
                        padding:
                            const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                        decoration: BoxDecoration(
                          color: Colors.red.shade50,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: Colors.red.shade200),
                        ),
                        child: Row(
                          children: [
                            Icon(Icons.error_outline,
                                color: Colors.red.shade400, size: 16),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                auth.error!,
                                style: TextStyle(
                                    color: Colors.red.shade600, fontSize: 13),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),
                    ],

                    // ── Sign In Button ───────────────────────────────────
                    SizedBox(
                      width: double.infinity,
                      height: 50,
                      child: ElevatedButton(
                        onPressed: auth.isLoading ? null : _login,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF4285F4),
                          foregroundColor: Colors.white,
                          elevation: 0,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                        child: auth.isLoading
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(
                                    color: Colors.white, strokeWidth: 2.5),
                              )
                            : const Text(
                                'SIGN IN',
                                style: TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.bold,
                                  letterSpacing: 1.2,
                                ),
                              ),
                      ),
                    ),
                    const SizedBox(height: 24),

                    // ── Create account ───────────────────────────────────
                    RichText(
                      text: const TextSpan(
                        text: "Don't have an account? ",
                        style: TextStyle(fontSize: 13, color: Color(0xFF9E9E9E)),
                        children: [
                          TextSpan(
                            text: 'Create account',
                            style: TextStyle(
                              color: Color(0xFF4285F4),
                              fontWeight: FontWeight.w600,
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

class _UnderlineField extends StatelessWidget {
  final TextEditingController controller;
  final String label;
  final bool obscure;
  final TextInputType? keyboardType;
  final Widget? suffix;

  const _UnderlineField({
    required this.controller,
    required this.label,
    this.obscure = false,
    this.keyboardType,
    this.suffix,
  });

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: controller,
      obscureText: obscure,
      keyboardType: keyboardType,
      style: const TextStyle(fontSize: 14, color: Color(0xFF1A1A2E)),
      decoration: InputDecoration(
        labelText: label,
        labelStyle: const TextStyle(
          fontSize: 13,
          color: Color(0xFF4285F4),
          fontWeight: FontWeight.w500,
        ),
        suffixIcon: suffix,
        enabledBorder: const UnderlineInputBorder(
          borderSide: BorderSide(color: Color(0xFFE0E0E0)),
        ),
        focusedBorder: const UnderlineInputBorder(
          borderSide: BorderSide(color: Color(0xFF4285F4), width: 1.5),
        ),
        contentPadding:
            const EdgeInsets.symmetric(vertical: 10, horizontal: 0),
      ),
    );
  }
}
