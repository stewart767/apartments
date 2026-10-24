import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../providers/property_provider.dart';
import '../../widgets/app_button.dart';
import '../../widgets/price_card.dart';
import '../../widgets/status_badge.dart';

class PropertyDetailScreen extends StatefulWidget {
  final int propertyId;

  const PropertyDetailScreen({super.key, required this.propertyId});

  @override
  State<PropertyDetailScreen> createState() => _PropertyDetailScreenState();
}

class _PropertyDetailScreenState extends State<PropertyDetailScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<PropertyProvider>(context, listen: false).fetchPropertyDetail(widget.propertyId);
    });
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<PropertyProvider>(context);
    final prop = prov.selectedProperty;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    if (prov.isLoading || prop == null) {
      return Scaffold(
        appBar: AppBar(),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    return Scaffold(
      body: CustomScrollView(
        slivers: [
          // Header App Bar with Image
          SliverAppBar(
            expandedHeight: 250,
            pinned: true,
            flexibleSpace: FlexibleSpaceBar(
              background: prop.images.isNotEmpty
                  ? PageView.builder(
                      itemCount: prop.images.length,
                      itemBuilder: (context, idx) {
                        return Image.network(
                          prop.images[idx],
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => const Icon(Icons.apartment, size: 80),
                        );
                      },
                    )
                  : Container(
                      color: isDark ? AppTheme.cardDark : Colors.grey[300],
                      child: const Icon(Icons.apartment, size: 80, color: Colors.grey),
                    ),
            ),
          ),

          // Content
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      StatusBadge(status: prop.rentStructure),
                      PriceCard(price: prop.price, billingPeriod: prop.billingPeriod, fontSize: 20),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text(
                    prop.propertyName,
                    style: GoogleFonts.outfit(fontSize: 22, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      const Icon(Icons.location_on, color: AppTheme.primary, size: 16),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          prop.address,
                          style: GoogleFonts.inter(
                            fontSize: 13,
                            color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const Divider(height: 32),

                  // Description
                  Text('About Property', style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  Text(
                    prop.description ?? 'No description provided for this listing.',
                    style: GoogleFonts.inter(
                      fontSize: 14,
                      height: 1.5,
                      color: isDark ? AppTheme.textMutedDark : AppTheme.textMutedLight,
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Amenities
                  if (prop.amenities.isNotEmpty) ...[
                    Text('Amenities & Features', style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 10),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: prop.amenities.map((am) {
                        return Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: isDark ? AppTheme.cardDark : Colors.white,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: isDark ? AppTheme.borderDark : AppTheme.borderLight),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.check_circle_outline, size: 14, color: AppTheme.primary),
                              const SizedBox(width: 6),
                              Text(am.name, style: GoogleFonts.inter(fontSize: 12, fontWeight: FontWeight.w500)),
                            ],
                          ),
                        );
                      }).toList(),
                    ),
                    const SizedBox(height: 24),
                  ],

                  // Room & Bed Layout
                  if (prop.rooms.isNotEmpty || prop.buildings.isNotEmpty) ...[
                    Text('Units & Room Availability', style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 10),
                    ...prop.rooms.map((room) {
                      return Card(
                        margin: const EdgeInsets.only(bottom: 8),
                        child: ListTile(
                          title: Text('Room ${room.roomNumber}', style: GoogleFonts.inter(fontWeight: FontWeight.w600)),
                          subtitle: Text('Capacity: ${room.capacity} | Price: TZS ${room.price}'),
                          trailing: StatusBadge(status: room.status),
                        ),
                      );
                    }),
                    const SizedBox(height: 32),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: isDark ? AppTheme.cardDark : Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 10,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: SafeArea(
          child: AppButton(
            text: 'Apply & Reserve Unit',
            icon: Icons.assignment_turned_in,
            onPressed: () {
              Navigator.pushNamed(
                context,
                '/booking/form',
                arguments: prop,
              );
            },
          ),
        ),
      ),
    );
  }
}
