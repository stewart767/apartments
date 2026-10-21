class PropertyModel {
  final int id;
  final String propertyName;
  final String slug;
  final String? description;
  final String? propertyType;
  final String rentStructure;
  final String address;
  final String? city;
  final String? stateProvince;
  final String? postalCode;
  final String? country;
  final double? latitude;
  final double? longitude;
  final double price;
  final double? depositAmount;
  final String billingPeriod;
  final int totalRooms;
  final int availableRooms;
  final int totalBeds;
  final int availableBeds;
  final bool isFeatured;
  final String status;
  final String? primaryImageUrl;
  final List<String> images;
  final List<AmenityModel> amenities;
  final List<BuildingModel> buildings;
  final List<RoomModel> rooms;
  final double averageRating;
  final int reviewsCount;
  final Map<String, dynamic>? owner;

  PropertyModel({
    required this.id,
    required this.propertyName,
    required this.slug,
    this.description,
    this.propertyType,
    required this.rentStructure,
    required this.address,
    this.city,
    this.stateProvince,
    this.postalCode,
    this.country,
    this.latitude,
    this.longitude,
    required this.price,
    this.depositAmount,
    required this.billingPeriod,
    this.totalRooms = 0,
    this.availableRooms = 0,
    this.totalBeds = 0,
    this.availableBeds = 0,
    this.isFeatured = false,
    required this.status,
    this.primaryImageUrl,
    this.images = const [],
    this.amenities = const [],
    this.buildings = const [],
    this.rooms = const [],
    this.averageRating = 0.0,
    this.reviewsCount = 0,
    this.owner,
  });

  bool get isPerBed => rentStructure == 'per_bed';
  bool get isPerRoom => rentStructure == 'per_room';
  bool get isEntireProperty => rentStructure == 'entire_property';

  factory PropertyModel.fromJson(Map<String, dynamic> json) {
    var imagesList = <String>[];
    if (json['images'] is List) {
      imagesList = (json['images'] as List).map((img) {
        if (img is Map && img.containsKey('url')) return img['url'].toString();
        if (img is String) return img;
        return '';
      }).where((s) => s.isNotEmpty).toList();
    }

    var amenitiesList = <AmenityModel>[];
    if (json['amenities'] is List) {
      amenitiesList = (json['amenities'] as List)
          .map((a) => AmenityModel.fromJson(a is Map<String, dynamic> ? a : {}))
          .toList();
    }

    var buildingsList = <BuildingModel>[];
    if (json['buildings'] is List) {
      buildingsList = (json['buildings'] as List)
          .map((b) => BuildingModel.fromJson(b is Map<String, dynamic> ? b : {}))
          .toList();
    }

    var roomsList = <RoomModel>[];
    if (json['rooms'] is List) {
      roomsList = (json['rooms'] as List)
          .map((r) => RoomModel.fromJson(r is Map<String, dynamic> ? r : {}))
          .toList();
    }

    return PropertyModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      propertyName: json['property_name'] ?? json['title'] ?? '',
      slug: json['slug'] ?? '',
      description: json['description'],
      propertyType: json['property_type'],
      rentStructure: json['rent_structure'] ?? 'entire_property',
      address: json['address'] ?? '',
      city: json['city'],
      stateProvince: json['state_province'],
      postalCode: json['postal_code'],
      country: json['country'],
      latitude: json['latitude'] != null ? double.tryParse(json['latitude'].toString()) : null,
      longitude: json['longitude'] != null ? double.tryParse(json['longitude'].toString()) : null,
      price: double.tryParse(json['price']?.toString() ?? '0') ?? 0.0,
      depositAmount: json['deposit_amount'] != null ? double.tryParse(json['deposit_amount'].toString()) : null,
      billingPeriod: json['billing_period'] ?? 'monthly',
      totalRooms: int.tryParse(json['total_rooms']?.toString() ?? '0') ?? 0,
      availableRooms: int.tryParse(json['available_rooms']?.toString() ?? '0') ?? 0,
      totalBeds: int.tryParse(json['total_beds']?.toString() ?? '0') ?? 0,
      availableBeds: int.tryParse(json['available_beds']?.toString() ?? '0') ?? 0,
      isFeatured: json['is_featured'] == true || json['is_featured'] == 1,
      status: json['status'] ?? 'published',
      primaryImageUrl: json['primary_image'] ?? json['primary_image_url'],
      images: imagesList,
      amenities: amenitiesList,
      buildings: buildingsList,
      rooms: roomsList,
      averageRating: double.tryParse(json['average_rating']?.toString() ?? '0') ?? 0.0,
      reviewsCount: int.tryParse(json['reviews_count']?.toString() ?? '0') ?? 0,
      owner: json['owner'] is Map<String, dynamic> ? json['owner'] : null,
    );
  }
}

