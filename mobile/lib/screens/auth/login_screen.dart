import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../core/api_client.dart';
import '../../core/constants.dart';
import '../../core/storage_service.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_text_field.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController(text: 'tenant@smartnest.com');
  final _passwordController = TextEditingController(text: 'password');
  bool _obscurePassword = true;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _showServerConfigDialog() {
    final urlController = TextEditingController(text: AppConstants.baseUrl);
    bool testing = false;
    bool? testResult;
    String testMessage = '';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Padding(
              padding: EdgeInsets.only(
                left: 20,
                right: 20,
                top: 24,
                bottom: MediaQuery.of(context).viewInsets.bottom + 24,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Backend API Server',
                        style: GoogleFonts.outfit(fontSize: 20, fontWeight: FontWeight.bold),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Configure the Laravel API host URL for local Wi-Fi or production HTTPS.',
                    style: GoogleFonts.inter(fontSize: 13, color: AppTheme.textMutedLight),
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: urlController,
                    decoration: InputDecoration(
                      labelText: 'API Endpoint Base URL',
                      hintText: 'http://192.168.1.145/apartments/public/api/v1',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      prefixIcon: const Icon(Icons.link),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      ActionChip(
                        label: const Text('Local Wi-Fi (192.168.1.145)'),
                        onPressed: () {
                          setModalState(() {
                            urlController.text = 'http://192.168.1.145/apartments/public/api/v1';
                            testResult = null;
                          });
                        },
                      ),
                      ActionChip(
                        label: const Text('Emulator (10.0.2.2)'),
                        onPressed: () {
                          setModalState(() {
                            urlController.text = 'http://10.0.2.2/apartments/public/api/v1';
                            testResult = null;
                          });
                        },
                      ),
                      ActionChip(
                        label: const Text('Production HTTPS'),
                        onPressed: () {
                          setModalState(() {
                            urlController.text = AppConstants.defaultProdUrl;
                            testResult = null;
                          });
                        },
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  if (testResult != null)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      margin: const EdgeInsets.only(bottom: 12),
                      decoration: BoxDecoration(
                        color: (testResult! ? AppTheme.success : AppTheme.error).withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            testResult! ? Icons.check_circle : Icons.error,
                            color: testResult! ? AppTheme.success : AppTheme.error,
                            size: 18,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              testMessage,
                              style: TextStyle(
                                color: testResult! ? AppTheme.success : AppTheme.error,
                                fontSize: 13,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          icon: testing
                              ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
                              : const Icon(Icons.network_ping),
                          label: const Text('Test Connection'),
                          onPressed: testing
                              ? null
                              : () async {
                                  setModalState(() => testing = true);
                                  final ok = await ApiClient.instance.checkHealth(urlController.text);
                                  setModalState(() {
                                    testing = false;
                                    testResult = ok;
                                    testMessage = ok ? 'Connected to Laravel API v1 successfully!' : 'Connection failed. Verify host and port.';
                                  });
                                },
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(backgroundColor: AppTheme.primary),
                          onPressed: () async {
                            final target = urlController.text.trim();
                            if (target.isNotEmpty) {
                              ApiClient.instance.setBaseUrl(target);
                              await StorageService.instance.saveServerUrl(target);
                              if (mounted) {
                                Navigator.pop(ctx);
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(content: Text('Server URL updated to: $target')),
                                );
                                setState(() {});
                              }
                            }
                          },
                          child: const Text('Save & Apply', style: TextStyle(color: Colors.white)),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;

    final auth = Provider.of<AuthProvider>(context, listen: false);
    final success = await auth.login(
      email: _emailController.text.trim(),
      password: _passwordController.text,
    );

    if (!mounted) return;

    if (success && auth.currentUser != null) {
      final role = auth.currentUser!.role;
      if (role == AppConstants.roleOwner) {
        Navigator.pushReplacementNamed(context, '/owner/dashboard');
      } else if (role == AppConstants.roleManager) {
        Navigator.pushReplacementNamed(context, '/manager/dashboard');
      } else {
        Navigator.pushReplacementNamed(context, '/marketplace');
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(auth.errorMessage ?? 'Login failed. Please check your credentials.'),
          backgroundColor: AppTheme.error,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.dns_outlined),
            tooltip: 'Server API Configuration',
            onPressed: _showServerConfigDialog,
          ),
        ],
      ),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Header
                  Center(
                    child: Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: AppTheme.primary.withOpacity(0.12),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(
                        Icons.apartment_rounded,
                        size: 48,
                        color: AppTheme.primary,
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                  Center(
                    child: Text(
                      'Welcome Back',
                      style: GoogleFonts.outfit(
                        fontSize: 28,
                        fontWeight: FontWeight.bold,
                        color: isDark ? AppTheme.textMainDark : AppTheme.textMainLight,
                      ),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Center(
                    child: Text(
                      'Sign in to manage your bookings and rentals',
                      style: GoogleFonts.inter(
                        fontSize: 14,
                        color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight,
                      ),
                    ),
                  ),
                  const SizedBox(height: 36),

                  // Email
                  AppTextField(
                    label: 'Email Address',
                    hint: 'you@example.com',
                    controller: _emailController,
                    keyboardType: TextInputType.emailAddress,
                    prefixIcon: const Icon(Icons.email_outlined, color: AppTheme.textMutedLight, size: 20),
                    validator: (val) {
                      if (val == null || val.trim().isEmpty) return 'Email is required';
                      if (!val.contains('@')) return 'Enter a valid email';
                      return null;
                    },
                  ),
                  const SizedBox(height: 20),

                  // Password
                  AppTextField(
                    label: 'Password',
                    hint: '••••••••',
                    controller: _passwordController,
                    obscureText: _obscurePassword,
                    prefixIcon: const Icon(Icons.lock_outline, color: AppTheme.textMutedLight, size: 20),
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscurePassword ? Icons.visibility_off : Icons.visibility,
                        color: AppTheme.textMutedLight,
                        size: 20,
                      ),
                      onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                    ),
                    validator: (val) {
                      if (val == null || val.isEmpty) return 'Password is required';
                      if (val.length < 6) return 'Password must be at least 6 characters';
                      return null;
                    },
                  ),
                  const SizedBox(height: 28),

                  // Submit Button
                  AppButton(
                    text: 'Sign In',
                    onPressed: _handleLogin,
                    isLoading: auth.status == AuthStatus.authenticating,
                  ),
                  const SizedBox(height: 24),

                  // Demo Shortcuts
                  Center(
                    child: Text(
                      'Quick Demo Login:',
                      style: GoogleFonts.inter(fontSize: 12, color: AppTheme.textMutedLight),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      ActionChip(
                        label: const Text('Tenant', style: TextStyle(fontSize: 12)),
                        onPressed: () {
                          setState(() {
                            _emailController.text = 'tenant@smartnest.com';
                            _passwordController.text = 'password';
                          });
                        },
                      ),
                      const SizedBox(width: 8),
                      ActionChip(
                        label: const Text('Owner', style: TextStyle(fontSize: 12)),
                        onPressed: () {
                          setState(() {
                            _emailController.text = 'owner@smartnest.com';
                            _passwordController.text = 'password';
                          });
                        },
                      ),
                      const SizedBox(width: 8),
                      ActionChip(
                        label: const Text('Manager', style: TextStyle(fontSize: 12)),
                        onPressed: () {
                          setState(() {
                            _emailController.text = 'manager@smartnest.com';
                            _passwordController.text = 'password';
                          });
                        },
                      ),
                    ],
                  ),
                  const SizedBox(height: 32),

                  // Register link
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        "Don't have an account? ",
                        style: GoogleFonts.inter(
                          color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight,
                        ),
                      ),
                      GestureDetector(
                        onTap: () => Navigator.pushNamed(context, '/register'),
                        child: Text(
                          'Register Now',
                          style: GoogleFonts.inter(
                            color: AppTheme.primary,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),

                  // Active Server URL Indicator
                  Center(
                    child: InkWell(
                      onTap: _showServerConfigDialog,
                      borderRadius: BorderRadius.circular(8),
                      child: Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.cloud_done_outlined, size: 14, color: AppTheme.primary),
                            const SizedBox(width: 6),
                            Flexible(
                              child: Text(
                                AppConstants.baseUrl,
                                overflow: TextOverflow.ellipsis,
                                style: GoogleFonts.inter(fontSize: 11, color: AppTheme.textMutedLight),
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
          ),
        ),
      ),
    );
  }
}
