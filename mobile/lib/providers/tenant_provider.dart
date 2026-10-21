import 'dart:io';
import 'package:flutter/material.dart';
import '../models/application_model.dart';
import '../models/contract_model.dart';
import '../models/invoice_model.dart';
import '../models/payment_model.dart';
import '../models/maintenance_model.dart';
import '../models/asset_model.dart';
import '../services/tenant_service.dart';

class TenantProvider extends ChangeNotifier {
  final TenantService _tenantService = TenantService();

  Map<String, dynamic>? _dashboardData;
  List<ApplicationModel> _applications = [];
  List<ContractModel> _contracts = [];
  ContractModel? _selectedContract;
  List<InvoiceModel> _invoices = [];
  List<PaymentModel> _payments = [];
  List<MaintenanceModel> _maintenanceTickets = [];
  List<AssetModel> _assets = [];

  bool _isLoading = false;
  String? _errorMessage;

  Map<String, dynamic>? get dashboardData => _dashboardData;
  List<ApplicationModel> get applications => _applications;
  List<ContractModel> get contracts => _contracts;
  ContractModel? get selectedContract => _selectedContract;
  List<InvoiceModel> get invoices => _invoices;
  List<PaymentModel> get payments => _payments;
  List<MaintenanceModel> get maintenanceTickets => _maintenanceTickets;
  List<AssetModel> get assets => _assets;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Future<void> fetchDashboard() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _dashboardData = await _tenantService.getDashboard();
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<void> fetchApplications() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _applications = await _tenantService.getApplications();
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<bool> submitApplication({
    required int propertyId,
    int? roomId,
    int? bedId,
    required String startDate,
    required String endDate,
    String? tenantNotes,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final app = await _tenantService.submitApplication(
        propertyId: propertyId,
        roomId: roomId,
        bedId: bedId,
        startDate: startDate,
        endDate: endDate,
        tenantNotes: tenantNotes,
      );
      _applications.insert(0, app);
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

  Future<void> fetchContracts() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _contracts = await _tenantService.getContracts();
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<void> fetchContractDetail(int contractId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _selectedContract = await _tenantService.getContractDetail(contractId);
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<bool> acceptContract(int contractId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final updated = await _tenantService.acceptContract(contractId);
      _selectedContract = updated;
      final idx = _contracts.indexWhere((c) => c.id == contractId);
      if (idx != -1) _contracts[idx] = updated;
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

  Future<void> fetchInvoicesAndPayments() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final results = await Future.wait([
        _tenantService.getInvoices(),
        _tenantService.getPayments(),
      ]);
      _invoices = results[0] as List<InvoiceModel>;
      _payments = results[1] as List<PaymentModel>;
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<bool> uploadPaymentProof({
    required int contractId,
    int? invoiceId,
    required double amount,
    required String paymentMethod,
    String? reference,
    required File receiptFile,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final payment = await _tenantService.uploadPaymentProof(
        contractId: contractId,
        invoiceId: invoiceId,
        amount: amount,
        paymentMethod: paymentMethod,
        reference: reference,
        receiptFile: receiptFile,
      );
      _payments.insert(0, payment);
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

  Future<void> fetchMaintenance() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _maintenanceTickets = await _tenantService.getMaintenanceTickets();
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<bool> createMaintenanceTicket({
    required int propertyId,
    int? roomId,
    int? bedId,
    required String title,
    required String description,
    required String priority,
    List<File>? images,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final ticket = await _tenantService.createMaintenanceTicket(
        propertyId: propertyId,
        roomId: roomId,
        bedId: bedId,
        title: title,
        description: description,
        priority: priority,
        imageFiles: images,
      );
      _maintenanceTickets.insert(0, ticket);
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

  Future<void> fetchAssets() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _assets = await _tenantService.getAssets();
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<bool> confirmAssetHandover(int assetId) async {
    try {
      final updated = await _tenantService.confirmAssetHandover(assetId);
      final idx = _assets.indexWhere((a) => a.id == assetId);
      if (idx != -1) _assets[idx] = updated;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
      return false;
    }
  }
}
