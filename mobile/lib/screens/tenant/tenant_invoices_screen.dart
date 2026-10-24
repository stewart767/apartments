import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../providers/tenant_provider.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/status_badge.dart';

class TenantInvoicesScreen extends StatefulWidget {
  const TenantInvoicesScreen({super.key});

  @override
  State<TenantInvoicesScreen> createState() => _TenantInvoicesScreenState();
}

class _TenantInvoicesScreenState extends State<TenantInvoicesScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<TenantProvider>(context, listen: false).fetchInvoicesAndPayments();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<TenantProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Invoices & Payments'),
        bottom: TabBar(
          controller: _tabController,
          labelColor: AppTheme.primary,
          unselectedLabelColor: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight,
          indicatorColor: AppTheme.primary,
          tabs: const [
            Tab(text: 'Invoices'),
            Tab(text: 'Payment History'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          // Invoices Tab
          RefreshIndicator(
            onRefresh: () => prov.fetchInvoicesAndPayments(),
            child: prov.isLoading
                ? const Center(child: CircularProgressIndicator())
                : prov.invoices.isEmpty
                    ? const EmptyState(
                        icon: Icons.receipt_outlined,
                        title: 'No Invoices',
                        message: 'You have no outstanding or past invoices at this time.',
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: prov.invoices.length,
                        itemBuilder: (context, index) {
                          final inv = prov.invoices[index];
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
                                        inv.invoiceNumber,
                                        style: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.primary),
                                      ),
                                      StatusBadge(status: inv.status),
                                    ],
                                  ),
                                  const SizedBox(height: 8),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        inv.invoiceType.toUpperCase(),
                                        style: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.bold),
                                      ),
                                      Text(
                                        'TZS ${inv.amount.toInt()}',
                                        style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.primary),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    'Due Date: ${inv.dueDate ?? 'N/A'}',
                                    style: GoogleFonts.inter(fontSize: 12, color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight),
                                  ),
                                  if (inv.isPending) ...[
                                    const Divider(height: 20),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.end,
                                      children: [
                                        ElevatedButton.icon(
                                          icon: const Icon(Icons.upload_file, size: 16),
                                          label: const Text('Pay / Upload Proof', style: TextStyle(fontSize: 12)),
                                          onPressed: () {
                                            Navigator.pushNamed(
                                              context,
                                              '/tenant/payments/upload',
                                              arguments: inv,
                                            );
                                          },
                                          style: ElevatedButton.styleFrom(
                                            minimumSize: const Size(140, 36),
                                            padding: const EdgeInsets.symmetric(horizontal: 12),
                                          ),
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

          // Payments Tab
          RefreshIndicator(
            onRefresh: () => prov.fetchInvoicesAndPayments(),
            child: prov.isLoading
                ? const Center(child: CircularProgressIndicator())
                : prov.payments.isEmpty
                    ? const EmptyState(
                        icon: Icons.history,
                        title: 'No Payments Yet',
                        message: 'Your payment submission history will be displayed here.',
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: prov.payments.length,
                        itemBuilder: (context, index) {
                          final p = prov.payments[index];
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
                                        p.paymentReference,
                                        style: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.bold),
                                      ),
                                      StatusBadge(status: p.status),
                                    ],
                                  ),
                                  const SizedBox(height: 8),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        p.paymentMethod ?? 'Direct Transfer',
                                        style: GoogleFonts.inter(fontWeight: FontWeight.w600),
                                      ),
                                      Text(
                                        'TZS ${p.amount.toInt()}',
                                        style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.primary),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    'Paid Date: ${p.paidDate ?? p.createdAt?.substring(0, 10) ?? 'N/A'}',
                                    style: GoogleFonts.inter(fontSize: 12, color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight),
                                  ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          Navigator.pushNamed(context, '/tenant/payments/upload');
        },
        backgroundColor: AppTheme.primary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Upload Payment', style: TextStyle(color: Colors.white)),
      ),
    );
  }
}
