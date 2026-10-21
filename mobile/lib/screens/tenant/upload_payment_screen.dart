import 'dart:io';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../models/invoice_model.dart';
import '../../providers/tenant_provider.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_text_field.dart';

class UploadPaymentScreen extends StatefulWidget {
  final InvoiceModel? initialInvoice;

  const UploadPaymentScreen({super.key, this.initialInvoice});

  @override
  State<UploadPaymentScreen> createState() => _UploadPaymentScreenState();
}

class _UploadPaymentScreenState extends State<UploadPaymentScreen> {
  final _formKey = GlobalKey<FormState>();
  final _picker = ImagePicker();

  int? _selectedContractId;
  int? _selectedInvoiceId;
  String _paymentMethod = 'mpesa';
  final _amountController = TextEditingController();
  final _referenceController = TextEditingController();
  File? _receiptFile;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final prov = Provider.of<TenantProvider>(context, listen: false);
      prov.fetchContracts();
      prov.fetchInvoicesAndPayments();
    });

    if (widget.initialInvoice != null) {
      _selectedInvoiceId = widget.initialInvoice!.id;
      _selectedContractId = widget.initialInvoice!.contractId;
      _amountController.text = widget.initialInvoice!.amount.toInt().toString();
    }
  }

  @override
  void dispose() {
    _amountController.dispose();
    _referenceController.dispose();
    super.dispose();
  }

  Future<void> _pickImage(ImageSource source) async {
    try {
      final picked = await _picker.pickImage(source: source, imageQuality: 80);
      if (picked != null) {
        setState(() => _receiptFile = File(picked.path));
      }
    } catch (_) {}
  }

  Future<void> _handleSubmit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedContractId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select an active contract.'), backgroundColor: AppTheme.error),
      );
      return;
    }
    if (_receiptFile == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please attach payment receipt or screenshot.'), backgroundColor: AppTheme.error),
      );
      return;
    }

    final prov = Provider.of<TenantProvider>(context, listen: false);
    final success = await prov.uploadPaymentProof(
      contractId: _selectedContractId!,
      invoiceId: _selectedInvoiceId,
      amount: double.tryParse(_amountController.text) ?? 0.0,
      paymentMethod: _paymentMethod,
      reference: _referenceController.text.trim().isNotEmpty ? _referenceController.text.trim() : null,
      receiptFile: _receiptFile!,
    );

    if (!mounted) return;

    if (success) {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => AlertDialog(
          title: const Text('Payment Uploaded!'),
          content: const Text(
            'Your payment proof has been submitted for owner review and verification. Your status will update once confirmed.',
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
        SnackBar(
          content: Text(prov.errorMessage ?? 'Payment submission failed.'),
          backgroundColor: AppTheme.error,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<TenantProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Upload Payment Proof'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Contract selection
              Text('Select Active Tenancy Contract', style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13)),
              const SizedBox(height: 6),
              DropdownButtonFormField<int>(
                value: _selectedContractId,
                decoration: const InputDecoration(hintText: 'Choose contract'),
                items: prov.contracts.map((c) {
                  return DropdownMenuItem<int>(
                    value: c.id,
                    child: Text('${c.contractNumber} (${c.property?['property_name'] ?? 'Property'})'),
                  );
                }).toList(),
                onChanged: (val) {
                  setState(() {
                    _selectedContractId = val;
                  });
                },
                validator: (val) => val == null ? 'Contract is required' : null,
              ),
              const SizedBox(height: 16),

              // Payment Method
              Text('Payment Method', style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13)),
              const SizedBox(height: 6),
              DropdownButtonFormField<String>(
                value: _paymentMethod,
                decoration: const InputDecoration(),
                items: const [
                  DropdownMenuItem(value: 'mpesa', child: Text('Vodacom M-Pesa')),
                  DropdownMenuItem(value: 'tigopesa', child: Text('Tigo Pesa')),
                  DropdownMenuItem(value: 'airtelmoney', child: Text('Airtel Money')),
                  DropdownMenuItem(value: 'bank_transfer', child: Text('Bank Wire / Transfer')),
                  DropdownMenuItem(value: 'cash', child: Text('Cash Handover')),
                ],
                onChanged: (val) => setState(() => _paymentMethod = val ?? 'mpesa'),
              ),
              const SizedBox(height: 16),

              // Amount
              AppTextField(
                label: 'Amount Paid (TZS)',
                hint: '150000',
                controller: _amountController,
                keyboardType: TextInputType.number,
                validator: (val) {
                  if (val == null || val.isEmpty) return 'Amount is required';
                  if (double.tryParse(val) == null || double.parse(val) <= 0) return 'Enter a valid amount';
                  return null;
                },
              ),
              const SizedBox(height: 16),

              // Reference
              AppTextField(
                label: 'Mobile Transaction ID / Reference (Optional)',
                hint: 'e.g. 9B3X892K1',
                controller: _referenceController,
              ),
              const SizedBox(height: 20),

              // Receipt Image Picker
              Text('Attach Bank / Mobile Money Receipt', style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13)),
              const SizedBox(height: 8),
              if (_receiptFile != null) ...[
                Stack(
                  alignment: Alignment.topRight,
                  children: [
                    Container(
                      height: 180,
                      width: double.infinity,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(12),
                        image: DecorationImage(
                          image: FileImage(_receiptFile!),
                          fit: BoxFit.cover,
                        ),
                      ),
                    ),
                    IconButton(
                      icon: const CircleAvatar(
                        backgroundColor: Colors.black54,
                        child: Icon(Icons.close, color: Colors.white, size: 18),
                      ),
                      onPressed: () => setState(() => _receiptFile = null),
                    ),
                  ],
                ),
              ] else ...[
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        icon: const Icon(Icons.camera_alt),
                        label: const Text('Take Photo'),
                        onPressed: () => _pickImage(ImageSource.camera),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: OutlinedButton.icon(
                        icon: const Icon(Icons.photo_library),
                        label: const Text('From Gallery'),
                        onPressed: () => _pickImage(ImageSource.gallery),
                      ),
                    ),
                  ],
                ),
              ],
              const SizedBox(height: 32),

              // Submit Button
              AppButton(
                text: 'Submit Payment Proof',
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
