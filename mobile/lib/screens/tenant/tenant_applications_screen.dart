import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../providers/tenant_provider.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/price_card.dart';
import '../../widgets/status_badge.dart';

class TenantApplicationsScreen extends StatefulWidget {
  const TenantApplicationsScreen({super.key});

  @override
  State<TenantApplicationsScreen> createState() => _TenantApplicationsScreenState();
}

class _TenantApplicationsScreenState extends State<TenantApplicationsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<TenantProvider>(context, listen: false).fetchApplications();
    });
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<TenantProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Applications'),
      ),
      body: RefreshIndicator(
        onRefresh: () => prov.fetchApplications(),
        child: prov.isLoading
            ? const Center(child: CircularProgressIndicator())
            : prov.applications.isEmpty
                ? EmptyState(
                    icon: Icons.assignment_outlined,
                    title: 'No Applications Yet',
                    message: 'Explore properties in the marketplace to submit your first application.',
                    actionText: 'Browse Marketplace',
                    onAction: () => Navigator.pushNamed(context, '/marketplace'),
                  )
                : ListView.builder(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                    itemCount: prov.applications.length,
                    itemBuilder: (context, index) {
                      final app = prov.applications[index];
                      return Card(
                        margin: const EdgeInsets.only(bottom: 12),
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    app.applicationNumber,
                                    style: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.primary),
                                  ),
                                  StatusBadge(status: app.status),
                                ],
                              ),
                              const SizedBox(height: 8),
                              Text(
                                app.property?['property_name'] ?? 'Property',
                                style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold),
                              ),
                              const SizedBox(height: 4),
                              if (app.room != null || app.bed != null)
                                Text(
                                  'Room: ${app.room?['room_number'] ?? 'N/A'}${app.bed != null ? ' | Bed: ${app.bed?['bed_number']}' : ''}',
                                  style: GoogleFonts.inter(fontSize: 12, color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight),
                                ),
                              const SizedBox(height: 12),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    '${app.startDate ?? ''} to ${app.endDate ?? ''}',
                                    style: GoogleFonts.inter(fontSize: 12),
                                  ),
                                  PriceCard(price: app.price, billingPeriod: app.billingPeriod, fontSize: 14),
                                ],
                              ),
                              if (app.isApproved) ...[
                                const Divider(height: 20),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.end,
                                  children: [
                                    ElevatedButton(
                                      onPressed: () {
                                        Navigator.pushNamed(context, '/tenant/contracts');
                                      },
                                      style: ElevatedButton.styleFrom(
                                        minimumSize: const Size(120, 36),
                                        padding: const EdgeInsets.symmetric(horizontal: 12),
                                      ),
                                      child: const Text('View Contract', style: TextStyle(fontSize: 12)),
                                    ),
                                  ],
                                ),
                              ],
                            ],
                          ),
                        ),
                      );
                    },
                  ),
      ),
    );
  }
}
