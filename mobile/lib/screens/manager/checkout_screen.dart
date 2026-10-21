import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../providers/manager_provider.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_text_field.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _formKey = GlobalKey<FormState>();

  final _contractIdController = TextEditingController();
  final _damageChargesController = TextEditingController(text: '0');
  final _depositRefundController = TextEditingController();
  final _notesController = TextEditingController();
  DateTime _checkOutDate = DateTime.now();
  bool _settled = true;

  @override
  void dispose() {
    _contractIdController.dispose();
    _damageChargesController.dispose();
    _depositRefundController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _handleSubmit() async {
    if (!_formKey.currentState!.validate()) return;

    final prov = Provider.of<ManagerProvider>(context, listen: false);
    final dateFormat = DateFormat('yyyy-MM-dd');

    final success = await prov.executeCheckout(
      contractId: int.parse(_contractIdController.text.trim()),
      checkOutDate: dateFormat.format(_checkOutDate),
      damageCharges: double.tryParse(_damageChargesController.text) ?? 0.0,
      depositRefunded: double.tryParse(_depositRefundController.text) ?? 0.0,
      outstandingBalanceSettled: _settled,
      finalInspectionNotes: _notesController.text.trim().isNotEmpty ? _notesController.text.trim() : null,
    );

    if (!mounted) return;

    if (success) {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => AlertDialog(
          title: const Text('Check-out Recorded!'),
          content: const Text(
            'Tenant check-out has been completed. The unit/bed is now freed and available for new bookings.',
          ),
          actions: [
            ElevatedButton(
              onPressed: () {
                Navigator.pop(ctx);
                Navigator.pop(context);
              },
              child: const Text('Done'),
            ),
          ],
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(prov.errorMessage ?? 'Check-out failed.'), backgroundColor: AppTheme.error),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<ManagerProvider>(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Digital Tenant Check-out'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              AppTextField(
                label: 'Contract ID / Reference #',
                hint: 'e.g. 1',
                controller: _contractIdController,
                keyboardType: TextInputType.number,
                validator: (val) => val == null || val.isEmpty ? 'Contract ID is required' : null,
              ),
              const SizedBox(height: 16),

              Row(
                children: [
                  Expanded(
                    child: AppTextField(
                      label: 'Damage Deductions (TZS)',
                      hint: '0',
                      controller: _damageChargesController,
                      keyboardType: TextInputType.number,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: AppTextField(
                      label: 'Deposit Refunded (TZS)',
                      hint: '50000',
                      controller: _depositRefundController,
                      keyboardType: TextInputType.number,
                      validator: (val) => val == null || val.isEmpty ? 'Refund amount required' : null,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              SwitchListTile(
                contentPadding: EdgeInsets.zero,
                title: Text('Outstanding Invoices & Utilities Settled', style: GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.w500)),
                value: _settled,
                activeColor: AppTheme.primary,
                onChanged: (val) => setState(() => _settled = val),
              ),
              const SizedBox(height: 16),

              AppTextField(
                label: 'Final Inspection & Inventory Notes',
                hint: 'Note key return, cleanliness, condition of room inventory and assets...',
                controller: _notesController,
                maxLines: 4,
              ),
              const SizedBox(height: 32),

              AppButton(
                text: 'Complete Check-out & Free Unit',
                onPressed: _handleSubmit,
                isLoading: prov.isLoading,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
