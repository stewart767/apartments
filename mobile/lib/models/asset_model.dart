class AssetModel {
  final int id;
  final String assetName;
  final String? assetCode;
  final String? category;
  final int propertyId;
  final String? propertyName;
  final Map<String, dynamic>? room;
  final Map<String, dynamic>? bed;
  final String condition; // 'new', 'good', 'fair', 'damaged', 'lost'
  final double? estimatedValue;
  final bool isHandedOver;
  final String? handoverDate;
  final String? handoverNotes;
  final bool tenantConfirmed;
  final String? tenantConfirmedAt;
  final String? checkoutCondition;
  final double? damageDeduction;

  AssetModel({
    required this.id,
    required this.assetName,
    this.assetCode,
    this.category,
    required this.propertyId,
    this.propertyName,
    this.room,
    this.bed,
    required this.condition,
    this.estimatedValue,
    this.isHandedOver = false,
    this.handoverDate,
    this.handoverNotes,
    this.tenantConfirmed = false,
    this.tenantConfirmedAt,
    this.checkoutCondition,
    this.damageDeduction,
  });

  factory AssetModel.fromJson(Map<String, dynamic> json) {
    return AssetModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      assetName: json['asset_name'] ?? json['name'] ?? '',
      assetCode: json['asset_code'] ?? json['code'],
      category: json['category'],
      propertyId: json['property_id'] is int ? json['property_id'] : int.tryParse(json['property_id'].toString()) ?? 0,
      propertyName: json['property_name'],
      room: json['room'] is Map<String, dynamic> ? json['room'] : null,
      bed: json['bed'] is Map<String, dynamic> ? json['bed'] : null,
      condition: json['condition'] ?? 'good',
      estimatedValue: json['estimated_value'] != null ? double.tryParse(json['estimated_value'].toString()) : null,
      isHandedOver: json['is_handed_over'] == true || json['is_handed_over'] == 1,
      handoverDate: json['handover_date'],
      handoverNotes: json['handover_notes'],
      tenantConfirmed: json['tenant_confirmed'] == true || json['tenant_confirmed'] == 1,
      tenantConfirmedAt: json['tenant_confirmed_at'],
      checkoutCondition: json['checkout_condition'],
      damageDeduction: json['damage_deduction'] != null ? double.tryParse(json['damage_deduction'].toString()) : null,
    );
  }
}
