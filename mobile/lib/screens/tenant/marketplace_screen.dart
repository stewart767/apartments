import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../models/property_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/property_provider.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/empty_state.dart';

class MarketplaceScreen extends StatefulWidget {
  const MarketplaceScreen({super.key});

  @override
  State<MarketplaceScreen> createState() => _MarketplaceScreenState();
}

class _MarketplaceScreenState extends State<MarketplaceScreen> {
  final _searchController = TextEditingController();
  String _selectedCategory = 'all';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final prov = Provider.of<PropertyProvider>(context, listen: false);
      prov.fetchProperties();
      prov.fetchMetadata();
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _onCategorySelected(String categoryKey, String? rentStructure) {
    setState(() {
      _selectedCategory = categoryKey;
    });
    final prov = Provider.of<PropertyProvider>(context, listen: false);
    prov.setFilter(rentStructure: rentStructure);
  }

  void _openFilterModal() {
    final prov = Provider.of<PropertyProvider>(context, listen: false);
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Container(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
              height: MediaQuery.of(context).size.height * 0.75,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Filter Accommodations',
                        style: GoogleFonts.outfit(fontSize: 20, fontWeight: FontWeight.bold),
                      ),
                      TextButton(
                        onPressed: () {
                          prov.resetFilters();
                          setState(() => _selectedCategory = 'all');
                          Navigator.pop(context);
                        },
                        child: Text(
                          'Reset All',
                          style: GoogleFonts.inter(fontWeight: FontWeight.w600, color: AppTheme.error),
                        ),
                      ),
                    ],
                  ),
                  const Divider(color: AppTheme.borderLight),
                  const SizedBox(height: 12),
                  Expanded(
                    child: ListView(
                      children: [
                        Text('Accommodation Type', style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 14)),
                        const SizedBox(height: 10),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: [
                            _buildChoiceChip('All Types', prov.selectedRentStructure == null, () {
                              setModalState(() {
                                prov.setFilter(rentStructure: null);
                                _selectedCategory = 'all';
                              });
                            }),
                            _buildChoiceChip('Hostel / Per Bed', prov.selectedRentStructure == 'per_bed', () {
                              setModalState(() {
                                prov.setFilter(rentStructure: 'per_bed');
                                _selectedCategory = 'hostels';
                              });
                            }),
                            _buildChoiceChip('Apartment / Per Room', prov.selectedRentStructure == 'per_room', () {
                              setModalState(() {
                                prov.setFilter(rentStructure: 'per_room');
                                _selectedCategory = 'apartments';
                              });
                            }),
                            _buildChoiceChip('Entire Property / House', prov.selectedRentStructure == 'entire_property', () {
                              setModalState(() {
                                prov.setFilter(rentStructure: 'entire_property');
                                _selectedCategory = 'houses';
                              });
                            }),
                          ],
                        ),
                        const SizedBox(height: 24),
                        Text('City / Region', style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 14)),
                        const SizedBox(height: 10),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: [
                            _buildChoiceChip('All Locations', prov.selectedCity == null, () {
                              setModalState(() => prov.setFilter(city: null));
                            }),
                            ...prov.cities.map((city) {
                              return _buildChoiceChip(city, prov.selectedCity == city, () {
                                setModalState(() => prov.setFilter(city: prov.selectedCity == city ? null : city));
                              });
                            }),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: () => Navigator.pop(context),
                    child: const Text('Show Results', style: TextStyle(fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildChoiceChip(String label, bool isSelected, VoidCallback onSelected) {
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (_) => onSelected(),
      backgroundColor: Colors.white,
      selectedColor: AppTheme.primary.withValues(alpha: 0.12),
      side: BorderSide(color: isSelected ? AppTheme.primary : AppTheme.borderLight),
      labelStyle: TextStyle(
        color: isSelected ? AppTheme.primary : AppTheme.textMainLight,
        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
        fontSize: 13,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<PropertyProvider>(context);
    final auth = Provider.of<AuthProvider>(context);
    final isGuest = !auth.isAuthenticated || auth.currentUser == null;

    return Scaffold(
      backgroundColor: Colors.white,
      drawer: const AppDrawer(),
      appBar: AppBar(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'SmartNest',
              style: GoogleFonts.outfit(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: AppTheme.primary,
                letterSpacing: -0.3,
              ),
            ),
            Text(
              'Browse & Book Accommodations',
              style: GoogleFonts.inter(fontSize: 11, color: AppTheme.textMutedLight),
            ),
          ],
        ),
        actions: [
          if (isGuest)
            Padding(
              padding: const EdgeInsets.only(right: 12),
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primary.withValues(alpha: 0.08),
                  foregroundColor: AppTheme.primary,
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  minimumSize: Size.zero,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(20),
                    side: BorderSide(color: AppTheme.primary.withValues(alpha: 0.2)),
                  ),
                ),
                icon: const Icon(Icons.login_rounded, size: 16),
                label: const Text('Sign In', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                onPressed: () => Navigator.pushNamed(context, '/login'),
              ),
            )
          else ...[
            IconButton(
              icon: const Icon(Icons.notifications_none_outlined, size: 22),
              onPressed: () => Navigator.pushNamed(context, '/notifications'),
            ),
            Padding(
              padding: const EdgeInsets.only(right: 12),
              child: GestureDetector(
                onTap: () => Navigator.pushNamed(context, '/profile'),
                child: CircleAvatar(
                  radius: 16,
                  backgroundColor: AppTheme.primary.withValues(alpha: 0.12),
                  child: Text(
                    auth.currentUser!.name.isNotEmpty ? auth.currentUser!.name[0].toUpperCase() : 'U',
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppTheme.primary),
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
      body: RefreshIndicator(
        color: AppTheme.primary,
        onRefresh: () => prov.fetchProperties(refresh: true),
        child: Column(
          children: [
            // Modern Search Bar Container
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
              child: Row(
                children: [
                  Expanded(
                    child: Container(
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: AppTheme.borderLight),
                      ),
                      child: TextField(
                        controller: _searchController,
                        onSubmitted: (val) => prov.setSearchQuery(val.trim()),
                        style: GoogleFonts.inter(fontSize: 14),
                        decoration: InputDecoration(
                          hintText: 'Search apartments, hostels, cities...',
                          hintStyle: GoogleFonts.inter(fontSize: 13, color: AppTheme.textMutedLight),
                          prefixIcon: const Icon(Icons.search_rounded, size: 20, color: AppTheme.textMutedLight),
                          suffixIcon: _searchController.text.isNotEmpty
                              ? IconButton(
                                  icon: const Icon(Icons.close_rounded, size: 18),
                                  onPressed: () {
                                    _searchController.clear();
                                    prov.setSearchQuery('');
                                    setState(() {});
                                  },
                                )
                              : null,
                          border: InputBorder.none,
                          enabledBorder: InputBorder.none,
                          focusedBorder: InputBorder.none,
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  InkWell(
                    onTap: _openFilterModal,
                    borderRadius: BorderRadius.circular(16),
                    child: Container(
                      padding: const EdgeInsets.all(13),
                      decoration: BoxDecoration(
                        color: AppTheme.primary,
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: [
                          BoxShadow(
                            color: AppTheme.primary.withValues(alpha: 0.25),
                            blurRadius: 8,
                            offset: const Offset(0, 3),
                          ),
                        ],
                      ),
                      child: const Icon(Icons.tune_rounded, color: Colors.white, size: 20),
                    ),
                  ),
                ],
              ),
            ),

            // Horizontal Categories Filter Carousel
            SizedBox(
              height: 46,
              child: ListView(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 16),
                children: [
                  _buildCategoryPill('All Homes', 'all', null),
                  const SizedBox(width: 8),
                  _buildCategoryPill('Apartments', 'apartments', 'per_room'),
                  const SizedBox(width: 8),
                  _buildCategoryPill('Hostels / Beds', 'hostels', 'per_bed'),
                  const SizedBox(width: 8),
                  _buildCategoryPill('Entire Houses', 'houses', 'entire_property'),
                ],
              ),
            ),
            const SizedBox(height: 6),

            // Listings Content
            Expanded(
              child: prov.isLoading
                  ? const Center(
                      child: CircularProgressIndicator(color: AppTheme.primary),
                    )
                  : prov.properties.isEmpty
                      ? EmptyState(
                          icon: Icons.home_work_outlined,
                          title: 'No Properties Found',
                          message: 'No accommodations matched your filter or search query.',
                          actionText: 'Clear Search & Filters',
                          onAction: () {
                            _searchController.clear();
                            prov.resetFilters();
                            setState(() => _selectedCategory = 'all');
                          },
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
                          itemCount: prov.properties.length,
                          itemBuilder: (context, index) {
                            final item = prov.properties[index];
                            return _buildModernPropertyCard(context, item);
                          },
                        ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCategoryPill(String title, String categoryKey, String? rentStructure) {
    final isSelected = _selectedCategory == categoryKey;
    return Center(
      child: InkWell(
        onTap: () => _onCategorySelected(categoryKey, rentStructure),
        borderRadius: BorderRadius.circular(24),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          decoration: BoxDecoration(
            color: isSelected ? AppTheme.primary : const Color(0xFFF8FAFC),
            borderRadius: BorderRadius.circular(24),
            border: Border.all(
              color: isSelected ? AppTheme.primary : AppTheme.borderLight,
              width: 1,
            ),
          ),
          child: Text(
            title,
            style: GoogleFonts.inter(
              fontSize: 12.5,
              fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
              color: isSelected ? Colors.white : AppTheme.textMainLight,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildModernPropertyCard(BuildContext context, PropertyModel item) {
    return Container(
      margin: const EdgeInsets.only(bottom: 20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: AppTheme.borderLight, width: 1),
        boxShadow: const [
          BoxShadow(
            color: Color(0x0A000000),
            blurRadius: 18,
            offset: Offset(0, 6),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () {
          Navigator.pushNamed(
            context,
            '/property/detail',
            arguments: item.id,
          );
        },
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Top Image with Overlays
            Stack(
              children: [
                SizedBox(
                  height: 195,
                  width: double.infinity,
                  child: item.primaryImageUrl != null && item.primaryImageUrl!.isNotEmpty
                      ? Image.network(
                          item.primaryImageUrl!,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => _buildPlaceholderImage(),
                        )
                      : _buildPlaceholderImage(),
                ),

                // Top Left Badges: Rent structure
                Positioned(
                  top: 14,
                  left: 14,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.95),
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: const [
                        BoxShadow(
                          color: Color(0x15000000),
                          blurRadius: 6,
                          offset: Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Text(
                      _formatRentStructure(item.rentStructure),
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.primary,
                      ),
                    ),
                  ),
                ),

                // Top Right Vacancy Pill
                if (item.availableBeds > 0 || item.availableRooms > 0)
                  Positioned(
                    top: 14,
                    right: 14,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                      decoration: BoxDecoration(
                        color: AppTheme.textMainLight.withValues(alpha: 0.88),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.check_circle_rounded, color: AppTheme.success, size: 12),
                          const SizedBox(width: 4),
                          Text(
                            item.isPerBed
                                ? '${item.availableBeds} beds available'
                                : '${item.availableRooms} rooms available',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 10.5,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
              ],
            ),

            // Card Bottom Details
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Title
                  Text(
                    item.propertyName,
                    style: GoogleFonts.outfit(
                      fontSize: 17,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.textMainLight,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 5),

                  // Location
                  Row(
                    children: [
                      const Icon(Icons.location_on_rounded, size: 14, color: AppTheme.primary),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          item.address,
                          style: GoogleFonts.inter(
                            fontSize: 12.5,
                            color: AppTheme.textMutedLight,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  const Divider(color: AppTheme.borderLight, height: 1),
                  const SizedBox(height: 12),

                  // Price & Action Button
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'TZS ${_formatNumber(item.price)}',
                            style: GoogleFonts.outfit(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.primary,
                            ),
                          ),
                          Text(
                            'per ${item.billingPeriod}',
                            style: GoogleFonts.inter(
                              fontSize: 11,
                              color: AppTheme.textMutedLight,
                            ),
                          ),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                        decoration: BoxDecoration(
                          color: AppTheme.primary,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          children: [
                            Text(
                              'View & Book',
                              style: GoogleFonts.inter(
                                fontSize: 12.5,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                              ),
                            ),
                            const SizedBox(width: 4),
                            const Icon(Icons.arrow_forward_rounded, size: 14, color: Colors.white),
                          ],
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPlaceholderImage() {
    return Container(
      color: const Color(0xFFF1F5F9),
      child: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.apartment_rounded, size: 48, color: AppTheme.primary.withValues(alpha: 0.35)),
            const SizedBox(height: 6),
            Text(
              'SmartNest Accommodation',
              style: GoogleFonts.inter(fontSize: 11, color: AppTheme.textMutedLight),
            ),
          ],
        ),
      ),
    );
  }

  String _formatRentStructure(String rentStructure) {
    switch (rentStructure) {
      case 'per_bed':
        return 'Hostel Bed';
      case 'per_room':
        return 'Private Room';
      case 'entire_property':
        return 'Entire House';
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
