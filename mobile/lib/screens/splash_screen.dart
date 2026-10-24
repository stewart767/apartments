import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../app/theme.dart';
import '../core/constants.dart';
import '../providers/auth_provider.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> with SingleTickerProviderStateMixin {
  late AnimationController _animController;
  late Animation<double> _fadeAnimation;

  @override
  void initState() {
    super.initState();
    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    );
    _fadeAnimation = CurvedAnimation(parent: _animController, curve: Curves.easeIn);
    _animController.forward();
    _checkAuth();
  }

  @override
  void dispose() {
    _animController.dispose();
    super.dispose();
  }

  Future<void> _checkAuth() async {
    // Artificial minimum delay for smooth branding display
    await Future.delayed(const Duration(milliseconds: 1100));

    if (!mounted) return;
    final auth = Provider.of<AuthProvider>(context, listen: false);
    await auth.initializeAuth();

    if (!mounted) return;

    if (auth.isAuthenticated && auth.currentUser != null) {
      final role = auth.currentUser!.role;
      if (role == AppConstants.roleOwner) {
        Navigator.pushReplacementNamed(context, '/owner/dashboard');
      } else if (role == AppConstants.roleManager) {
        Navigator.pushReplacementNamed(context, '/manager/dashboard');
      } else {
        Navigator.pushReplacementNamed(context, '/marketplace');
      }
    } else {
      // Guest mode: Browse uploaded apartments & houses without login
      Navigator.pushReplacementNamed(context, '/marketplace');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Center(
          child: FadeTransition(
            opacity: _fadeAnimation,
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // Modern Clean Brand Icon Container
                Container(
                  padding: const EdgeInsets.all(22),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: AppTheme.primary.withValues(alpha: 0.14),
                        blurRadius: 30,
                        offset: const Offset(0, 10),
                      ),
                    ],
                    border: Border.all(
                      color: AppTheme.primary.withValues(alpha: 0.15),
                      width: 1.5,
                    ),
                  ),
                  child: const Icon(
                    Icons.apartment_rounded,
                    size: 60,
                    color: AppTheme.primary,
                  ),
                ),
                const SizedBox(height: 28),

                // Brand Name
                Text(
                  AppConstants.appName,
                  style: GoogleFonts.outfit(
                    fontSize: 34,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.textMainLight,
                    letterSpacing: 0.5,
                  ),
                ),
                const SizedBox(height: 6),

                // Subtitle
                Text(
                  'Verified Accommodation & Apartments',
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    color: AppTheme.textMutedLight,
                    fontWeight: FontWeight.w400,
                  ),
                ),
                const SizedBox(height: 48),

                // Elegant Smooth Loading Indicator
                const SizedBox(
                  width: 24,
                  height: 24,
                  child: CircularProgressIndicator(
                    strokeWidth: 2.5,
                    valueColor: AlwaysStoppedAnimation<Color>(AppTheme.primary),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
