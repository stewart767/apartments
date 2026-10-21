import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../models/application_model.dart';
import '../../providers/owner_provider.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/price_card.dart';
import '../../widgets/status_badge.dart';

class OwnerApplicationsScreen extends StatefulWidget {
  const OwnerApplicationsScreen({super.key});

  @override
  State<OwnerApplicationsScreen> createState() => _OwnerApplicationsScreenState();
}

class _OwnerApplicationsScreenState extends State<OwnerApplicationsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<OwnerProvider>(context, listen: false).fetchApplications();
    });
  }

  void _showActionDialog(ApplicationModel app, bool isApprove) {
    final notesController = TextEditingController();
    final prov = Provider.of<OwnerProvider>(context, listen: false);

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(isApprove ? 'Approve Application' : 'Reject Application'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              isApprove
                  ? 'Approving this application will automatically generate a digital tenancy agreement contract and send it to the tenant for signing.'
                  : 'Please provide a reason for rejecting this application.',
              style: GoogleFonts.inter(fontSize: 13),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: notesController,
              decoration: InputDecoration(
                hintText: isApprove ? 'Optional notes for tenant...' : 'Rejection reason (required)...',
              ),
              maxLines: 2,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(ctx);
              final success = await prov.updateApplicationStatus(
                app.id,
                status: isApprove ? 'approved' : 'rejected',
                notes: notesController.text.trim(),
              );
              if (mounted && success) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(isApprove ? 'Application approved & contract created!' : 'Application rejected.'),
                    backgroundColor: isApprove ? AppTheme.success : AppTheme.error,
                  ),
                );
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: isApprove ? AppTheme.success : AppTheme.error,
            ),
            child: Text(isApprove ? 'Confirm Approval' : 'Reject'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<OwnerProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Booking Applications'),
      ),
      body: RefreshIndicator(
        onRefresh: () => prov.fetchApplications(),
        child: prov.isLoading
            ? const Center(child: CircularProgressIndicator())
            : prov.applications.isEmpty
                ? const EmptyState(
                    icon: Icons.rule_outlined,
                    title: 'No Pending Applications',
                    message: 'New prospective tenant applications will appear here for review.',
                  )
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
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
                                app.tenant?['name'] ?? 'Applicant',
                                style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold),
                              ),
                              if (app.tenant?['phone'] != null)
                                Text(
                                  'Phone: ${app.tenant?['phone']}',
                                  style: GoogleFonts.inter(fontSize: 12, color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight),
                                ),
                              const SizedBox(height: 4),
                              Text(
                                'Property: ${app.property?['property_name'] ?? 'Property'}',
                                style: GoogleFonts.inter(fontSize: 13, fontWeight: FontWeight.w500),
                              ),
                              if (app.room != null || app.bed != null)
                                Text(
                                  'Room: ${app.room?['room_number'] ?? 'N/A'}${app.bed != null ? ' | Bed: ${app.bed?['bed_number']}' : ''}',
                                  style: GoogleFonts.inter(fontSize: 12, color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight),
                                ),
                              const SizedBox(height: 8),
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
                              if (app.isPending) ...[
                                const Divider(height: 20),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.end,
                                  children: [
                                    OutlinedButton(
                                      onPressed: () => _showActionDialog(app, false),
                                      style: OutlinedButton.styleFrom(
                                        foregroundColor: AppTheme.error,
                                        side: const BorderSide(color: AppTheme.error),
                                        minimumSize: const Size(90, 36),
                                        padding: const EdgeInsets.symmetric(horizontal: 12),
                                      ),
                                      child: const Text('Reject', style: TextStyle(fontSize: 12)),
                                    ),
                                    const SizedBox(width: 10),
                                    ElevatedButton(
                                      onPressed: () => _showActionDialog(app, true),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: AppTheme.success,
                                        minimumSize: const Size(100, 36),
                                        padding: const EdgeInsets.symmetric(horizontal: 12),
                                      ),
                                      child: const Text('Approve', style: TextStyle(fontSize: 12)),
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
