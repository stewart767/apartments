import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../providers/manager_provider.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_text_field.dart';

class CheckinScreen extends StatefulWidget {
  const CheckinScreen({super.key});

  @override
  State<CheckinScreen> createState() => _CheckinScreenState();
}

class _CheckinScreenState extends State<CheckinScreen> {
  final _formKey = GlobalKey<FormState>();

  final _contractIdController = TextEditingController();
  final _depositController = TextEditingController();
  final _notesController = TextEditingController();
  final _keyDetailsController = TextEditingController();
  DateTime _checkInDate = DateTime.now();

  @override
  void dispose() {
    _contractIdController.dispose();
    _depositController.dispose();
    _notesController.dispose();
    _keyDetailsController.dispose();
    super.dispose();
  }

  Future<void> _handleSubmit() async {
    if (!_formKey.currentState!.validate()) return;

    final prov = Provider.of<ManagerProvider>(context, listen: false);
    final dateFormat = DateFormat('yyyy-MM-dd');

    final success = await prov.executeCheckin(
      contractId: int.parse(_contractIdController.text.trim()),
      checkInDate: dateFormat.format(_checkInDate),
      depositReceived: double.tryParse(_depositController.text) ?? 0.0,
      roomConditionNotes: _notesController.text.trim().isNotEmpty ? _notesController.text.trim() : null,
      keyHandoverDetails: _keyDetailsController.text.trim().isNotEmpty
          ? {'keys_issued': _keyDetailsController.text.trim()}
          : null,
    );

    if (!mounted) return;

    if (success) {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => AlertDialog(
          title: const Text('Check-in Completed!'),
          content: const Text('Tenant check-in recorded successfully. Room and bed status set to occupied.'),
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
        SnackBar(content: Text(prov.errorMessage ?? 'Check-in failed.'), backgroundColor: AppTheme.error),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<ManagerProvider>(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Digital Tenant Check-in'),
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

              AppTextField(
                label: 'Security Deposit Received (TZS)',
                hint: '50000',
                controller: _depositController,
                keyboardType: TextInputType.number,
                validator: (val) => val == null || val.isEmpty ? 'Deposit amount is required' : null,
              ),
              const SizedBox(height: 16),

              AppTextField(
                label: 'Key & Access Tag Handover Details',
                hint: 'e.g. Room key #104, Front gate RFID fob #22',
                controller: _keyDetailsController,
              ),
              const SizedBox(height: 16),

              AppTextField(
                label: 'Baseline Room Condition Notes',
                hint: 'Note existing paint condition, lights, mattress status, windows...',
                controller: _notesController,
                maxLines: 4,
              ),
              const SizedBox(height: 32),

              AppButton(
                text: 'Execute & Activate Tenancy',
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
