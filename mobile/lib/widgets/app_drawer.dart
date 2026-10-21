import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../app/theme.dart';
import '../providers/auth_provider.dart';

class AppDrawer extends StatelessWidget {
  const AppDrawer({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final user = auth.currentUser;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Drawer(
      backgroundColor: isDark ? AppTheme.cardDark : Colors.white,
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          UserAccountsDrawerHeader(
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [AppTheme.primaryDark, AppTheme.primary],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
            accountName: Text(
              user?.name ?? 'Guest User',
              style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 16),
            ),
            accountEmail: Text(
              '${user?.email ?? ''} • ${user?.role.toUpperCase() ?? ''}',
              style: GoogleFonts.inter(fontSize: 12, color: Colors.white70),
            ),
            currentAccountPicture: CircleAvatar(
              backgroundColor: Colors.white,
              child: Text(
                (user?.name.isNotEmpty == true) ? user!.name[0].toUpperCase() : 'U',
                style: GoogleFonts.outfit(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.primary,
                ),
              ),
            ),
          ),

          // Common / Tenant Links
          if (user?.isTenant == true) ...[
            ListTile(
              leading: const Icon(Icons.search, color: AppTheme.primary),
              title: const Text('Property Marketplace'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/marketplace');
              },
            ),
            ListTile(
              leading: const Icon(Icons.assignment, color: AppTheme.primary),
              title: const Text('My Applications'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/tenant/applications');
              },
            ),
            ListTile(
              leading: const Icon(Icons.description, color: AppTheme.primary),
              title: const Text('My Contracts'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/tenant/contracts');
              },
            ),
            ListTile(
              leading: const Icon(Icons.receipt_long, color: AppTheme.primary),
              title: const Text('Invoices & Payments'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/tenant/invoices');
              },
            ),
            ListTile(
              leading: const Icon(Icons.build, color: AppTheme.primary),
              title: const Text('Maintenance Tickets'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/tenant/maintenance');
              },
            ),
            ListTile(
              leading: const Icon(Icons.inventory_2, color: AppTheme.primary),
              title: const Text('Room Assets Handover'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/tenant/assets');
              },
            ),
          ],

          // Owner Links
          if (user?.isOwner == true) ...[
            ListTile(
              leading: const Icon(Icons.dashboard, color: AppTheme.primary),
              title: const Text('Owner Dashboard'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/owner/dashboard');
              },
            ),
            ListTile(
              leading: const Icon(Icons.apartment, color: AppTheme.primary),
              title: const Text('My Properties'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/owner/properties');
              },
            ),
            ListTile(
              leading: const Icon(Icons.rule, color: AppTheme.primary),
              title: const Text('Booking Applications'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/owner/applications');
              },
            ),
            ListTile(
              leading: const Icon(Icons.verified, color: AppTheme.primary),
              title: const Text('Payment Approvals'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/owner/payments');
              },
            ),
            ListTile(
              leading: const Icon(Icons.handyman, color: AppTheme.primary),
              title: const Text('Maintenance Management'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/owner/maintenance');
              },
            ),
            ListTile(
              leading: const Icon(Icons.bar_chart, color: AppTheme.primary),
              title: const Text('Financial & Occupancy Reports'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/owner/reports');
              },
            ),
          ],

          // Manager Links
          if (user?.isManager == true) ...[
            ListTile(
              leading: const Icon(Icons.hotel, color: AppTheme.primary),
              title: const Text('Operations Dashboard'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/manager/dashboard');
              },
            ),
            ListTile(
              leading: const Icon(Icons.login, color: AppTheme.primary),
              title: const Text('Digital Check-in'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/manager/checkin');
              },
            ),
            ListTile(
              leading: const Icon(Icons.logout, color: AppTheme.primary),
              title: const Text('Digital Check-out'),
              onTap: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/manager/checkout');
              },
            ),
          ],

          const Divider(),

          ListTile(
            leading: const Icon(Icons.notifications_none),
            title: const Text('Notifications'),
            onTap: () {
              Navigator.pop(context);
              Navigator.pushNamed(context, '/notifications');
            },
          ),
          ListTile(
            leading: const Icon(Icons.person_outline),
            title: const Text('Profile Settings'),
            onTap: () {
              Navigator.pop(context);
              Navigator.pushNamed(context, '/profile');
            },
          ),
          ListTile(
            leading: const Icon(Icons.exit_to_app, color: AppTheme.error),
            title: const Text('Sign Out', style: TextStyle(color: AppTheme.error)),
            onTap: () async {
              await auth.logout();
              if (context.mounted) {
                Navigator.pushNamedAndRemoveUntil(context, '/login', (route) => false);
              }
            },
          ),
        ],
      ),
    );
  }
}
