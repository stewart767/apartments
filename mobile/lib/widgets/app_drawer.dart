import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../app/theme.dart';
import '../core/constants.dart';
import '../providers/auth_provider.dart';

class AppDrawer extends StatelessWidget {
  const AppDrawer({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final user = auth.currentUser;
    final isGuest = user == null || !auth.isAuthenticated;

    return Drawer(
      backgroundColor: Colors.white,
      surfaceTintColor: Colors.transparent,
      child: SafeArea(
        child: Column(
          children: [
            // Header Area
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
              decoration: const BoxDecoration(
                color: Colors.white,
                border: Border(bottom: BorderSide(color: AppTheme.borderLight, width: 1)),
              ),
              child: isGuest
                  ? Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                color: AppTheme.primary.withValues(alpha: 0.1),
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(
                                Icons.apartment_rounded,
                                color: AppTheme.primary,
                                size: 26,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  AppConstants.appName,
                                  style: GoogleFonts.outfit(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 18,
                                    color: AppTheme.textMainLight,
                                  ),
                                ),
                                Text(
                                  'Guest Browsing Mode',
                                  style: GoogleFonts.inter(
                                    fontSize: 12,
                                    color: AppTheme.primary,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          'Sign in to reserve verified apartments, sign contracts, and manage rent payments.',
                          style: GoogleFonts.inter(fontSize: 12.5, color: AppTheme.textMutedLight, height: 1.4),
                        ),
                        const SizedBox(height: 16),
                        Row(
                          children: [
                            Expanded(
                              child: ElevatedButton(
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: AppTheme.primary,
                                  minimumSize: const Size.fromHeight(42),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                ),
                                onPressed: () {
                                  Navigator.pop(context);
                                  Navigator.pushNamed(context, '/login');
                                },
                                child: const Text('Sign In', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: OutlinedButton(
                                style: OutlinedButton.styleFrom(
                                  minimumSize: const Size.fromHeight(42),
                                  side: const BorderSide(color: AppTheme.primary),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                ),
                                onPressed: () {
                                  Navigator.pop(context);
                                  Navigator.pushNamed(context, '/register');
                                },
                                child: const Text('Register', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                              ),
                            ),
                          ],
                        ),
                      ],
                    )
                  : Row(
                      children: [
                        CircleAvatar(
                          radius: 28,
                          backgroundColor: AppTheme.primary.withValues(alpha: 0.12),
                          child: Text(
                            (user.name.isNotEmpty) ? user.name[0].toUpperCase() : 'U',
                            style: GoogleFonts.outfit(
                              fontSize: 22,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.primary,
                            ),
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                user.name,
                                style: GoogleFonts.outfit(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 16,
                                  color: AppTheme.textMainLight,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 2),
                              Text(
                                user.email,
                                style: GoogleFonts.inter(
                                  fontSize: 12,
                                  color: AppTheme.textMutedLight,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 4),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                decoration: BoxDecoration(
                                  color: AppTheme.primary.withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  user.role.toUpperCase(),
                                  style: GoogleFonts.inter(
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    color: AppTheme.primary,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
            ),

            // Navigation List
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(vertical: 8),
                children: [
                  _buildDrawerItem(
                    context,
                    icon: Icons.explore_outlined,
                    activeIcon: Icons.explore,
                    title: 'Browse Accommodations',
                    onTap: () {
                      Navigator.pop(context);
                      Navigator.pushNamed(context, '/marketplace');
                    },
                  ),

                  if (!isGuest && user.isTenant) ...[
                    _buildDrawerItem(
                      context,
                      icon: Icons.assignment_outlined,
                      activeIcon: Icons.assignment,
                      title: 'My Applications',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.pushNamed(context, '/tenant/applications');
                      },
                    ),
                    _buildDrawerItem(
                      context,
                      icon: Icons.description_outlined,
                      activeIcon: Icons.description,
                      title: 'My Contracts',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.pushNamed(context, '/tenant/contracts');
                      },
                    ),
                    _buildDrawerItem(
                      context,
                      icon: Icons.receipt_long_outlined,
                      activeIcon: Icons.receipt_long,
                      title: 'Invoices & Payments',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.pushNamed(context, '/tenant/invoices');
                      },
                    ),
                    _buildDrawerItem(
                      context,
                      icon: Icons.build_outlined,
                      activeIcon: Icons.build,
                      title: 'Maintenance Tickets',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.pushNamed(context, '/tenant/maintenance');
                      },
                    ),
                    _buildDrawerItem(
                      context,
                      icon: Icons.inventory_2_outlined,
                      activeIcon: Icons.inventory_2,
                      title: 'Room Assets Handover',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.pushNamed(context, '/tenant/assets');
                      },
                    ),
                  ],

                  if (!isGuest && user.isOwner) ...[
                    _buildDrawerItem(
                      context,
                      icon: Icons.dashboard_outlined,
                      activeIcon: Icons.dashboard,
                      title: 'Owner Dashboard',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.pushNamed(context, '/owner/dashboard');
                      },
                    ),
                    _buildDrawerItem(
                      context,
                      icon: Icons.apartment_outlined,
                      activeIcon: Icons.apartment,
                      title: 'My Properties',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.pushNamed(context, '/owner/properties');
                      },
                    ),
                    _buildDrawerItem(
                      context,
                      icon: Icons.rule_folder_outlined,
                      activeIcon: Icons.rule_folder,
                      title: 'Booking Applications',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.pushNamed(context, '/owner/applications');
                      },
                    ),
                    _buildDrawerItem(
                      context,
                      icon: Icons.verified_outlined,
                      activeIcon: Icons.verified,
                      title: 'Payment Approvals',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.pushNamed(context, '/owner/payments');
                      },
                    ),
                    _buildDrawerItem(
                      context,
                      icon: Icons.handyman_outlined,
                      activeIcon: Icons.handyman,
                      title: 'Maintenance Requests',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.pushNamed(context, '/owner/maintenance');
                      },
                    ),
                    _buildDrawerItem(
                      context,
                      icon: Icons.bar_chart_outlined,
                      activeIcon: Icons.bar_chart,
                      title: 'Financial Reports',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.pushNamed(context, '/owner/reports');
                      },
                    ),
                  ],

                  if (!isGuest && user.isManager) ...[
                    _buildDrawerItem(
                      context,
                      icon: Icons.hotel_outlined,
                      activeIcon: Icons.hotel,
                      title: 'Operations Dashboard',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.pushNamed(context, '/manager/dashboard');
                      },
                    ),
                    _buildDrawerItem(
                      context,
                      icon: Icons.login_outlined,
                      activeIcon: Icons.login,
                      title: 'Digital Check-in',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.pushNamed(context, '/manager/checkin');
                      },
                    ),
                    _buildDrawerItem(
                      context,
                      icon: Icons.logout_outlined,
                      activeIcon: Icons.logout,
                      title: 'Digital Check-out',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.pushNamed(context, '/manager/checkout');
                      },
                    ),
                  ],

