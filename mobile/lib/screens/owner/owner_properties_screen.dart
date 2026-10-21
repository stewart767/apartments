import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../models/property_model.dart';
import '../../providers/owner_provider.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/price_card.dart';
import '../../widgets/status_badge.dart';

class OwnerPropertiesScreen extends StatefulWidget {
  const OwnerPropertiesScreen({super.key});

  @override
  State<OwnerPropertiesScreen> createState() => _OwnerPropertiesScreenState();
}

class _OwnerPropertiesScreenState extends State<OwnerPropertiesScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<OwnerProvider>(context, listen: false).fetchProperties();
    });
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<OwnerProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('My Properties'),
      ),
      body: RefreshIndicator(
        onRefresh: () => prov.fetchProperties(),
        child: prov.isLoading
            ? const Center(child: CircularProgressIndicator())
            : prov.properties.isEmpty
                ? EmptyState(
                    icon: Icons.apartment_outlined,
                    title: 'No Properties Registered',
                    message: 'Add your hostels, apartments, or rental houses to begin managing tenancies and receiving bookings.',
                    actionText: 'Add New Property',
                    onAction: () => Navigator.pushNamed(context, '/owner/properties/create'),
                  )
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: prov.properties.length,
                    itemBuilder: (context, index) {
                      final prop = prov.properties[index];
                      return Card(
                        margin: const EdgeInsets.only(bottom: 16),
                        clipBehavior: Clip.antiAlias,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            if (prop.primaryImageUrl != null)
                              Image.network(
                                prop.primaryImageUrl!,
                                height: 140,
                                width: double.infinity,
                                fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) => const SizedBox(height: 8),
                              ),
                            Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        prop.propertyName,
                                        style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold),
                                      ),
                                      StatusBadge(status: prop.rentStructure),
                                    ],
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    prop.address,
                                    style: GoogleFonts.inter(
                                      fontSize: 12,
                                      color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight,
                                    ),
                                  ),
                                  const SizedBox(height: 12),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        'Rooms: ${prop.totalRooms} | Beds: ${prop.totalBeds}',
                                        style: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.w600),
                                      ),
                                      PriceCard(price: prop.price, billingPeriod: prop.billingPeriod, fontSize: 14),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => Navigator.pushNamed(context, '/owner/properties/create'),
        backgroundColor: AppTheme.primary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Add Property', style: TextStyle(color: Colors.white)),
      ),
    );
  }
}
