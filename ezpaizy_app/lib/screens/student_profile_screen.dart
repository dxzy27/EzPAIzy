import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';

class StudentProfileScreen extends StatefulWidget {
  const StudentProfileScreen({super.key});

  @override
  State<StudentProfileScreen> createState() => _StudentProfileScreenState();
}

class _StudentProfileScreenState extends State<StudentProfileScreen> {
  bool _isLoading = true;
  Map<String, dynamic>? _profileData;

  @override
  void initState() {
    super.initState();
    _fetchProfile();
  }

  Future<void> _fetchProfile() async {
    setState(() => _isLoading = true);
    final res = await ApiService.getProfile();
    if (res['success'] == true && res['data'] != null) {
      if (mounted) {
        setState(() {
          _profileData = Map<String, dynamic>.from(res['data']);
          _isLoading = false;
        });
      }
    } else {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _showEditProfileModal() {
    final authUser = Provider.of<AuthProvider>(context, listen: false).user;
    final nameCtrl = TextEditingController(
      text: _profileData?['name'] ?? authUser?['name'] ?? '',
    );
    final emailCtrl = TextEditingController(
      text: _profileData?['email'] ?? authUser?['email'] ?? '',
    );
    final phoneCtrl = TextEditingController(
      text: _profileData?['phone_number'] ?? authUser?['phone_number'] ?? '',
    );
    final addressCtrl = TextEditingController(
      text: _profileData?['address'] ?? authUser?['address'] ?? '',
    );

    bool isSaving = false;

    showDialog(
      context: context,
      builder: (dialogCtx) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Row(
            children: [
              const Icon(Icons.edit_square, color: Color(0xFF3B82F6), size: 22),
              const SizedBox(width: 8),
              Text(
                'Edit Profile',
                style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 18),
              ),
            ],
          ),
          content: SingleChildScrollView(
            child: Container(
              width: 400,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Full Name *', style: GoogleFonts.outfit(fontWeight: FontWeight.w600, fontSize: 12, color: const Color(0xFF64748B))),
                  const SizedBox(height: 4),
                  TextField(
                    controller: nameCtrl,
                    decoration: InputDecoration(
                      isDense: true,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text('Email Address *', style: GoogleFonts.outfit(fontWeight: FontWeight.w600, fontSize: 12, color: const Color(0xFF64748B))),
                  const SizedBox(height: 4),
                  TextField(
                    controller: emailCtrl,
                    decoration: InputDecoration(
                      isDense: true,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text('Phone Number', style: GoogleFonts.outfit(fontWeight: FontWeight.w600, fontSize: 12, color: const Color(0xFF64748B))),
                  const SizedBox(height: 4),
                  TextField(
                    controller: phoneCtrl,
                    decoration: InputDecoration(
                      isDense: true,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text('Address', style: GoogleFonts.outfit(fontWeight: FontWeight.w600, fontSize: 12, color: const Color(0xFF64748B))),
                  const SizedBox(height: 4),
                  TextField(
                    controller: addressCtrl,
                    maxLines: 2,
                    decoration: InputDecoration(
                      isDense: true,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                  ),
                ],
              ),
            ),
          ),
          actions: [
            TextButton(
              onPressed: isSaving ? null : () => Navigator.pop(dialogCtx),
              child: Text('Cancel', style: GoogleFonts.outfit(color: const Color(0xFF64748B))),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF10B981),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
              onPressed: isSaving
                  ? null
                  : () async {
                      setDialogState(() => isSaving = true);
                      final res = await ApiService.updateProfile(
                        name: nameCtrl.text.trim(),
                        email: emailCtrl.text.trim(),
                        phoneNumber: phoneCtrl.text.trim(),
                        address: addressCtrl.text.trim(),
                      );
                      setDialogState(() => isSaving = false);
                      if (res['success'] == true) {
                        Navigator.pop(dialogCtx);
                        _fetchProfile();
                        if (mounted) {
                          ScaffoldMessenger.of(this.context).showSnackBar(
                            const SnackBar(content: Text('Profile updated successfully!')),
                          );
                        }
                      } else {
                        if (mounted) {
                          ScaffoldMessenger.of(this.context).showSnackBar(
                            SnackBar(content: Text(res['message'] ?? 'Failed to update profile')),
                          );
                        }
                      }
                    },
              child: isSaving
                  ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : Text('Save Changes', style: GoogleFonts.outfit(color: Colors.white, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final authUser = Provider.of<AuthProvider>(context).user;

    final name = (_profileData?['name'] ?? authUser?['name'] ?? 'Student').toString();
    final email = (_profileData?['email'] ?? authUser?['email'] ?? 'Not provided').toString();
    final phone = (_profileData?['phone_number'] ?? authUser?['phone_number'] ?? 'Not provided').toString();
    final className = (_profileData?['class_name'] ?? authUser?['class_name'] ?? 'Not assigned').toString();
    final address = (_profileData?['address'] ?? authUser?['address'] ?? 'Not provided').toString();
    final createdAt = (_profileData?['created_at'] ?? 'Jul 19, 2026').toString();
    final initial = name.isNotEmpty ? name[0].toUpperCase() : 'S';

    final stats = _profileData?['stats'] as Map<String, dynamic>?;
    final quizzesTaken = stats?['quizzes_taken'] ?? 0;
    final avgScore = stats?['average_score'] ?? 0;
    final savedMaterials = stats?['saved_materials'] ?? 0;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: SafeArea(
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
                child: Center(
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 800),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Top Navigation Header
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Row(
                              children: [
                                InkWell(
                                  onTap: () {
                                    if (context.canPop()) {
                                      context.pop();
                                    } else {
                                      context.go('/student/dashboard');
                                    }
                                  },
                                  borderRadius: BorderRadius.circular(20),
                                  child: Container(
                                    padding: const EdgeInsets.all(8),
                                    decoration: BoxDecoration(
                                      color: Colors.white,
                                      shape: BoxShape.circle,
                                      border: Border.all(color: const Color(0xFFE2E8F0)),
                                    ),
                                    child: const Icon(Icons.arrow_back, size: 20, color: Color(0xFF334155)),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'My Profile',
                                      style: GoogleFonts.outfit(
                                        fontSize: 22,
                                        fontWeight: FontWeight.bold,
                                        color: const Color(0xFF0F172A),
                                      ),
                                    ),
                                    Text(
                                      'Manage personal details and view account statistics',
                                      style: GoogleFonts.outfit(
                                        fontSize: 12,
                                        color: const Color(0xFF64748B),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                            ElevatedButton.icon(
                              onPressed: _showEditProfileModal,
                              icon: const Icon(Icons.edit_note, size: 18, color: Colors.white),
                              label: Text(
                                'Edit Profile',
                                style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 13, color: Colors.white),
                              ),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFFF97316),
                                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                elevation: 0,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 24),

                        // Main Card Container
                        Container(
                          width: double.infinity,
                          decoration: BoxDecoration(
                            color: const Color(0xFFE2E8F0).withOpacity(0.5),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: const Color(0xFFCBD5E1)),
                          ),
                          padding: const EdgeInsets.all(28),
                          child: Column(
                            children: [
                              // Avatar Circle
                              Container(
                                width: 90,
                                height: 90,
                                decoration: const BoxDecoration(
                                  color: Color(0xFF84CC16),
                                  shape: BoxShape.circle,
                                ),
                                child: Center(
                                  child: Text(
                                    initial,
                                    style: GoogleFonts.outfit(
                                      fontSize: 40,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.white,
                                    ),
                                  ),
                                ),
                              ),
                              const SizedBox(height: 16),

                              // Name & Badge Row
                              Text(
                                name.toUpperCase(),
                                style: GoogleFonts.outfit(
                                  fontSize: 20,
                                  fontWeight: FontWeight.w900,
                                  color: const Color(0xFF0F172A),
                                  letterSpacing: 0.5,
                                ),
                                textAlign: TextAlign.center,
                              ),
                              const SizedBox(height: 8),

                              Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFA7F3D0),
                                      borderRadius: BorderRadius.circular(16),
                                    ),
                                    child: Text(
                                      'Student',
                                      style: GoogleFonts.outfit(
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                        color: const Color(0xFF047857),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    '• Member since $createdAt',
                                    style: GoogleFonts.outfit(
                                      fontSize: 12,
                                      color: const Color(0xFF64748B),
                                    ),
                                  ),
                                ],
                              ),

                              const SizedBox(height: 24),
                              const Divider(color: Color(0xFFCBD5E1), thickness: 1),
                              const SizedBox(height: 24),

                              // Info Grid
                              LayoutBuilder(
                                builder: (context, constraints) {
                                  final isWide = constraints.maxWidth > 500;
                                  return Column(
                                    children: [
                                      Row(
                                        children: [
                                          Expanded(child: _buildInfoItem('FULL NAME', name)),
                                          if (isWide) const SizedBox(width: 24),
                                          Expanded(child: _buildInfoItem('EMAIL ADDRESS', email)),
                                        ],
                                      ),
                                      const SizedBox(height: 20),
                                      Row(
                                        children: [
                                          Expanded(child: _buildInfoItem('PHONE NUMBER', phone.isEmpty ? 'Not provided' : phone)),
                                          if (isWide) const SizedBox(width: 24),
                                          Expanded(child: _buildInfoItem('CLASS', className.isEmpty ? 'Not assigned' : className)),
                                        ],
                                      ),
                                      const SizedBox(height: 20),
                                      _buildInfoItem('ADDRESS', address.isEmpty ? 'Not provided' : address, alignCenter: true),
                                      const SizedBox(height: 20),
                                      Column(
                                        children: [
                                          Text(
                                            'ACCOUNT TYPE',
                                            style: GoogleFonts.outfit(
                                              fontSize: 10,
                                              fontWeight: FontWeight.bold,
                                              color: const Color(0xFF64748B),
                                              letterSpacing: 0.8,
                                            ),
                                          ),
                                          const SizedBox(height: 6),
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                                            decoration: BoxDecoration(
                                              color: const Color(0xFFA7F3D0),
                                              borderRadius: BorderRadius.circular(8),
                                              border: Border.all(color: const Color(0xFF6EE7B7)),
                                            ),
                                            child: Text(
                                              'Student',
                                              style: GoogleFonts.outfit(
                                                fontSize: 12,
                                                fontWeight: FontWeight.bold,
                                                color: const Color(0xFF047857),
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  );
                                },
                              ),

                              const SizedBox(height: 32),

                              // Learning Statistics Card
                              Container(
                                width: double.infinity,
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(16),
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withOpacity(0.03),
                                      blurRadius: 10,
                                      offset: const Offset(0, 4),
                                    ),
                                  ],
                                ),
                                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
                                child: Column(
                                  children: [
                                    Text(
                                      'LEARNING STATISTICS',
                                      style: GoogleFonts.outfit(
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                        color: const Color(0xFF64748B),
                                        letterSpacing: 1.0,
                                      ),
                                    ),
                                    const SizedBox(height: 20),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                                      children: [
                                        _buildStatItem('$quizzesTaken', '📝 Quizzes Taken', const Color(0xFF3B82F6)),
                                        _buildStatItem('$avgScore%', '📊 Average Score', const Color(0xFF10B981)),
                                        _buildStatItem('$savedMaterials', '📚 Saved Materials', const Color(0xFF06B6D4)),
                                      ],
                                    ),
                                  ],
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
    );
  }

  Widget _buildInfoItem(String label, String value, {bool alignCenter = true}) {
    return Column(
      children: [
        Text(
          label,
          style: GoogleFonts.outfit(
            fontSize: 10,
            fontWeight: FontWeight.bold,
            color: const Color(0xFF64748B),
            letterSpacing: 0.8,
          ),
          textAlign: alignCenter ? TextAlign.center : TextAlign.start,
        ),
        const SizedBox(height: 4),
        Text(
          value,
          style: GoogleFonts.outfit(
            fontSize: 14,
            fontWeight: FontWeight.bold,
            color: const Color(0xFF0F172A),
          ),
          textAlign: alignCenter ? TextAlign.center : TextAlign.start,
        ),
      ],
    );
  }

  Widget _buildStatItem(String val, String label, Color valColor) {
    return Column(
      children: [
        Text(
          val,
          style: GoogleFonts.outfit(
            fontSize: 28,
            fontWeight: FontWeight.w900,
            color: valColor,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          style: GoogleFonts.outfit(
            fontSize: 11,
            fontWeight: FontWeight.w600,
            color: const Color(0xFF64748B),
          ),
        ),
      ],
    );
  }
}
