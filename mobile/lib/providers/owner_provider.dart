import 'dart:io';
import 'package:flutter/material.dart';
import '../models/property_model.dart';
import '../models/application_model.dart';
import '../models/contract_model.dart';
import '../models/payment_model.dart';
import '../models/maintenance_model.dart';
import '../models/asset_model.dart';
import '../models/report_model.dart';
import '../services/owner_service.dart';

class OwnerProvider extends ChangeNotifier {
  final OwnerService _ownerService = OwnerService();

  Map<String, dynamic>? _dashboardData;
  List<PropertyModel> _properties = [];
  List<ApplicationModel> _applications = [];
  List<ContractModel> _contracts = [];
  List<PaymentModel> _payments = [];
  List<MaintenanceModel> _maintenanceTickets = [];
  List<AssetModel> _assets = [];
  OwnerReportModel? _reports;

  bool _isLoading = false;
  String? _errorMessage;

  Map<String, dynamic>? get dashboardData => _dashboardData;
  List<PropertyModel> get properties => _properties;
  List<ApplicationModel> get applications => _applications;
  List<ContractModel> get contracts => _contracts;
  List<PaymentModel> get payments => _payments;
  List<MaintenanceModel> get maintenanceTickets => _maintenanceTickets;
  List<AssetModel> get assets => _assets;
  OwnerReportModel? get reports => _reports;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Future<void> fetchDashboard() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _dashboardData = await _ownerService.getDashboard();
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<void> fetchProperties() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _properties = await _ownerService.getProperties();
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<bool> createProperty({
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
    List<File>? images,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final prop = await _ownerService.createProperty(
        propertyName: propertyName,
        propertyType: propertyType,
        rentStructure: rentStructure,
        address: address,
        city: city,
        price: price,
        depositAmount: depositAmount,
        billingPeriod: billingPeriod,
        description: description,
        amenityIds: amenityIds,
        imageFiles: images,
      );
      _properties.insert(0, prop);
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> fetchApplications({int? propertyId, String? status}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _applications = await _ownerService.getApplications(propertyId: propertyId, status: status);
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<bool> updateApplicationStatus(int applicationId, {required String status, String? notes}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final updated = await _ownerService.updateApplicationStatus(applicationId, status: status, ownerNotes: notes);
      final idx = _applications.indexWhere((a) => a.id == applicationId);
      if (idx != -1) _applications[idx] = updated;
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> fetchContracts({int? propertyId, String? status}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _contracts = await _ownerService.getContracts(propertyId: propertyId, status: status);
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<void> fetchPayments({int? propertyId, String? status}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _payments = await _ownerService.getPayments(propertyId: propertyId, status: status);
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<bool> verifyPayment(int paymentId, {required String status, String? reason}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final updated = await _ownerService.verifyPayment(paymentId, status: status, rejectionReason: reason);
      final idx = _payments.indexWhere((p) => p.id == paymentId);
      if (idx != -1) _payments[idx] = updated;
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> fetchMaintenance({int? propertyId, String? status}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _maintenanceTickets = await _ownerService.getMaintenanceTickets(propertyId: propertyId, status: status);
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<bool> assignMaintenanceTicket(int ticketId, {required int assignedToUserId, String? priority}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final updated = await _ownerService.assignMaintenanceTicket(ticketId, assignedToUserId: assignedToUserId, priority: priority);
      final idx = _maintenanceTickets.indexWhere((t) => t.id == ticketId);
      if (idx != -1) _maintenanceTickets[idx] = updated;
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> fetchAssets({int? propertyId}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _assets = await _ownerService.getAssets(propertyId: propertyId);
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<void> fetchReports({int? propertyId, int? year}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _reports = await _ownerService.getReports(propertyId: propertyId, year: year);
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }
}
