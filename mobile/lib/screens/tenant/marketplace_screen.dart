import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../models/property_model.dart';
import '../../providers/property_provider.dart';
import '../../widgets/app_drawer.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/price_card.dart';
import '../../widgets/status_badge.dart';

class MarketplaceScreen extends StatefulWidget {
  const MarketplaceScreen({super.key});

  @override
  State<MarketplaceScreen> createState() => _MarketplaceScreenState();
}

class _MarketplaceScreenState extends State<MarketplaceScreen> {
  final _searchController = TextEditingController();

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

  void _openFilterModal() {
    final prov = Provider.of<PropertyProvider>(context, listen: false);
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Container(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
              height: MediaQuery.of(context).size.height * 0.7,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Filter Properties', style: GoogleFonts.outfit(fontSize: 18, fontWeight: FontWeight.bold)),
                      TextButton(
                        onPressed: () {
                          prov.resetFilters();
                          Navigator.pop(context);
                        },
                        child: const Text('Reset'),
                      ),
                    ],
                  ),
                  const Divider(),
                  Expanded(
                    child: ListView(
                      children: [
                        Text('Rental Type', style: GoogleFonts.inter(fontWeight: FontWeight.w600)),
                        const SizedBox(height: 8),
                        Wrap(
                          spacing: 8,
                          children: [
                            ChoiceChip(
                              label: const Text('All'),
                              selected: prov.selectedRentStructure == null,
                              onSelected: (_) => setModalState(() => prov.setFilter(rentStructure: null)),
                            ),
                            ChoiceChip(
                              label: const Text('Hostel / Per Bed'),
                              selected: prov.selectedRentStructure == 'per_bed',
                              onSelected: (_) => setModalState(() => prov.setFilter(rentStructure: 'per_bed')),
                            ),
                            ChoiceChip(
                              label: const Text('Apartment / Per Room'),
                              selected: prov.selectedRentStructure == 'per_room',
                              onSelected: (_) => setModalState(() => prov.setFilter(rentStructure: 'per_room')),
                            ),
                            ChoiceChip(
                              label: const Text('Entire Property'),
                              selected: prov.selectedRentStructure == 'entire_property',
                              onSelected: (_) => setModalState(() => prov.setFilter(rentStructure: 'entire_property')),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        Text('City / Location', style: GoogleFonts.inter(fontWeight: FontWeight.w600)),
                        const SizedBox(height: 8),
                        Wrap(
                          spacing: 8,
                          children: [
                            ChoiceChip(
                              label: const Text('All Cities'),
                              selected: prov.selectedCity == null,
                              onSelected: (_) => setModalState(() => prov.setFilter(city: null)),
                            ),
                            ...prov.cities.map((city) {
                              return ChoiceChip(
                                label: Text(city),
                                selected: prov.selectedCity == city,
                                onSelected: (sel) => setModalState(() => prov.setFilter(city: sel ? city : null)),
                              );
                            }),
                          ],
                        ),
                      ],
                    ),
                  ),
                  ElevatedButton(
                    onPressed: () => Navigator.pop(context),
                    child: const Text('Apply Filters'),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<PropertyProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      drawer: const AppDrawer(),
      appBar: AppBar(
        title: const Text('SmartNest Rentals'),
        actions: [
          IconButton(
            icon: const Icon(Icons.tune),
            onPressed: _openFilterModal,
          ),
          IconButton(
            icon: const Icon(Icons.notifications_none),
            onPressed: () => Navigator.pushNamed(context, '/notifications'),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => prov.fetchProperties(refresh: true),
        child: Column(
          children: [
            // Search Input
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: TextField(
                controller: _searchController,
                onSubmitted: (val) => prov.setSearchQuery(val.trim()),
                decoration: InputDecoration(
                  hintText: 'Search properties, hostels, colleges...',
                  prefixIcon: const Icon(Icons.search, size: 20),
                  suffixIcon: _searchController.text.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear, size: 18),
                          onPressed: () {
                            _searchController.clear();
                            prov.setSearchQuery('');
                          },
                        )
                      : null,
                ),
              ),
            ),

            // Content
            Expanded(
              child: prov.isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : prov.properties.isEmpty
                      ? EmptyState(
                          icon: Icons.search_off,
                          title: 'No Properties Found',
                          message: 'Try adjusting your search criteria or filters.',
                          actionText: 'Reset Filters',
                          onAction: () {
                            _searchController.clear();
                            prov.resetFilters();
                          },
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                          itemCount: prov.properties.length,
                          itemBuilder: (context, index) {
                            final item = prov.properties[index];
                            return _buildPropertyCard(context, item, isDark);
                          },
                        ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPropertyCard(BuildContext context, PropertyModel item, bool isDark) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
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
            // Image & Badges Stack
            Stack(
              children: [
                Container(
                  height: 180,
                  width: double.infinity,
                  color: isDark ? AppTheme.cardDark : Colors.grey[200],
                  child: item.primaryImageUrl != null && item.primaryImageUrl!.isNotEmpty
                      ? Image.network(
                          item.primaryImageUrl!,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => const Icon(Icons.apartment, size: 64, color: Colors.grey),
                        )
                      : const Icon(Icons.apartment, size: 64, color: Colors.grey),
                ),
                Positioned(
                  top: 12,
                  left: 12,
                  child: StatusBadge(status: item.rentStructure),
                ),
                if (item.availableBeds > 0 || item.availableRooms > 0)
                  Positioned(
                    top: 12,
                    right: 12,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.black.withOpacity(0.7),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        item.isPerBed
                            ? '${item.availableBeds} beds left'
                            : '${item.availableRooms} rooms left',
                        style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ),
              ],
            ),

            // Content Info
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          item.propertyName,
                          style: GoogleFonts.outfit(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      PriceCard(price: item.price, billingPeriod: item.billingPeriod, fontSize: 16),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      const Icon(Icons.location_on_outlined, size: 15, color: AppTheme.primary),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          item.address,
                          style: GoogleFonts.inter(
                            fontSize: 12,
                            color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
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
}
