import '../core/api_client.dart';
import '../models/property_model.dart';

class PropertyService {
  final ApiClient _api = ApiClient.instance;

  Future<List<PropertyModel>> getProperties({
    String? search,
    String? city,
    String? rentStructure,
    double? minPrice,
    double? maxPrice,
    int? institutionId,
    List<int>? amenityIds,
    int page = 1,
  }) async {
    final query = <String, dynamic>{
      'page': page,
    };
    if (search != null && search.isNotEmpty) query['search'] = search;
    if (city != null && city.isNotEmpty) query['city'] = city;
    if (rentStructure != null && rentStructure.isNotEmpty) query['rent_structure'] = rentStructure;
    if (minPrice != null) query['min_price'] = minPrice;
    if (maxPrice != null) query['max_price'] = maxPrice;
    if (institutionId != null) query['institution_id'] = institutionId;
    if (amenityIds != null && amenityIds.isNotEmpty) {
      query['amenities'] = amenityIds.join(',');
    }

    final response = await _api.get('/properties', queryParameters: query);
    if (response is List) {
      return response.map((json) => PropertyModel.fromJson(json as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<PropertyModel> getPropertyDetail(int propertyId) async {
    final response = await _api.get('/properties/$propertyId');
    return PropertyModel.fromJson(response as Map<String, dynamic>);
  }

  Future<List<AmenityModel>> getAmenities() async {
    final response = await _api.get('/amenities');
    if (response is List) {
      return response.map((json) => AmenityModel.fromJson(json as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<List<Map<String, dynamic>>> getInstitutions({String? city}) async {
    final query = city != null ? {'city': city} : null;
    final response = await _api.get('/institutions', queryParameters: query);
    if (response is List) {
      return List<Map<String, dynamic>>.from(response);
    }
    return [];
  }

  Future<List<String>> getCities() async {
    final response = await _api.get('/locations/cities');
    if (response is List) {
      return List<String>.from(response);
    }
    return [];
  }
}
