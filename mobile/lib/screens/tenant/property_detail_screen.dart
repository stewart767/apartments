import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../models/property_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/property_provider.dart';
import '../../widgets/app_button.dart';
import '../../widgets/status_badge.dart';

class PropertyDetailScreen extends StatefulWidget {
  final int propertyId;

  const PropertyDetailScreen({super.key, required this.propertyId});

  @override
  State<PropertyDetailScreen> createState() => _PropertyDetailScreenState();
}

class _PropertyDetailScreenState extends State<PropertyDetailScreen> {
  int _currentImageIndex = 0;
  final PageController _pageController = PageController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<PropertyProvider>(context, listen: false).fetchPropertyDetail(widget.propertyId);
    });
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  void _handleBookingPress(BuildContext context, PropertyModel prop) {
    final auth = Provider.of<AuthProvider>(context, listen: false);

    if (!auth.isAuthenticated || auth.currentUser == null) {
      // User is browsing as guest - prompt login or register
      _showAuthRequiredSheet(context, prop);
    } else {
      // Authenticated user - proceed to booking form
      Navigator.pushNamed(
        context,
        '/booking/form',
        arguments: prop,
      );
    }
  }

  void _showAuthRequiredSheet(BuildContext context, PropertyModel prop) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      builder: (ctx) {
        return Padding(
          padding: const EdgeInsets.fromLTRB(24, 28, 24, 32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Lock / Member Icon
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: AppTheme.primary.withValues(alpha: 0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.lock_person_rounded,
                  color: AppTheme.primary,
                  size: 38,
                ),
              ),
              const SizedBox(height: 20),

              // Title
              Text(
                'Sign In to Book Property',
                style: GoogleFonts.outfit(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.textMainLight,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),

              // Subtitle
              Text(
                'You can browse all apartments and hostels freely as a guest. To reserve "${prop.propertyName}" and submit your rental booking, please sign in or register an account.',
                style: GoogleFonts.inter(
                  fontSize: 13.5,
                  color: AppTheme.textMutedLight,
                  height: 1.45,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 28),

              // Sign In Button
              ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primary,
                  foregroundColor: Colors.white,
                  minimumSize: const Size.fromHeight(50),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  elevation: 0,
                ),
                icon: const Icon(Icons.login_rounded, size: 20),
                label: const Text(
                  'Sign In to Account',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                ),
                onPressed: () {
                  Navigator.pop(ctx);
                  Navigator.pushNamed(context, '/login');
                },
              ),
              const SizedBox(height: 12),

              // Create Account Button
              OutlinedButton.icon(
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppTheme.primary,
                  side: const BorderSide(color: AppTheme.primary, width: 1.5),
                  minimumSize: const Size.fromHeight(50),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
                icon: const Icon(Icons.person_add_rounded, size: 20),
                label: const Text(
                  'Create New Account',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                ),
                onPressed: () {
                  Navigator.pop(ctx);
                  Navigator.pushNamed(context, '/register');
                },
              ),
              const SizedBox(height: 14),

              // Continue Browsing Button
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: Text(
                  'Continue Browsing as Guest',
                  style: GoogleFonts.inter(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textMutedLight,
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<PropertyProvider>(context);
    final prop = prov.selectedProperty;

    if (prov.isLoading || prop == null) {
      return const Scaffold(
        backgroundColor: Colors.white,
        body: Center(child: CircularProgressIndicator(color: AppTheme.primary)),
      );
    }

    final allImages = prop.images.isNotEmpty
        ? prop.images
        : (prop.primaryImageUrl != null && prop.primaryImageUrl!.isNotEmpty ? [prop.primaryImageUrl!] : <String>[]);

    return Scaffold(
      backgroundColor: Colors.white,
      body: Stack(
        children: [
          CustomScrollView(
            slivers: [
              // Hero Image Header
              SliverAppBar(
                expandedHeight: 300,
                pinned: true,
                backgroundColor: Colors.white,
                surfaceTintColor: Colors.transparent,
                leading: Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: CircleAvatar(
                    backgroundColor: Colors.white.withValues(alpha: 0.9),
                    child: IconButton(
                      icon: const Icon(Icons.arrow_back_rounded, color: AppTheme.textMainLight, size: 20),
                      onPressed: () => Navigator.pop(context),
                    ),
                  ),
                ),
                flexibleSpace: FlexibleSpaceBar(
                  background: Stack(
                    fit: StackFit.expand,
                    children: [
                      allImages.isNotEmpty
                          ? PageView.builder(
                              controller: _pageController,
                              itemCount: allImages.length,
                              onPageChanged: (idx) => setState(() => _currentImageIndex = idx),
                              itemBuilder: (context, idx) {
                                return Image.network(
                                  allImages[idx],
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, __, ___) => _buildPlaceholderHeader(),
                                );
                              },
                            )
                          : _buildPlaceholderHeader(),

                      // Image Index Indicator Pill
                      if (allImages.length > 1)
                        Positioned(
                          bottom: 16,
                          right: 16,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.black.withValues(alpha: 0.7),
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: Text(
                              '${_currentImageIndex + 1} / ${allImages.length}',
                              style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ),

              // Main Details Content
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(20, 20, 20, 100),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Badge & Status Row
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                            decoration: BoxDecoration(
                              color: AppTheme.primary.withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: Text(
                              _formatRentStructure(prop.rentStructure),
                              style: GoogleFonts.inter(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: AppTheme.primary,
                              ),
                            ),
                          ),
                          StatusBadge(status: prop.status),
                        ],
                      ),
                      const SizedBox(height: 12),

                      // Property Title
                      Text(
                        prop.propertyName,
                        style: GoogleFonts.outfit(
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.textMainLight,
                          height: 1.25,
                        ),
                      ),
                      const SizedBox(height: 8),

                      // Address Row
                      Row(
                        children: [
                          const Icon(Icons.location_on_rounded, color: AppTheme.primary, size: 16),
                          const SizedBox(width: 4),
                          Expanded(
                            child: Text(
                              prop.address,
                              style: GoogleFonts.inter(
                                fontSize: 13,
                                color: AppTheme.textMutedLight,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),

                      // Highlight Specs Row
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: AppTheme.borderLight),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceAround,
                          children: [
                            _buildSpecItem(Icons.meeting_room_outlined, '${prop.totalRooms} Rooms', 'Total Units'),
                            Container(width: 1, height: 32, color: AppTheme.borderLight),
                            _buildSpecItem(Icons.hotel_outlined, '${prop.totalBeds} Beds', 'Capacity'),
                            Container(width: 1, height: 32, color: AppTheme.borderLight),
                            _buildSpecItem(Icons.calendar_today_outlined, prop.billingPeriod.toUpperCase(), 'Billing Cycle'),
                          ],
                        ),
                      ),
                      const SizedBox(height: 24),

                      // About Section
                      Text(
                        'About This Accommodation',
                        style: GoogleFonts.outfit(fontSize: 17, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        prop.description != null && prop.description!.isNotEmpty
                            ? prop.description!
                            : 'This property offers fully verified, high-quality accommodation with modern amenities and reliable management.',
                        style: GoogleFonts.inter(
                          fontSize: 13.5,
                          height: 1.55,
                          color: const Color(0xFF334155),
                        ),
                      ),
                      const SizedBox(height: 24),

                      // Amenities Grid
                      if (prop.amenities.isNotEmpty) ...[
                        Text(
                          'Features & Amenities',
                          style: GoogleFonts.outfit(fontSize: 17, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 12),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: prop.amenities.map((am) {
                            return Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: AppTheme.borderLight),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(Icons.check_circle_rounded, size: 15, color: AppTheme.primary),
                                  const SizedBox(width: 6),
                                  Text(
                                    am.name,
                                    style: GoogleFonts.inter(fontSize: 12.5, fontWeight: FontWeight.w500),
                                  ),
                                ],
                              ),
                            );
                          }).toList(),
                        ),
                        const SizedBox(height: 24),
                      ],

                      // Rooms Breakdown
                      if (prop.rooms.isNotEmpty) ...[
                        Text(
                          'Available Units & Rooms',
                          style: GoogleFonts.outfit(fontSize: 17, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 12),
                        ...prop.rooms.map((room) {
                          return Container(
                            margin: const EdgeInsets.only(bottom: 10),
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF8FAFC),
                              borderRadius: BorderRadius.circular(14),
                              border: Border.all(color: AppTheme.borderLight),
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Room ${room.roomNumber}',
                                      style: GoogleFonts.inter(fontSize: 14, fontWeight: FontWeight.bold),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      'Capacity: ${room.capacity} beds | TZS ${_formatNumber(room.price)}',
                                      style: GoogleFonts.inter(fontSize: 12, color: AppTheme.textMutedLight),
                                    ),
                                  ],
                                ),
                                StatusBadge(status: room.status),
                              ],
                            ),
                          );
                        }),
                      ],
                    ],
                  ),
                ),
              ),
            ],
          ),

          // Sticky Bottom Booking Bar
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: Container(
              padding: const EdgeInsets.fromLTRB(20, 14, 20, 20),
              decoration: BoxDecoration(
                color: Colors.white,
                border: const Border(top: BorderSide(color: AppTheme.borderLight, width: 1)),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.06),
                    blurRadius: 16,
                    offset: const Offset(0, -4),
                  ),
                ],
              ),
              child: SafeArea(
                top: false,
                child: Row(
                  children: [
                    // Price info
                    Expanded(
                      flex: 4,
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'TZS ${_formatNumber(prop.price)}',
                            style: GoogleFonts.outfit(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.primary,
                            ),
                          ),
                          Text(
                            'per ${prop.billingPeriod}',
                            style: GoogleFonts.inter(
                              fontSize: 12,
                              color: AppTheme.textMutedLight,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 14),

                    // Book Now Button
                    Expanded(
                      flex: 6,
                      child: AppButton(
                        text: 'Book / Reserve Now',
                        icon: Icons.calendar_today_rounded,
                        onPressed: () => _handleBookingPress(context, prop),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSpecItem(IconData icon, String value, String label) {
    return Column(
      children: [
        Icon(icon, color: AppTheme.primary, size: 20),
        const SizedBox(height: 4),
        Text(value, style: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.bold)),
        Text(label, style: GoogleFonts.inter(fontSize: 10, color: AppTheme.textMutedLight)),
      ],
    );
  }

  Widget _buildPlaceholderHeader() {
    return Container(
      color: const Color(0xFFF1F5F9),
      child: Center(
        child: Icon(Icons.apartment_rounded, size: 80, color: AppTheme.primary.withValues(alpha: 0.3)),
      ),
    );
  }

  String _formatRentStructure(String rentStructure) {
    switch (rentStructure) {
      case 'per_bed':
        return 'Hostel Bed Space';
      case 'per_room':
        return 'Private Room';
      case 'entire_property':
        return 'Entire House / Apartment';
      default:
        return 'Apartment';
    }
  }

  String _formatNumber(double number) {
    return number.toStringAsFixed(0).replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]},',
    );
  }
}
