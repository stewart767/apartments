import 'dart:io';
import '../core/api_client.dart';
import '../models/application_model.dart';
import '../models/contract_model.dart';
import '../models/invoice_model.dart';
import '../models/payment_model.dart';
import '../models/maintenance_model.dart';
import '../models/asset_model.dart';

class TenantService {
  final ApiClient _api = ApiClient.instance;

  Future<Map<String, dynamic>> getDashboard() async {
    final response = await _api.get('/tenant/dashboard');
    return response as Map<String, dynamic>;
  }

  // Applications
  Future<List<ApplicationModel>> getApplications() async {
    final response = await _api.get('/tenant/applications');
    if (response is List) {
      return response.map((json) => ApplicationModel.fromJson(json as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<ApplicationModel> submitApplication({
    required int propertyId,
    int? roomId,
    int? bedId,
    required String startDate,
    required String endDate,
    String? tenantNotes,
  }) async {
    final response = await _api.post('/tenant/applications', data: {
      'property_id': propertyId,
      'room_id': roomId,
      'bed_id': bedId,
      'start_date': startDate,
      'end_date': endDate,
      'tenant_notes': tenantNotes,
    });
    return ApplicationModel.fromJson(response as Map<String, dynamic>);
  }

  Future<void> cancelApplication(int applicationId) async {
    await _api.delete('/tenant/applications/$applicationId');
  }

  // Contracts
  Future<List<ContractModel>> getContracts() async {
    final response = await _api.get('/tenant/contracts');
    if (response is List) {
      return response.map((json) => ContractModel.fromJson(json as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<ContractModel> getContractDetail(int contractId) async {
    final response = await _api.get('/tenant/contracts/$contractId');
    return ContractModel.fromJson(response as Map<String, dynamic>);
  }

  Future<ContractModel> acceptContract(int contractId) async {
    final response = await _api.post('/tenant/contracts/$contractId/accept');
    return ContractModel.fromJson(response as Map<String, dynamic>);
  }

  // Invoices & Payments
  Future<List<InvoiceModel>> getInvoices() async {
    final response = await _api.get('/tenant/invoices');
    if (response is List) {
      return response.map((json) => InvoiceModel.fromJson(json as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<List<PaymentModel>> getPayments() async {
    final response = await _api.get('/tenant/payments');
    if (response is List) {
      return response.map((json) => PaymentModel.fromJson(json as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<PaymentModel> uploadPaymentProof({
    required int contractId,
    int? invoiceId,
    required double amount,
    required String paymentMethod,
    String? reference,
    required File receiptFile,
  }) async {
    final fields = <String, dynamic>{
      'contract_id': contractId,
      'amount': amount,
      'payment_method': paymentMethod,
    };
    if (invoiceId != null) fields['invoice_id'] = invoiceId;
    if (reference != null) fields['reference'] = reference;

    final response = await _api.postMultipart(
      '/tenant/payments',
      fields: fields,
      files: {'receipt': receiptFile},
    );
    return PaymentModel.fromJson(response as Map<String, dynamic>);
  }

  // Maintenance
  Future<List<MaintenanceModel>> getMaintenanceTickets() async {
    final response = await _api.get('/tenant/maintenance');
    if (response is List) {
      return response.map((json) => MaintenanceModel.fromJson(json as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<MaintenanceModel> createMaintenanceTicket({
    required int propertyId,
    int? roomId,
    int? bedId,
    required String title,
    required String description,
    required String priority,
    List<File>? imageFiles,
  }) async {
    final fields = <String, dynamic>{
      'property_id': propertyId,
      'title': title,
      'description': description,
      'priority': priority,
    };
    if (roomId != null) fields['room_id'] = roomId;
    if (bedId != null) fields['bed_id'] = bedId;

    final response = await _api.postMultipart(
      '/tenant/maintenance',
      fields: fields,
      multiFiles: imageFiles,
      multiFilesKey: 'images',
    );
    return MaintenanceModel.fromJson(response as Map<String, dynamic>);
  }

  // Assets
  Future<List<AssetModel>> getAssets() async {
    final response = await _api.get('/tenant/assets');
    if (response is List) {
      return response.map((json) => AssetModel.fromJson(json as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<AssetModel> confirmAssetHandover(int assetId) async {
    final response = await _api.post('/tenant/assets/$assetId/confirm');
    return AssetModel.fromJson(response as Map<String, dynamic>);
  }
}
