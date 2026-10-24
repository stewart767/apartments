import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../providers/tenant_provider.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/status_badge.dart';

class TenantAssetsScreen extends StatefulWidget {
  const TenantAssetsScreen({super.key});

  @override
  State<TenantAssetsScreen> createState() => _TenantAssetsScreenState();
}

class _TenantAssetsScreenState extends State<TenantAssetsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<TenantProvider>(context, listen: false).fetchAssets();
    });
  }

  Future<void> _handleConfirm(int assetId) async {
    final prov = Provider.of<TenantProvider>(context, listen: false);
    final success = await prov.confirmAssetHandover(assetId);

    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Asset condition confirmed!'), backgroundColor: AppTheme.success),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<TenantProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Room Inventory & Assets'),
      ),
      body: RefreshIndicator(
        onRefresh: () => prov.fetchAssets(),
        child: prov.isLoading
            ? const Center(child: CircularProgressIndicator())
            : prov.assets.isEmpty
                ? const EmptyState(
                    icon: Icons.inventory_2_outlined,
                    title: 'No Assigned Assets',
                    message: 'Any furniture, keys, or appliances handed over to you will be listed here.',
                  )
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: prov.assets.length,
                    itemBuilder: (context, index) {
                      final item = prov.assets[index];
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
                                    item.assetName,
                                    style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold),
                                  ),
                                  StatusBadge(status: item.condition),
                                ],
                              ),
                              const SizedBox(height: 4),
                              if (item.assetCode != null)
                                Text(
                                  'Tag / Code: ${item.assetCode}',
                                  style: GoogleFonts.inter(fontSize: 12, color: AppTheme.primary),
                                ),
                              if (item.category != null)
                                Text(
                                  'Category: ${item.category}',
                                  style: GoogleFonts.inter(
                                    fontSize: 12,
                                    color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight,
                                  ),
                                ),
                              if (item.handoverNotes != null && item.handoverNotes!.isNotEmpty) ...[
                                const SizedBox(height: 8),
                                Text(
                                  'Handover Notes: ${item.handoverNotes}',
                                  style: GoogleFonts.inter(fontSize: 12),
                                ),
                              ],
                              const Divider(height: 20),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    item.tenantConfirmed
                                        ? 'Confirmed on ${item.tenantConfirmedAt?.substring(0, 10) ?? ''}'
                                        : 'Awaiting your confirmation',
                                    style: GoogleFonts.inter(
                                      fontSize: 11,
                                      color: item.tenantConfirmed ? AppTheme.success : AppTheme.warning,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  if (!item.tenantConfirmed)
                                    ElevatedButton(
                                      onPressed: () => _handleConfirm(item.id),
                                      style: ElevatedButton.styleFrom(
                                        minimumSize: const Size(90, 32),
                                        padding: const EdgeInsets.symmetric(horizontal: 12),
                                      ),
                                      child: const Text('Confirm', style: TextStyle(fontSize: 11)),
                                    ),
                                ],
                              ),
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
