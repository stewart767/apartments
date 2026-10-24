import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../providers/tenant_provider.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/price_card.dart';
import '../../widgets/status_badge.dart';

class TenantContractsScreen extends StatefulWidget {
  const TenantContractsScreen({super.key});

  @override
  State<TenantContractsScreen> createState() => _TenantContractsScreenState();
}

class _TenantContractsScreenState extends State<TenantContractsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<TenantProvider>(context, listen: false).fetchContracts();
    });
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<TenantProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Rental Contracts'),
      ),
      body: RefreshIndicator(
        onRefresh: () => prov.fetchContracts(),
        child: prov.isLoading
            ? const Center(child: CircularProgressIndicator())
            : prov.contracts.isEmpty
                ? const EmptyState(
                    icon: Icons.description_outlined,
                    title: 'No Contracts Found',
                    message: 'Once your booking application is approved, your tenancy agreement will appear here for signing.',
                  )
                : ListView.builder(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                    itemCount: prov.contracts.length,
                    itemBuilder: (context, index) {
                      final item = prov.contracts[index];
                      return Card(
                        margin: const EdgeInsets.only(bottom: 12),
                        child: InkWell(
                          onTap: () {
                            Navigator.pushNamed(
                              context,
                              '/contract/detail',
                              arguments: item.id,
                            );
                          },
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      item.contractNumber,
                                      style: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.primary),
                                    ),
                                    StatusBadge(status: item.status),
                                  ],
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  item.property?['property_name'] ?? 'Property',
                                  style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'Duration: ${item.startDate ?? ''} to ${item.endDate ?? ''}',
                                  style: GoogleFonts.inter(
                                    fontSize: 12,
                                    color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight,
                                  ),
                                ),
                                const SizedBox(height: 12),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    PriceCard(price: item.rentAmount, billingPeriod: item.billingFrequency, fontSize: 14),
                                    if (item.isPendingSignature)
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: AppTheme.warning.withOpacity(0.15),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          'Action Required: Sign',
                                          style: GoogleFonts.inter(fontSize: 11, fontWeight: FontWeight.bold, color: AppTheme.warning),
                                        ),
                                      ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ),
                      );
                    },
                  ),
      ),
    );
  }
}
