class MaintenanceModel {
  final int id;
  final String ticketNumber;
  final int tenantId;
  final Map<String, dynamic>? tenant;
  final int propertyId;
  final String? propertyName;
  final Map<String, dynamic>? room;
  final Map<String, dynamic>? bed;
  final String title;
  final String description;
  final String priority; // 'low', 'medium', 'high', 'emergency'
  final String status; // 'pending', 'assigned', 'in_progress', 'completed', 'cancelled'
  final Map<String, dynamic>? assignedTo;
  final String? resolutionNotes;
  final String? resolvedAt;
  final List<MaintenanceImageModel> images;
  final String? createdAt;

  MaintenanceModel({
    required this.id,
    required this.ticketNumber,
    required this.tenantId,
    this.tenant,
    required this.propertyId,
    this.propertyName,
    this.room,
    this.bed,
    required this.title,
    required this.description,
    required this.priority,
    required this.status,
    this.assignedTo,
    this.resolutionNotes,
    this.resolvedAt,
    this.images = const [],
    this.createdAt,
  });

  bool get isCompleted => status == 'completed';
  bool get isPending => status == 'pending';
  bool get isInProgress => status == 'in_progress';
  bool get isAssigned => status == 'assigned';

  factory MaintenanceModel.fromJson(Map<String, dynamic> json) {
    var imagesList = <MaintenanceImageModel>[];
    if (json['images'] is List) {
      imagesList = (json['images'] as List)
          .map((img) => MaintenanceImageModel.fromJson(img is Map<String, dynamic> ? img : {}))
          .toList();
    }
    return MaintenanceModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      ticketNumber: json['ticket_number'] ?? '',
      tenantId: json['tenant_id'] is int ? json['tenant_id'] : int.tryParse(json['tenant_id'].toString()) ?? 0,
      tenant: json['tenant'] is Map<String, dynamic> ? json['tenant'] : null,
      propertyId: json['property_id'] is int ? json['property_id'] : int.tryParse(json['property_id'].toString()) ?? 0,
      propertyName: json['property_name'],
      room: json['room'] is Map<String, dynamic> ? json['room'] : null,
      bed: json['bed'] is Map<String, dynamic> ? json['bed'] : null,
      title: json['title'] ?? '',
      description: json['description'] ?? '',
      priority: json['priority'] ?? 'medium',
      status: json['status'] ?? 'pending',
      assignedTo: json['assigned_to'] is Map<String, dynamic> ? json['assigned_to'] : null,
      resolutionNotes: json['resolution_notes'],
      resolvedAt: json['resolved_at'],
      images: imagesList,
      createdAt: json['created_at'],
    );
  }
}

class MaintenanceImageModel {
  final int id;
  final String url;

  MaintenanceImageModel({required this.id, required this.url});

  factory MaintenanceImageModel.fromJson(Map<String, dynamic> json) {
    return MaintenanceImageModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      url: json['url'] ?? '',
    );
  }
}
