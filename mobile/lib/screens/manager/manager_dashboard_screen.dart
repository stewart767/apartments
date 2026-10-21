import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../providers/auth_provider.dart';
import '../../providers/manager_provider.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/stat_card.dart';

class ManagerDashboardScreen extends StatefulWidget {
  const ManagerDashboardScreen({super.key});

  @override
  State<ManagerDashboardScreen> createState() => _ManagerDashboardScreenState();
}

class _ManagerDashboardScreenState extends State<ManagerDashboardScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<ManagerProvider>(context, listen: false).fetchDashboard();
    });
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<ManagerProvider>(context);
    final auth = Provider.of<AuthProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final data = prov.dashboardData;

    return Scaffold(
      drawer: const AppDrawer(),
      appBar: AppBar(
        title: const Text('Operations Dashboard'),
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications_none),
            onPressed: () => Navigator.pushNamed(context, '/notifications'),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => prov.fetchDashboard(),
        child: prov.isLoading && data == null
            ? const Center(child: CircularProgressIndicator())
            : SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header Banner
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [Color(0xFF1E293B), Color(0xFF0F766E)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Reception & Operations',
                            style: GoogleFonts.outfit(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Manager: ${auth.currentUser?.name ?? 'Staff'}',
                            style: GoogleFonts.inter(color: Colors.white70, fontSize: 13),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Metrics
                    Row(
                      children: [
                        Expanded(
                          child: StatCard(
                            title: "Today's Check-ins",
                            value: (data?['today_checkins'] ?? '0').toString(),
                            icon: Icons.login,
                            color: AppTheme.success,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: StatCard(
                            title: "Today's Check-outs",
                            value: (data?['today_checkouts'] ?? '0').toString(),
                            icon: Icons.logout,
                            color: AppTheme.secondary,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: StatCard(
                            title: 'Active Tickets',
                            value: (data?['active_tickets'] ?? '0').toString(),
                            icon: Icons.build_circle_outlined,
                            color: AppTheme.warning,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: StatCard(
                            title: 'Occupancy Rate',
                            value: '${data?['occupancy_rate'] ?? '0'}%',
                            icon: Icons.hotel_outlined,
                            color: AppTheme.primary,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),

                    // Core Operations
                    Text('Digital Front Desk', style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 12),
                    _buildDeskActionCard(
                      context: context,
                      title: 'Digital Tenant Check-in',
                      subtitle: 'Record baseline inventory, deposit received, and issue room keys.',
                      icon: Icons.vpn_key_outlined,
                      color: AppTheme.success,
                      route: '/manager/checkin',
                      isDark: isDark,
                    ),
                    const SizedBox(height: 12),
                    _buildDeskActionCard(
                      context: context,
                      title: 'Digital Tenant Check-out',
                      subtitle: 'Compare move-in vs move-out conditions, log damage charges, refund deposit.',
                      icon: Icons.fact_check_outlined,
                      color: AppTheme.secondary,
                      route: '/manager/checkout',
                      isDark: isDark,
                    ),
                  ],
                ),
              ),
      ),
    );
  }

  Widget _buildDeskActionCard({
    required BuildContext context,
    required String title,
    required String subtitle,
    required IconData icon,
    required Color color,
    required String route,
    required bool isDark,
  }) {
    return Card(
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        leading: Container(
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(
            color: color.withOpacity(0.12),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, color: color, size: 24),
        ),
        title: Text(title, style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 15)),
        subtitle: Text(subtitle, style: GoogleFonts.inter(fontSize: 12)),
        trailing: const Icon(Icons.chevron_right),
        onTap: () => Navigator.pushNamed(context, route),
      ),
    );
  }
}