class AmenityModel {
  final int id;
  final String name;
  final String? icon;

  AmenityModel({required this.id, required this.name, this.icon});

  factory AmenityModel.fromJson(Map<String, dynamic> json) {
    return AmenityModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      name: json['name'] ?? '',
      icon: json['icon'],
    );
  }
}

class BuildingModel {
  final int id;
  final String buildingName;
  final List<FloorModel> floors;

  BuildingModel({required this.id, required this.buildingName, this.floors = const []});

  factory BuildingModel.fromJson(Map<String, dynamic> json) {
    var floorsList = <FloorModel>[];
    if (json['floors'] is List) {
      floorsList = (json['floors'] as List)
          .map((f) => FloorModel.fromJson(f is Map<String, dynamic> ? f : {}))
          .toList();
    }
    return BuildingModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      buildingName: json['building_name'] ?? '',
      floors: floorsList,
    );
  }
}

class FloorModel {
  final int id;
  final String floorName;
  final int floorNumber;
  final List<RoomModel> rooms;

  FloorModel({
    required this.id,
    required this.floorName,
    required this.floorNumber,
    this.rooms = const [],
  });

  factory FloorModel.fromJson(Map<String, dynamic> json) {
    var roomsList = <RoomModel>[];
    if (json['rooms'] is List) {
      roomsList = (json['rooms'] as List)
          .map((r) => RoomModel.fromJson(r is Map<String, dynamic> ? r : {}))
          .toList();
    }
    return FloorModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      floorName: json['floor_name'] ?? '',
      floorNumber: int.tryParse(json['floor_number']?.toString() ?? '0') ?? 0,
      rooms: roomsList,
    );
  }
}

class RoomModel {
  final int id;
  final String roomNumber;
  final String? roomType;
  final int capacity;
  final double price;
  final String status;
  final List<BedModel> beds;

  RoomModel({
    required this.id,
    required this.roomNumber,
    this.roomType,
    required this.capacity,
    required this.price,
    required this.status,
    this.beds = const [],
  });

  factory RoomModel.fromJson(Map<String, dynamic> json) {
    var bedsList = <BedModel>[];
    if (json['beds'] is List) {
      bedsList = (json['beds'] as List)
          .map((b) => BedModel.fromJson(b is Map<String, dynamic> ? b : {}))
          .toList();
    }
    return RoomModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      roomNumber: json['room_number'] ?? '',
      roomType: json['room_type'],
      capacity: int.tryParse(json['capacity']?.toString() ?? '1') ?? 1,
      price: double.tryParse(json['price']?.toString() ?? '0') ?? 0.0,
      status: json['status'] ?? 'available',
      beds: bedsList,
    );
  }
}

class BedModel {
  final int id;
  final String bedNumber;
  final double price;
  final String status; // 'available', 'reserved', 'occupied', 'maintenance'

  BedModel({
    required this.id,
    required this.bedNumber,
    required this.price,
    required this.status,
  });

  bool get isAvailable => status == 'available';

  factory BedModel.fromJson(Map<String, dynamic> json) {
    return BedModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      bedNumber: json['bed_number'] ?? '',
      price: double.tryParse(json['price']?.toString() ?? '0') ?? 0.0,
      status: json['status'] ?? 'available',
    );
  }
}