                  const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    child: Divider(color: AppTheme.borderLight),
                  ),

                  if (!isGuest) ...[
                    _buildDrawerItem(
                      context,
                      icon: Icons.notifications_none_outlined,
                      activeIcon: Icons.notifications,
                      title: 'Notifications',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.pushNamed(context, '/notifications');
                      },
                    ),
                    _buildDrawerItem(
                      context,
                      icon: Icons.person_outline_rounded,
                      activeIcon: Icons.person_rounded,
                      title: 'Profile Settings',
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.pushNamed(context, '/profile');
                      },
                    ),
                  ],
                ],
              ),
            ),

            // Bottom Footer
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: const BoxDecoration(
                border: Border(top: BorderSide(color: AppTheme.borderLight, width: 1)),
              ),
              child: isGuest
                  ? ListTile(
                      dense: true,
                      contentPadding: EdgeInsets.zero,
                      leading: const Icon(Icons.login_rounded, color: AppTheme.primary),
                      title: Text(
                        'Login / Register',
                        style: GoogleFonts.inter(fontWeight: FontWeight.bold, color: AppTheme.primary),
                      ),
                      subtitle: const Text('Sign in to your account', style: TextStyle(fontSize: 11)),
                      onTap: () {
                        Navigator.pop(context);
                        Navigator.pushNamed(context, '/login');
                      },
                    )
                  : ListTile(
                      dense: true,
                      contentPadding: EdgeInsets.zero,
                      leading: const Icon(Icons.logout_rounded, color: AppTheme.error),
                      title: Text(
                        'Sign Out',
                        style: GoogleFonts.inter(fontWeight: FontWeight.bold, color: AppTheme.error),
                      ),
                      onTap: () async {
                        await auth.logout();
                        if (context.mounted) {
                          Navigator.pushNamedAndRemoveUntil(context, '/marketplace', (route) => false);
                        }
                      },
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDrawerItem(
    BuildContext context, {
    required IconData icon,
    required IconData activeIcon,
    required String title,
    required VoidCallback onTap,
  }) {
    return ListTile(
      dense: true,
      horizontalTitleGap: 12,
      contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 2),
      leading: Icon(icon, color: AppTheme.textMainLight, size: 22),
      title: Text(
        title,
        style: GoogleFonts.inter(
          fontSize: 14,
          fontWeight: FontWeight.w500,
          color: AppTheme.textMainLight,
        ),
      ),
      onTap: onTap,
    );
  }
}
