import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../providers/owner_provider.dart';
import '../../widgets/stat_card.dart';

class OwnerReportsScreen extends StatefulWidget {
  const OwnerReportsScreen({super.key});

  @override
  State<OwnerReportsScreen> createState() => _OwnerReportsScreenState();
}

class _OwnerReportsScreenState extends State<OwnerReportsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<OwnerProvider>(context, listen: false).fetchReports();
    });
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<OwnerProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final r = prov.reports;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Reports & Analytics'),
      ),
      body: RefreshIndicator(
        onRefresh: () => prov.fetchReports(),
        child: prov.isLoading && r == null
            ? const Center(child: CircularProgressIndicator())
            : r == null
                ? const Center(child: Text('No report data available.'))
                : SingleChildScrollView(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Financial Summary', style: GoogleFonts.outfit(fontSize: 18, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Expanded(
                              child: StatCard(
                                title: 'Total Collected',
                                value: 'TZS ${(r.financialSummary['total_collected'] ?? 0).toInt()}',
                                icon: Icons.account_balance_wallet,
                                color: AppTheme.success,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: StatCard(
                                title: 'Outstanding',
                                value: 'TZS ${(r.financialSummary['total_outstanding'] ?? 0).toInt()}',
                                icon: Icons.pending_actions,
                                color: AppTheme.warning,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 24),

                        Text('Occupancy Breakdown', style: GoogleFonts.outfit(fontSize: 18, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 12),
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: isDark ? AppTheme.cardDark : Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: isDark ? AppTheme.borderDark : AppTheme.borderLight),
                          ),
                          child: Column(
                            children: [
                              _buildMetricRow('Total Units / Rooms:', '${r.occupancy['total_units'] ?? 0}'),
                              const Divider(height: 16),
                              _buildMetricRow('Occupied Units:', '${r.occupancy['occupied_units'] ?? 0}'),
                              const Divider(height: 16),
                              _buildMetricRow('Available Units:', '${r.occupancy['available_units'] ?? 0}'),
                              const Divider(height: 16),
                              _buildMetricRow(
                                'Overall Occupancy Rate:',
                                '${r.occupancy['occupancy_rate'] ?? 0}%',
                                isHighlight: true,
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 24),

                        Text('Maintenance Resolution', style: GoogleFonts.outfit(fontSize: 18, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Expanded(
                              child: StatCard(
                                title: 'Total Tickets',
                                value: '${r.maintenance['total_tickets'] ?? 0}',
                                icon: Icons.build,
                                color: AppTheme.primary,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: StatCard(
                                title: 'Resolved Tickets',
                                value: '${r.maintenance['resolved_tickets'] ?? 0}',
                                icon: Icons.check_circle,
                                color: AppTheme.success,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
      ),
    );
  }

  Widget _buildMetricRow(String label, String value, {bool isHighlight = false}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: GoogleFonts.inter(fontSize: 14)),
        Text(
          value,
          style: GoogleFonts.inter(
            fontSize: 14,
            fontWeight: FontWeight.bold,
            color: isHighlight ? AppTheme.primary : null,
          ),
        ),
      ],
    );
  }
}
