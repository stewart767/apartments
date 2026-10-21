import 'dart:io';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../core/constants.dart';
import '../../providers/owner_provider.dart';
import '../../providers/property_provider.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_text_field.dart';

class CreatePropertyScreen extends StatefulWidget {
  const CreatePropertyScreen({super.key});

  @override
  State<CreatePropertyScreen> createState() => _CreatePropertyScreenState();
}

class _CreatePropertyScreenState extends State<CreatePropertyScreen> {
  final _formKey = GlobalKey<FormState>();
  final _picker = ImagePicker();

  final _nameController = TextEditingController();
  final _addressController = TextEditingController();
  final _cityController = TextEditingController(text: 'Dar es Salaam');
  final _priceController = TextEditingController();
  final _depositController = TextEditingController();
  final _descController = TextEditingController();

  String _rentStructure = AppConstants.rentPerBed;
  String _propertyType = 'hostel';
  String _billingPeriod = 'monthly';
  final Set<int> _selectedAmenities = {};
  final List<File> _images = [];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<PropertyProvider>(context, listen: false).fetchMetadata();
    });
  }

  @override
  void dispose() {
    _nameController.dispose();
    _addressController.dispose();
    _cityController.dispose();
    _priceController.dispose();
    _depositController.dispose();
    _descController.dispose();
    super.dispose();
  }

  Future<void> _pickImage(ImageSource source) async {
    try {
      final picked = await _picker.pickImage(source: source, imageQuality: 80);
      if (picked != null) {
        setState(() => _images.add(File(picked.path)));
      }
    } catch (_) {}
  }

  Future<void> _handleSubmit() async {
    if (!_formKey.currentState!.validate()) return;

    final prov = Provider.of<OwnerProvider>(context, listen: false);
    final success = await prov.createProperty(
      propertyName: _nameController.text.trim(),
      propertyType: _propertyType,
      rentStructure: _rentStructure,
      address: _addressController.text.trim(),
      city: _cityController.text.trim(),
      price: double.tryParse(_priceController.text) ?? 0.0,
      depositAmount: double.tryParse(_depositController.text) ?? 0.0,
      billingPeriod: _billingPeriod,
      description: _descController.text.trim(),
      amenityIds: _selectedAmenities.toList(),
      images: _images.isNotEmpty ? _images : null,
    );

    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Property created successfully!'), backgroundColor: AppTheme.success),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(prov.errorMessage ?? 'Creation failed.'), backgroundColor: AppTheme.error),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final meta = Provider.of<PropertyProvider>(context);
    final prov = Provider.of<OwnerProvider>(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Add New Property'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              AppTextField(
                label: 'Property Name / Hostel Title',
                hint: 'e.g. Mlimani Student Hostel',
                controller: _nameController,
                validator: (val) => val == null || val.trim().isEmpty ? 'Property name is required' : null,
              ),
              const SizedBox(height: 16),

              Text('Rental Model / Structure', style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13)),
              const SizedBox(height: 6),
              DropdownButtonFormField<String>(
                value: _rentStructure,
                decoration: const InputDecoration(),
                items: const [
                  DropdownMenuItem(value: 'per_bed', child: Text('Hostel / Per Bed')),
                  DropdownMenuItem(value: 'per_room', child: Text('Apartment / Per Room')),
                  DropdownMenuItem(value: 'entire_property', child: Text('Entire House / Property')),
                  DropdownMenuItem(value: 'mixed', child: Text('Mixed Rental Structure')),
                ],
                onChanged: (val) => setState(() => _rentStructure = val ?? 'per_bed'),
              ),
              const SizedBox(height: 16),

              AppTextField(
                label: 'Street Address & Location',
                hint: 'e.g. Sam Nujoma Road, Survey',
                controller: _addressController,
                validator: (val) => val == null || val.trim().isEmpty ? 'Address is required' : null,
              ),
              const SizedBox(height: 16),

              AppTextField(
                label: 'City',
                hint: 'Dar es Salaam',
                controller: _cityController,
              ),
              const SizedBox(height: 16),

              Row(
                children: [
                  Expanded(
                    child: AppTextField(
                      label: 'Base Price (TZS)',
                      hint: '150000',
                      controller: _priceController,
                      keyboardType: TextInputType.number,
                      validator: (val) => val == null || val.isEmpty ? 'Price is required' : null,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: AppTextField(
                      label: 'Security Deposit (TZS)',
                      hint: '50000',
                      controller: _depositController,
                      keyboardType: TextInputType.number,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              AppTextField(
                label: 'Description & Features',
                hint: 'Describe proximity to campus, security features, water availability...',
                controller: _descController,
                maxLines: 4,
              ),
              const SizedBox(height: 20),

              // Amenities Multi-Select
              if (meta.amenities.isNotEmpty) ...[
                Text('Select Included Amenities', style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13)),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: meta.amenities.map((a) {
                    final isSel = _selectedAmenities.contains(a.id);
                    return FilterChip(
                      label: Text(a.name),
                      selected: isSel,
                      onSelected: (val) {
                        setState(() {
                          if (val) {
                            _selectedAmenities.add(a.id);
                          } else {
                            _selectedAmenities.remove(a.id);
                          }
                        });
                      },
                    );
                  }).toList(),
                ),
                const SizedBox(height: 20),
              ],

              // Photos
              Text('Property Images', style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 13)),
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
                      icon: const Icon(Icons.camera_alt),
                      label: const Text('Add Camera Photo'),
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
              const SizedBox(height: 32),

              AppButton(
                text: 'Publish Listing',
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
