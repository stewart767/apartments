import 'dart:io';
import '../core/api_client.dart';
import '../models/property_model.dart';
import '../models/application_model.dart';
import '../models/contract_model.dart';
import '../models/payment_model.dart';
import '../models/maintenance_model.dart';
import '../models/asset_model.dart';
import '../models/report_model.dart';

class OwnerService {
  final ApiClient _api = ApiClient.instance;

  Future<Map<String, dynamic>> getDashboard() async {
    final response = await _api.get('/owner/dashboard');
    return response as Map<String, dynamic>;
  }

  // Properties
  Future<List<PropertyModel>> getProperties() async {
    final response = await _api.get('/owner/properties');
    if (response is List) {
      return response.map((json) => PropertyModel.fromJson(json as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<PropertyModel> createProperty({
    required String propertyName,
    String? propertyType,
    required String rentStructure,
    required String address,
    String? city,
    required double price,
    double? depositAmount,
    String billingPeriod = 'monthly',
    String? description,
    List<int>? amenityIds,
    List<File>? imageFiles,
  }) async {
    final fields = <String, dynamic>{
      'property_name': propertyName,
      'property_type': propertyType ?? 'hostel',
      'rent_structure': rentStructure,
      'address': address,
      'city': city,
      'price': price,
      'deposit_amount': depositAmount ?? 0.0,
      'billing_period': billingPeriod,
      'description': description,
    };
    if (amenityIds != null && amenityIds.isNotEmpty) {
      for (int i = 0; i < amenityIds.length; i++) {
        fields['amenities[$i]'] = amenityIds[i];
      }
    }

    dynamic response;
    if (imageFiles != null && imageFiles.isNotEmpty) {
      response = await _api.postMultipart(
        '/owner/properties',
        fields: fields,
        multiFiles: imageFiles,
        multiFilesKey: 'images',
      );
    } else {
      response = await _api.post('/owner/properties', data: fields);
    }

    return PropertyModel.fromJson(response as Map<String, dynamic>);
  }

  // Applications
  Future<List<ApplicationModel>> getApplications({int? propertyId, String? status}) async {
    final query = <String, dynamic>{};
    if (propertyId != null) query['property_id'] = propertyId;
    if (status != null) query['status'] = status;

    final response = await _api.get('/owner/applications', queryParameters: query);
    if (response is List) {
      return response.map((json) => ApplicationModel.fromJson(json as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<ApplicationModel> updateApplicationStatus(int applicationId, {
    required String status,
    String? ownerNotes,
  }) async {
    final response = await _api.post('/owner/applications/$applicationId/status', data: {
      'status': status,
      'owner_notes': ownerNotes,
    });
    return ApplicationModel.fromJson(response as Map<String, dynamic>);
  }

  // Contracts
  Future<List<ContractModel>> getContracts({int? propertyId, String? status}) async {
    final query = <String, dynamic>{};
    if (propertyId != null) query['property_id'] = propertyId;
    if (status != null) query['status'] = status;

    final response = await _api.get('/owner/contracts', queryParameters: query);
    if (response is List) {
      return response.map((json) => ContractModel.fromJson(json as Map<String, dynamic>)).toList();
    }
    return [];
  }

  // Payments
  Future<List<PaymentModel>> getPayments({int? propertyId, String? status}) async {
    final query = <String, dynamic>{};
    if (propertyId != null) query['property_id'] = propertyId;
    if (status != null) query['status'] = status;

    final response = await _api.get('/owner/payments', queryParameters: query);
    if (response is List) {
      return response.map((json) => PaymentModel.fromJson(json as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<PaymentModel> verifyPayment(int paymentId, {
    required String status,
    String? rejectionReason,
  }) async {
    final response = await _api.post('/owner/payments/$paymentId/verify', data: {
      'status': status,
      'rejection_reason': rejectionReason,
    });
    return PaymentModel.fromJson(response as Map<String, dynamic>);
  }

  // Maintenance
  Future<List<MaintenanceModel>> getMaintenanceTickets({int? propertyId, String? status}) async {
    final query = <String, dynamic>{};
    if (propertyId != null) query['property_id'] = propertyId;
    if (status != null) query['status'] = status;

    final response = await _api.get('/owner/maintenance', queryParameters: query);
    if (response is List) {
      return response.map((json) => MaintenanceModel.fromJson(json as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<MaintenanceModel> assignMaintenanceTicket(int ticketId, {
    required int assignedToUserId,
    String? priority,
  }) async {
    final response = await _api.post('/owner/maintenance/$ticketId/assign', data: {
      'assigned_to': assignedToUserId,
      'priority': priority,
    });
    return MaintenanceModel.fromJson(response as Map<String, dynamic>);
  }

  // Assets
  Future<List<AssetModel>> getAssets({int? propertyId}) async {
    final query = propertyId != null ? {'property_id': propertyId} : null;
    final response = await _api.get('/owner/assets', queryParameters: query);
    if (response is List) {
      return response.map((json) => AssetModel.fromJson(json as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<AssetModel> createAsset({
    required int propertyId,
    int? roomId,
    int? bedId,
    required String assetName,
    String? assetCode,
    String? category,
    String condition = 'good',
    double? estimatedValue,
  }) async {
    final response = await _api.post('/owner/assets', data: {
      'property_id': propertyId,
      'room_id': roomId,
      'bed_id': bedId,
      'asset_name': assetName,
      'asset_code': assetCode,
      'category': category,
      'condition': condition,
      'estimated_value': estimatedValue,
    });
    return AssetModel.fromJson(response as Map<String, dynamic>);
  }

  // Reports
  Future<OwnerReportModel> getReports({int? propertyId, int? year}) async {
    final query = <String, dynamic>{};
    if (propertyId != null) query['property_id'] = propertyId;
    if (year != null) query['year'] = year;

    final response = await _api.get('/owner/reports', queryParameters: query);
    return OwnerReportModel.fromJson(response as Map<String, dynamic>);
  }
}
