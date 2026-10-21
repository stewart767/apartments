import 'dart:io';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../providers/tenant_provider.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_text_field.dart';

class CreateMaintenanceScreen extends StatefulWidget {
  const CreateMaintenanceScreen({super.key});

  @override
  State<CreateMaintenanceScreen> createState() => _CreateMaintenanceScreenState();
}

class _CreateMaintenanceScreenState extends State<CreateMaintenanceScreen> {
  final _formKey = GlobalKey<FormState>();
  final _picker = ImagePicker();

  int? _selectedPropertyId;
  final _titleController = TextEditingController();
  final _descController = TextEditingController();
  String _priority = 'medium';
  final List<File> _images = [];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<TenantProvider>(context, listen: false).fetchContracts();
    });
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descController.dispose();
    super.dispose();
  }

  Future<void> _pickImage(ImageSource source) async {
    if (_images.length >= 5) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Maximum 5 images allowed per ticket.')),
      );
      return;
    }
    try {
      final picked = await _picker.pickImage(source: source, imageQuality: 75);
      if (picked != null) {
        setState(() => _images.add(File(picked.path)));
      }
    } catch (_) {}
  }

  Future<void> _handleSubmit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedPropertyId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select a property/room.'), backgroundColor: AppTheme.error),
      );
      return;
    }

    final prov = Provider.of<TenantProvider>(context, listen: false);
    final success = await prov.createMaintenanceTicket(
      propertyId: _selectedPropertyId!,
      title: _titleController.text.trim(),
      description: _descController.text.trim(),
      priority: _priority,
      images: _images.isNotEmpty ? _images : null,
    );

    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Maintenance ticket created!'), backgroundColor: AppTheme.success),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(prov.errorMessage ?? 'Submission failed.'), backgroundColor: AppTheme.error),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<TenantProvider>(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Report Maintenance Issue'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Select Property / Tenancy', style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13)),
              const SizedBox(height: 6),
              DropdownButtonFormField<int>(
                value: _selectedPropertyId,
                decoration: const InputDecoration(hintText: 'Choose tenancy'),
                items: prov.contracts.map((c) {
                  return DropdownMenuItem<int>(
                    value: c.propertyId,
                    child: Text('${c.property?['property_name'] ?? 'Property'} (${c.contractNumber})'),
                  );
                }).toList(),
                onChanged: (val) => setState(() => _selectedPropertyId = val),
                validator: (val) => val == null ? 'Please select a tenancy' : null,
              ),
              const SizedBox(height: 16),

              AppTextField(
                label: 'Issue Title / Summary',
                hint: 'e.g. Leaking bathroom faucet',
                controller: _titleController,
                validator: (val) => val == null || val.trim().isEmpty ? 'Title is required' : null,
              ),
              const SizedBox(height: 16),

              Text('Priority Level', style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13)),
              const SizedBox(height: 6),
              DropdownButtonFormField<String>(
                value: _priority,
                decoration: const InputDecoration(),
                items: const [
                  DropdownMenuItem(value: 'low', child: Text('Low - General inquiry / minor wear')),
                  DropdownMenuItem(value: 'medium', child: Text('Medium - Standard repair needed')),
                  DropdownMenuItem(value: 'high', child: Text('High - Affects daily living')),
                  DropdownMenuItem(value: 'emergency', child: Text('Emergency - Water leak / Power hazard')),
                ],
                onChanged: (val) => setState(() => _priority = val ?? 'medium'),
              ),
              const SizedBox(height: 16),

              AppTextField(
                label: 'Detailed Description',
                hint: 'Describe the issue clearly, including location in the room and when it started...',
                controller: _descController,
                maxLines: 4,
                validator: (val) => val == null || val.trim().isEmpty ? 'Description is required' : null,
              ),
              const SizedBox(height: 20),

              Text('Attach Photos (Up to 5)', style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13)),
              const SizedBox(height: 8),
              if (_images.isNotEmpty) ...[
                SizedBox(
                  height: 90,
                  child: ListView.builder(
                    scrollDirection: Axis.horizontal,
                    itemCount: _images.length,
                    itemBuilder: (context, idx) {
                      return Stack(
                        alignment: Alignment.topRight,
                        children: [
                          Container(
                            width: 90,
                            margin: const EdgeInsets.only(right: 8),
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(8),
                              image: DecorationImage(image: FileImage(_images[idx]), fit: BoxFit.cover),
                            ),
                          ),
                          IconButton(
                            icon: const CircleAvatar(
                              radius: 10,
                              backgroundColor: Colors.black54,
                              child: Icon(Icons.close, size: 12, color: Colors.white),
                            ),
                            onPressed: () => setState(() => _images.removeAt(idx)),
                          ),
                        ],
                      );
                    },
                  ),
                ),
                const SizedBox(height: 12),
              ],
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      icon: const Icon(Icons.camera_alt, size: 18),
                      label: const Text('Camera'),
                      onPressed: () => _pickImage(ImageSource.camera),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: OutlinedButton.icon(
                      icon: const Icon(Icons.photo_library, size: 18),
                      label: const Text('Gallery'),
                      onPressed: () => _pickImage(ImageSource.gallery),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 32),

              AppButton(
                text: 'Submit Ticket',
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
