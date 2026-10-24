import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../providers/tenant_provider.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/status_badge.dart';

class TenantMaintenanceScreen extends StatefulWidget {
  const TenantMaintenanceScreen({super.key});

  @override
  State<TenantMaintenanceScreen> createState() => _TenantMaintenanceScreenState();
}

class _TenantMaintenanceScreenState extends State<TenantMaintenanceScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<TenantProvider>(context, listen: false).fetchMaintenance();
    });
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<TenantProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Maintenance Tickets'),
      ),
      body: RefreshIndicator(
        onRefresh: () => prov.fetchMaintenance(),
        child: prov.isLoading
            ? const Center(child: CircularProgressIndicator())
            : prov.maintenanceTickets.isEmpty
                ? const EmptyState(
                    icon: Icons.build_circle_outlined,
                    title: 'No Issues Reported',
                    message: 'Have an issue with plumbing, electricity, or furniture? Submit a maintenance ticket anytime.',
                  )
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: prov.maintenanceTickets.length,
                    itemBuilder: (context, index) {
                      final item = prov.maintenanceTickets[index];
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
                                    item.ticketNumber,
                                    style: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.primary),
                                  ),
                                  Row(
                                    children: [
                                      StatusBadge(status: item.priority),
                                      const SizedBox(width: 6),
                                      StatusBadge(status: item.status),
                                    ],
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),
                              Text(
                                item.title,
                                style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                item.description,
                                style: GoogleFonts.inter(
                                  fontSize: 13,
                                  color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight,
                                ),
                              ),
                              if (item.images.isNotEmpty) ...[
                                const SizedBox(height: 12),
                                SizedBox(
                                  height: 60,
                                  child: ListView.builder(
                                    scrollDirection: Axis.horizontal,
                                    itemCount: item.images.length,
                                    itemBuilder: (context, idx) {
                                      return Container(
                                        width: 60,
                                        margin: const EdgeInsets.only(right: 8),
                                        decoration: BoxDecoration(
                                          borderRadius: BorderRadius.circular(8),
                                          image: DecorationImage(
                                            image: NetworkImage(item.images[idx].url),
                                            fit: BoxFit.cover,
                                          ),
                                        ),
                                      );
                                    },
                                  ),
                                ),
                              ],
                              if (item.resolutionNotes != null && item.resolutionNotes!.isNotEmpty) ...[
                                const Divider(height: 20),
                                Text(
                                  'Resolution: ${item.resolutionNotes}',
                                  style: GoogleFonts.inter(fontSize: 12, color: AppTheme.success, fontWeight: FontWeight.w500),
                                ),
                              ],
                            ],
                          ),
                        ),
                      );
                    },
                  ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => Navigator.pushNamed(context, '/tenant/maintenance/create'),
        backgroundColor: AppTheme.primary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Report Issue', style: TextStyle(color: Colors.white)),
      ),
    );
  }
}
