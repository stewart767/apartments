import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../models/property_model.dart';
import '../../providers/tenant_provider.dart';
import '../../widgets/app_button.dart';
import '../../widgets/app_text_field.dart';
import '../../widgets/price_card.dart';

class BookingFormScreen extends StatefulWidget {
  final PropertyModel property;

  const BookingFormScreen({super.key, required this.property});

  @override
  State<BookingFormScreen> createState() => _BookingFormScreenState();
}

class _BookingFormScreenState extends State<BookingFormScreen> {
  final _formKey = GlobalKey<FormState>();

  int? _selectedRoomId;
  int? _selectedBedId;
  DateTime _startDate = DateTime.now().add(const Duration(days: 1));
  DateTime _endDate = DateTime.now().add(const Duration(days: 180));
  final _notesController = TextEditingController();

  RoomModel? get _selectedRoom {
    if (_selectedRoomId == null) return null;
    try {
      return widget.property.rooms.firstWhere((r) => r.id == _selectedRoomId);
    } catch (_) {
      return null;
    }
  }

  BedModel? get _selectedBed {
    final room = _selectedRoom;
    if (room == null || _selectedBedId == null) return null;
    try {
      return room.beds.firstWhere((b) => b.id == _selectedBedId);
    } catch (_) {
      return null;
    }
  }

  double get _calculatedPrice {
    if (_selectedBed != null && _selectedBed!.price > 0) return _selectedBed!.price;
    if (_selectedRoom != null && _selectedRoom!.price > 0) return _selectedRoom!.price;
    return widget.property.price;
  }

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _pickDate(bool isStart) async {
    final initialDate = isStart ? _startDate : _endDate;
    final firstDate = isStart ? DateTime.now() : _startDate.add(const Duration(days: 1));
    final lastDate = DateTime.now().add(const Duration(days: 730));

    final picked = await showDatePicker(
      context: context,
      initialDate: initialDate,
      firstDate: firstDate,
      lastDate: lastDate,
    );

    if (picked != null) {
      setState(() {
        if (isStart) {
          _startDate = picked;
          if (_endDate.isBefore(_startDate)) {
            _endDate = _startDate.add(const Duration(days: 180));
          }
        } else {
          _endDate = picked;
        }
      });
    }
  }

