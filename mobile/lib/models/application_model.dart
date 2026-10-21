class ApplicationModel {
  final int id;
  final String applicationNumber;
  final int tenantId;
  final Map<String, dynamic>? tenant;
  final int propertyId;
  final Map<String, dynamic>? property;
  final int? roomId;
  final Map<String, dynamic>? room;
  final int? bedId;
  final Map<String, dynamic>? bed;
  final String? startDate;
  final String? endDate;
  final double price;
  final String billingPeriod;
  final String status; // 'pending', 'approved', 'rejected', 'cancelled'
  final String? ownerNotes;
  final String? tenantNotes;
  final String? createdAt;

  ApplicationModel({
    required this.id,
    required this.applicationNumber,
    required this.tenantId,
    this.tenant,
    required this.propertyId,
    this.property,
    this.roomId,
    this.room,
    this.bedId,
    this.bed,
    this.startDate,
    this.endDate,
    required this.price,
    required this.billingPeriod,
    required this.status,
    this.ownerNotes,
    this.tenantNotes,
    this.createdAt,
  });

  bool get isPending => status == 'pending';
  bool get isApproved => status == 'approved';
  bool get isRejected => status == 'rejected';

  factory ApplicationModel.fromJson(Map<String, dynamic> json) {
    return ApplicationModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      applicationNumber: json['application_number'] ?? '',
      tenantId: json['tenant_id'] is int ? json['tenant_id'] : int.tryParse(json['tenant_id'].toString()) ?? 0,
      tenant: json['tenant'] is Map<String, dynamic> ? json['tenant'] : null,
      propertyId: json['property_id'] is int ? json['property_id'] : int.tryParse(json['property_id'].toString()) ?? 0,
      property: json['property'] is Map<String, dynamic> ? json['property'] : null,
      roomId: json['room_id'] != null ? int.tryParse(json['room_id'].toString()) : null,
      room: json['room'] is Map<String, dynamic> ? json['room'] : null,
      bedId: json['bed_id'] != null ? int.tryParse(json['bed_id'].toString()) : null,
      bed: json['bed'] is Map<String, dynamic> ? json['bed'] : null,
      startDate: json['start_date'],
      endDate: json['end_date'],
      price: double.tryParse(json['price']?.toString() ?? '0') ?? 0.0,
      billingPeriod: json['billing_period'] ?? 'monthly',
      status: json['status'] ?? 'pending',
      ownerNotes: json['owner_notes'],
      tenantNotes: json['tenant_notes'],
      createdAt: json['created_at'],
    );
  }
}
