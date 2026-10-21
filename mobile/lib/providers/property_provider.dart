import 'package:flutter/material.dart';
import '../models/property_model.dart';
import '../services/property_service.dart';

class PropertyProvider extends ChangeNotifier {
  final PropertyService _propertyService = PropertyService();

  List<PropertyModel> _properties = [];
  PropertyModel? _selectedProperty;
  List<AmenityModel> _amenities = [];
  List<Map<String, dynamic>> _institutions = [];
  List<String> _cities = [];

  bool _isLoading = false;
  String? _errorMessage;

  // Search & Filter State
  String _searchQuery = '';
  String? _selectedCity;
  String? _selectedRentStructure;
  double? _minPrice;
  double? _maxPrice;
  int? _selectedInstitutionId;
  final Set<int> _selectedAmenityIds = {};

  List<PropertyModel> get properties => _properties;
  PropertyModel? get selectedProperty => _selectedProperty;
  List<AmenityModel> get amenities => _amenities;
  List<Map<String, dynamic>> get institutions => _institutions;
  List<String> get cities => _cities;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  String get searchQuery => _searchQuery;
  String? get selectedCity => _selectedCity;
  String? get selectedRentStructure => _selectedRentStructure;
  double? get minPrice => _minPrice;
  double? get maxPrice => _maxPrice;
  int? get selectedInstitutionId => _selectedInstitutionId;
  Set<int> get selectedAmenityIds => _selectedAmenityIds;

  Future<void> fetchProperties({bool refresh = false}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _properties = await _propertyService.getProperties(
        search: _searchQuery.isEmpty ? null : _searchQuery,
        city: _selectedCity,
        rentStructure: _selectedRentStructure,
        minPrice: _minPrice,
        maxPrice: _maxPrice,
        institutionId: _selectedInstitutionId,
        amenityIds: _selectedAmenityIds.isEmpty ? null : _selectedAmenityIds.toList(),
      );
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<void> fetchPropertyDetail(int propertyId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _selectedProperty = await _propertyService.getPropertyDetail(propertyId);
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<void> fetchMetadata() async {
    try {
      final results = await Future.wait([
        _propertyService.getAmenities(),
        _propertyService.getInstitutions(),
        _propertyService.getCities(),
      ]);
      _amenities = results[0] as List<AmenityModel>;
      _institutions = results[1] as List<Map<String, dynamic>>;
      _cities = results[2] as List<String>;
      notifyListeners();
    } catch (_) {
      // Non-fatal metadata fetch
    }
  }

  void setSearchQuery(String query) {
    _searchQuery = query;
    fetchProperties();
  }

  void setFilter({
    String? city,
    String? rentStructure,
    double? minPrice,
    double? maxPrice,
    int? institutionId,
  }) {
    _selectedCity = city;
    _selectedRentStructure = rentStructure;
    _minPrice = minPrice;
    _maxPrice = maxPrice;
    _selectedInstitutionId = institutionId;
    fetchProperties();
  }

  void toggleAmenity(int amenityId) {
    if (_selectedAmenityIds.contains(amenityId)) {
      _selectedAmenityIds.remove(amenityId);
    } else {
      _selectedAmenityIds.add(amenityId);
    }
    notifyListeners();
  }

  void resetFilters() {
    _searchQuery = '';
    _selectedCity = null;
    _selectedRentStructure = null;
    _minPrice = null;
    _maxPrice = null;
    _selectedInstitutionId = null;
    _selectedAmenityIds.clear();
    fetchProperties();
  }
}