  Future<void> _handleSubmit() async {
    if (!_formKey.currentState!.validate()) return;

    final tenantProv = Provider.of<TenantProvider>(context, listen: false);
    final dateFormat = DateFormat('yyyy-MM-dd');

    final success = await tenantProv.submitApplication(
      propertyId: widget.property.id,
      roomId: _selectedRoomId,
      bedId: _selectedBedId,
      startDate: dateFormat.format(_startDate),
      endDate: dateFormat.format(_endDate),
      tenantNotes: _notesController.text.trim().isNotEmpty ? _notesController.text.trim() : null,
    );

    if (!mounted) return;

    if (success) {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => AlertDialog(
          title: const Text('Application Submitted!'),
          content: const Text(
            'Your booking application has been received. The property owner will review your application and generate your digital contract upon approval.',
          ),
          actions: [
            ElevatedButton(
              onPressed: () {
                Navigator.pop(ctx);
                Navigator.pushReplacementNamed(context, '/tenant/applications');
              },
              child: const Text('View Applications'),
            ),
          ],
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(tenantProv.errorMessage ?? 'Submission failed. Please check availability.'),
          backgroundColor: AppTheme.error,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final tenantProv = Provider.of<TenantProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final dateFormat = DateFormat('dd MMM yyyy');

    return Scaffold(
      appBar: AppBar(
        title: const Text('Booking Application'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Property Info Banner
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: isDark ? AppTheme.cardDark : Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: isDark ? AppTheme.borderDark : AppTheme.borderLight),
                ),
                child: Row(
                  children: [
                    Container(
                      width: 50,
                      height: 50,
                      decoration: BoxDecoration(
                        color: AppTheme.primary.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(Icons.apartment, color: AppTheme.primary),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            widget.property.propertyName,
                            style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 16),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            widget.property.address,
                            style: GoogleFonts.inter(
                              fontSize: 12,
                              color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Room Selector (if property has rooms)
              if (widget.property.rooms.isNotEmpty) ...[
                Text('Select Room / Unit', style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 14)),
                const SizedBox(height: 8),
                DropdownButtonFormField<int>(
                  value: _selectedRoomId,
                  decoration: const InputDecoration(hintText: 'Choose a room'),
                  items: widget.property.rooms.map((room) {
                    return DropdownMenuItem<int>(
                      value: room.id,
                      child: Text('Room ${room.roomNumber} (${room.status.toUpperCase()})'),
                    );
                  }).toList(),
                  onChanged: (val) {
                    setState(() {
                      _selectedRoomId = val;
                      _selectedBedId = null;
                    });
                  },
                ),
                const SizedBox(height: 20),
              ],

              // Bed Selector (if per_bed and room has beds)
              if (widget.property.isPerBed && _selectedRoom != null && _selectedRoom!.beds.isNotEmpty) ...[
                Text('Select Bed Number', style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 14)),
                const SizedBox(height: 8),
                DropdownButtonFormField<int>(
                  value: _selectedBedId,
                  decoration: const InputDecoration(hintText: 'Choose a bed'),
                  items: _selectedRoom!.beds.map((bed) {
                    return DropdownMenuItem<int>(
                      value: bed.id,
                      child: Text('Bed ${bed.bedNumber} - ${bed.status.toUpperCase()}'),
                    );
                  }).toList(),
                  onChanged: (val) => setState(() => _selectedBedId = val),
                ),
                const SizedBox(height: 20),
              ],

              // Move-in & Move-out Dates
              Text('Rental Duration', style: GoogleFonts.inter(fontWeight: FontWeight.w600, fontSize: 14)),
              const SizedBox(height: 8),
              Row(
                children: [
                  Expanded(
                    child: InkWell(
                      onTap: () => _pickDate(true),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        decoration: BoxDecoration(
                          color: isDark ? AppTheme.cardDark : Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: isDark ? AppTheme.borderDark : AppTheme.borderLight),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Move-in Date', style: GoogleFonts.inter(fontSize: 11, color: AppTheme.textMutedLight)),
                            const SizedBox(height: 4),
                            Text(dateFormat.format(_startDate), style: GoogleFonts.inter(fontWeight: FontWeight.w600)),
                          ],
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: InkWell(
                      onTap: () => _pickDate(false),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        decoration: BoxDecoration(
                          color: isDark ? AppTheme.cardDark : Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: isDark ? AppTheme.borderDark : AppTheme.borderLight),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Move-out Date', style: GoogleFonts.inter(fontSize: 11, color: AppTheme.textMutedLight)),
                            const SizedBox(height: 4),
                            Text(dateFormat.format(_endDate), style: GoogleFonts.inter(fontWeight: FontWeight.w600)),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 20),

              // Notes
              AppTextField(
                label: 'Notes for Property Owner (Optional)',
                hint: 'e.g. Special requests, preferred move-in time...',
                controller: _notesController,
                maxLines: 3,
              ),
              const SizedBox(height: 24),

              // Price Summary
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppTheme.primary.withOpacity(0.08),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppTheme.primary.withOpacity(0.2)),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Estimated Rent', style: GoogleFonts.inter(fontSize: 12, color: AppTheme.primary)),
                        const SizedBox(height: 2),
                        PriceCard(
                          price: _calculatedPrice,
                          billingPeriod: widget.property.billingPeriod,
                          fontSize: 18,
                        ),
                      ],
                    ),
                    const Icon(Icons.verified_outlined, color: AppTheme.primary),
                  ],
                ),
              ),
              const SizedBox(height: 32),

              // Submit Button
              AppButton(
                text: 'Submit Application',
                onPressed: _handleSubmit,
                isLoading: tenantProv.isLoading,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
