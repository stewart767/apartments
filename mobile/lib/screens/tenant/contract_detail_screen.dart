import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../providers/tenant_provider.dart';
import '../../widgets/app_button.dart';
import '../../widgets/price_card.dart';
import '../../widgets/status_badge.dart';

class ContractDetailScreen extends StatefulWidget {
  final int contractId;

  const ContractDetailScreen({super.key, required this.contractId});

  @override
  State<ContractDetailScreen> createState() => _ContractDetailScreenState();
}

class _ContractDetailScreenState extends State<ContractDetailScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<TenantProvider>(context, listen: false).fetchContractDetail(widget.contractId);
    });
  }

  Future<void> _handleAccept() async {
    final prov = Provider.of<TenantProvider>(context, listen: false);
    final success = await prov.acceptContract(widget.contractId);

    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Contract signed and activated successfully!'),
          backgroundColor: AppTheme.success,
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(prov.errorMessage ?? 'Acceptance failed.'),
          backgroundColor: AppTheme.error,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<TenantProvider>(context);
    final contract = prov.selectedContract;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    if (prov.isLoading || contract == null) {
      return Scaffold(
        appBar: AppBar(),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: Text(contract.contractNumber),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Status & Property Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                StatusBadge(status: contract.status),
                Text(
                  'Created: ${contract.createdAt?.substring(0, 10) ?? ''}',
                  style: GoogleFonts.inter(fontSize: 12, color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Text(
              contract.property?['property_name'] ?? 'Property',
              style: GoogleFonts.outfit(fontSize: 22, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            Text(
              contract.property?['address'] ?? '',
              style: GoogleFonts.inter(fontSize: 13, color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight),
            ),
            const Divider(height: 32),

            // Financial Summary
            Text('Financial Obligations', style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.cardDark : Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: isDark ? AppTheme.borderDark : AppTheme.borderLight),
              ),
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Rent Amount:'),
                      PriceCard(price: contract.rentAmount, billingPeriod: contract.billingFrequency, fontSize: 15),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Security Deposit:'),
                      Text('TZS ${contract.depositAmount.toInt()}', style: const TextStyle(fontWeight: FontWeight.bold)),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Tenancy Period
            Text('Agreement Duration', style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            Text(
              'From ${contract.startDate ?? 'TBD'} to ${contract.endDate ?? 'TBD'}',
              style: GoogleFonts.inter(fontSize: 14),
            ),
            const SizedBox(height: 24),

            // House Rules & Terms
            if (contract.rules != null && contract.rules!.isNotEmpty) ...[
              Text('House Rules & Policy', style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              Text(
                contract.rules!,
                style: GoogleFonts.inter(fontSize: 13, height: 1.5, color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight),
              ),
              const SizedBox(height: 24),
            ],

            if (contract.termsAndConditions != null && contract.termsAndConditions!.isNotEmpty) ...[
              Text('Terms & Conditions', style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              Text(
                contract.termsAndConditions!,
                style: GoogleFonts.inter(fontSize: 13, height: 1.5, color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight),
              ),
              const SizedBox(height: 24),
            ],

            // Signature Status
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: contract.tenantAccepted ? AppTheme.success.withOpacity(0.08) : AppTheme.warning.withOpacity(0.08),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: contract.tenantAccepted ? AppTheme.success.withOpacity(0.3) : AppTheme.warning.withOpacity(0.3),
                ),
              ),
              child: Row(
                children: [
                  Icon(
                    contract.tenantAccepted ? Icons.check_circle : Icons.pending_actions,
                    color: contract.tenantAccepted ? AppTheme.success : AppTheme.warning,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      contract.tenantAccepted
                          ? 'Signed by Tenant on ${contract.tenantAcceptedAt?.substring(0, 10) ?? ''}'
                          : 'Pending your digital signature and acceptance.',
                      style: GoogleFonts.inter(fontSize: 13, fontWeight: FontWeight.w500),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 32),

            // Action Button
            if (!contract.tenantAccepted)
              AppButton(
                text: 'Digitally Sign & Accept Contract',
                icon: Icons.draw,
                onPressed: _handleAccept,
                isLoading: prov.isLoading,
              ),
          ],
        ),
      ),
    );
  }
}
