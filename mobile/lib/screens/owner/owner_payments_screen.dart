import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../models/payment_model.dart';
import '../../providers/owner_provider.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/status_badge.dart';

class OwnerPaymentsScreen extends StatefulWidget {
  const OwnerPaymentsScreen({super.key});

  @override
  State<OwnerPaymentsScreen> createState() => _OwnerPaymentsScreenState();
}

class _OwnerPaymentsScreenState extends State<OwnerPaymentsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<OwnerProvider>(context, listen: false).fetchPayments();
    });
  }

  void _showVerifyDialog(PaymentModel p, bool isPaid) {
    final reasonController = TextEditingController();
    final prov = Provider.of<OwnerProvider>(context, listen: false);

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text(isPaid ? 'Confirm Payment' : 'Reject Payment Proof'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              isPaid
                  ? 'Confirming this transaction will mark the invoice as PAID and update tenant billing ledger.'
                  : 'Specify reason for rejecting this receipt (e.g. illegible screenshot, wrong amount).',
              style: GoogleFonts.inter(fontSize: 13),
            ),
            if (!isPaid) ...[
              const SizedBox(height: 14),
              TextField(
                controller: reasonController,
                decoration: const InputDecoration(hintText: 'Rejection reason...'),
                maxLines: 2,
              ),
            ],
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
              final success = await prov.verifyPayment(
                p.id,
                status: isPaid ? 'paid' : 'rejected',
                reason: reasonController.text.trim(),
              );
              if (mounted && success) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(isPaid ? 'Payment verified and marked PAID!' : 'Payment rejected.'),
                    backgroundColor: isPaid ? AppTheme.success : AppTheme.error,
                  ),
                );
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: isPaid ? AppTheme.success : AppTheme.error,
            ),
            child: Text(isPaid ? 'Confirm Paid' : 'Reject'),
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
        title: const Text('Payment Approvals'),
      ),
      body: RefreshIndicator(
        onRefresh: () => prov.fetchPayments(),
        child: prov.isLoading
            ? const Center(child: CircularProgressIndicator())
            : prov.payments.isEmpty
                ? const EmptyState(
                    icon: Icons.payments_outlined,
                    title: 'No Payments to Review',
                    message: 'Tenant uploaded proofs of payment will appear here for verification.',
                  )
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: prov.payments.length,
                    itemBuilder: (context, index) {
                      final p = prov.payments[index];
                      return Card(
                        margin: const EdgeInsets.only(bottom: 16),
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    p.paymentReference,
                                    style: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.primary),
                                  ),
                                  StatusBadge(status: p.status),
                                ],
                              ),
                              const SizedBox(height: 8),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    p.tenant?['name'] ?? 'Tenant',
                                    style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold),
                                  ),
                                  Text(
                                    'TZS ${p.amount.toInt()}',
                                    style: GoogleFonts.outfit(fontSize: 18, fontWeight: FontWeight.bold, color: AppTheme.primary),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Method: ${p.paymentMethod?.toUpperCase()} | Property: ${p.propertyName ?? 'N/A'}',
                                style: GoogleFonts.inter(
                                  fontSize: 12,
                                  color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight,
                                ),
                              ),
                              if (p.receiptUrl != null) ...[
                                const SizedBox(height: 12),
                                InkWell(
                                  onTap: () {
                                    showDialog(
                                      context: context,
                                      builder: (ctx) => Dialog(
                                        child: Image.network(p.receiptUrl!, fit: BoxFit.contain),
                                      ),
                                    );
                                  },
                                  child: Container(
                                    height: 120,
                                    width: double.infinity,
                                    decoration: BoxDecoration(
                                      borderRadius: BorderRadius.circular(8),
                                      image: DecorationImage(
                                        image: NetworkImage(p.receiptUrl!),
                                        fit: BoxFit.cover,
                                      ),
                                    ),
                                    child: Center(
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: Colors.black54,
                                          borderRadius: BorderRadius.circular(12),
                                        ),
                                        child: const Text(
                                          'Tap to view full receipt',
                                          style: TextStyle(color: Colors.white, fontSize: 11),
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                              if (p.isUnderReview || p.isPending) ...[
                                const Divider(height: 24),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.end,
                                  children: [
                                    OutlinedButton(
                                      onPressed: () => _showVerifyDialog(p, false),
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
                                      onPressed: () => _showVerifyDialog(p, true),
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: AppTheme.success,
                                        minimumSize: const Size(120, 36),
                                        padding: const EdgeInsets.symmetric(horizontal: 12),
                                      ),
                                      child: const Text('Confirm Paid', style: TextStyle(fontSize: 12)),
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
