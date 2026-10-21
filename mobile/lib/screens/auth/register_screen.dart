import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../core/constants.dart';
import '../../providers/auth_provider.dart';
import '../../providers/property_provider.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_text_field.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();

  String _role = AppConstants.roleTenant;
  bool _isStudent = false;

  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _whatsappController = TextEditingController();
  final _passwordController = TextEditingController();
  final _passwordConfirmController = TextEditingController();

  // Student fields
  final _studentRegController = TextEditingController();
  final _customInstController = TextEditingController();
  int? _selectedInstitutionId;

  // Owner fields
  final _businessNameController = TextEditingController();
  final _idNumberController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<PropertyProvider>(context, listen: false).fetchMetadata();
    });
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _whatsappController.dispose();
    _passwordController.dispose();
    _passwordConfirmController.dispose();
    _studentRegController.dispose();
    _customInstController.dispose();
    _businessNameController.dispose();
    _idNumberController.dispose();
    super.dispose();
  }

  Future<void> _handleRegister() async {
    if (!_formKey.currentState!.validate()) return;

    final auth = Provider.of<AuthProvider>(context, listen: false);
    final success = await auth.register(
      name: _nameController.text.trim(),
      email: _emailController.text.trim(),
      password: _passwordController.text,
      passwordConfirmation: _passwordConfirmController.text,
      role: _role,
      phone: _phoneController.text.trim(),
      whatsapp: _whatsappController.text.trim(),
      isStudent: _isStudent,
      studentRegNumber: _studentRegController.text.trim(),
      institutionId: _selectedInstitutionId,
      customInstitutionName: _customInstController.text.trim(),
      businessName: _businessNameController.text.trim(),
      idNumber: _idNumberController.text.trim(),
    );

    if (!mounted) return;

    if (success && auth.currentUser != null) {
      if (_role == AppConstants.roleOwner) {
        Navigator.pushReplacementNamed(context, '/owner/dashboard');
      } else {
        Navigator.pushReplacementNamed(context, '/marketplace');
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(auth.errorMessage ?? 'Registration failed.'),
          backgroundColor: AppTheme.error,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final propertyProv = Provider.of<PropertyProvider>(context);
    final auth = Provider.of<AuthProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Create Account'),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Join SmartNest',
                  style: GoogleFonts.outfit(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: isDark ? AppTheme.textMainDark : AppTheme.textMainLight,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'Choose your account type to get started',
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight,
                  ),
                ),
                const SizedBox(height: 20),

                // Role Toggle (Tenant vs Property Owner)
                Container(
                  padding: const EdgeInsets.all(4),
                  decoration: BoxDecoration(
                    color: isDark ? AppTheme.cardDark : AppTheme.borderLight,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: GestureDetector(
                          onTap: () => setState(() => _role = AppConstants.roleTenant),
                          child: Container(
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            decoration: BoxDecoration(
                              color: _role == AppConstants.roleTenant
                                  ? AppTheme.primary
                                  : Colors.transparent,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Center(
                              child: Text(
                                'Tenant / Student',
                                style: GoogleFonts.inter(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                  color: _role == AppConstants.roleTenant
                                      ? Colors.white
                                      : (isDark ? AppTheme.textMutedDark : AppTheme.textMainLight),
                                ),
                              ),
                            ),
                          ),
                        ),
                      ),
                      Expanded(
                        child: GestureDetector(
                          onTap: () => setState(() => _role = AppConstants.roleOwner),
                          child: Container(
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            decoration: BoxDecoration(
                              color: _role == AppConstants.roleOwner
                                  ? AppTheme.primary
                                  : Colors.transparent,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: Center(
                              child: Text(
                                'Property Owner',
                                style: GoogleFonts.inter(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                  color: _role == AppConstants.roleOwner
                                      ? Colors.white
                                      : (isDark ? AppTheme.textMutedDark : AppTheme.textMainLight),
                                ),
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),

                // Full Name
                AppTextField(
                  label: 'Full Name',
                  hint: 'John Doe',
                  controller: _nameController,
                  prefixIcon: const Icon(Icons.person_outline, size: 20),
                  validator: (val) => val == null || val.trim().isEmpty ? 'Full name is required' : null,
                ),
                const SizedBox(height: 16),

                // Email
                AppTextField(
                  label: 'Email Address',
                  hint: 'you@example.com',
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
                  prefixIcon: const Icon(Icons.email_outlined, size: 20),
                  validator: (val) {
                    if (val == null || val.trim().isEmpty) return 'Email is required';
                    if (!val.contains('@')) return 'Enter a valid email';
                    return null;
                  },
                ),
                const SizedBox(height: 16),

                // Phone
                AppTextField(
                  label: 'Phone Number',
                  hint: '+255 700 000 000',
                  controller: _phoneController,
                  keyboardType: TextInputType.phone,
                  prefixIcon: const Icon(Icons.phone_outlined, size: 20),
                  validator: (val) => val == null || val.trim().isEmpty ? 'Phone number is required' : null,
                ),
                const SizedBox(height: 16),

                // Conditional fields for Tenant: Student Toggle
                if (_role == AppConstants.roleTenant) ...[
                  SwitchListTile(
                    contentPadding: EdgeInsets.zero,
                    title: Text(
                      'Are you a College / University Student?',
                      style: GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.w500),
                    ),
                    value: _isStudent,
                    activeColor: AppTheme.primary,
                    onChanged: (val) => setState(() => _isStudent = val),
                  ),
                  if (_isStudent) ...[
                    const SizedBox(height: 8),
                    AppTextField(
                      label: 'Student Registration Number',
                      hint: 'REG/2026/001',
                      controller: _studentRegController,
                    ),
                    const SizedBox(height: 16),
                    DropdownButtonFormField<int>(
                      decoration: const InputDecoration(labelText: 'Select Institution / University'),
                      value: _selectedInstitutionId,
                      items: [
                        ...propertyProv.institutions.map((inst) {
                          return DropdownMenuItem<int>(
                            value: inst['id'] as int,
                            child: Text(inst['name'].toString()),
                          );
                        }),
                      ],
                      onChanged: (val) => setState(() => _selectedInstitutionId = val),
                    ),
                    const SizedBox(height: 16),
                  ],
                ],

                // Conditional fields for Owner
                if (_role == AppConstants.roleOwner) ...[
                  AppTextField(
                    label: 'Business / Agency Name',
                    hint: 'Alpha Estates Ltd',
                    controller: _businessNameController,
                  ),
                  const SizedBox(height: 16),
                  AppTextField(
                    label: 'National ID / Tax Number',
                    hint: '19900101-12345-00001-20',
                    controller: _idNumberController,
                  ),
                  const SizedBox(height: 16),
                ],

                // Password
                AppTextField(
                  label: 'Password',
                  hint: '••••••••',
                  controller: _passwordController,
                  obscureText: true,
                  prefixIcon: const Icon(Icons.lock_outline, size: 20),
                  validator: (val) {
                    if (val == null || val.isEmpty) return 'Password is required';
                    if (val.length < 6) return 'At least 6 characters required';
                    return null;
                  },
                ),
                const SizedBox(height: 16),

                // Confirm Password
                AppTextField(
                  label: 'Confirm Password',
                  hint: '••••••••',
                  controller: _passwordConfirmController,
                  obscureText: true,
                  prefixIcon: const Icon(Icons.lock_outline, size: 20),
                  validator: (val) {
                    if (val != _passwordController.text) return 'Passwords do not match';
                    return null;
                  },
                ),
                const SizedBox(height: 28),

                // Register Button
                AppButton(
                  text: 'Create Account',
                  onPressed: _handleRegister,
                  isLoading: auth.status == AuthStatus.authenticating,
                ),
                const SizedBox(height: 24),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
