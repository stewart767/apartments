import 'package:flutter/material.dart';
import '../models/maintenance_model.dart';
import '../services/manager_service.dart';

class ManagerProvider extends ChangeNotifier {
  final ManagerService _managerService = ManagerService();

  Map<String, dynamic>? _dashboardData;
  List<MaintenanceModel> _maintenanceTickets = [];
  bool _isLoading = false;
  String? _errorMessage;

  Map<String, dynamic>? get dashboardData => _dashboardData;
  List<MaintenanceModel> get maintenanceTickets => _maintenanceTickets;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Future<void> fetchDashboard() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _dashboardData = await _managerService.getDashboard();
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<bool> executeCheckin({
    required int contractId,
    required String checkInDate,
    required double depositReceived,
    String? roomConditionNotes,
    Map<String, String>? keyHandoverDetails,
    List<int>? assetIds,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _managerService.executeCheckin(
        contractId: contractId,
        checkInDate: checkInDate,
        depositReceived: depositReceived,
        roomConditionNotes: roomConditionNotes,
        keyHandoverDetails: keyHandoverDetails,
        assetIds: assetIds,
      );
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

  Future<bool> executeCheckout({
    required int contractId,
    required String checkOutDate,
    required double damageCharges,
    required double depositRefunded,
    bool outstandingBalanceSettled = true,
    String? finalInspectionNotes,
    List<Map<String, dynamic>>? assetConditions,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _managerService.executeCheckout(
        contractId: contractId,
        checkOutDate: checkOutDate,
        damageCharges: damageCharges,
        depositRefunded: depositRefunded,
        outstandingBalanceSettled: outstandingBalanceSettled,
        finalInspectionNotes: finalInspectionNotes,
        assetConditions: assetConditions,
      );
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
      _maintenanceTickets = await _managerService.getMaintenanceTickets();
      _isLoading = false;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<bool> updateMaintenanceStatus(int ticketId, {required String status, String? resolutionNotes}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final updated = await _managerService.updateMaintenanceStatus(ticketId, status: status, resolutionNotes: resolutionNotes);
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
}
