import '../core/api_client.dart';
import '../models/maintenance_model.dart';

class ManagerService {
  final ApiClient _api = ApiClient.instance;

  Future<Map<String, dynamic>> getDashboard() async {
    final response = await _api.get('/manager/dashboard');
    return response as Map<String, dynamic>;
  }

  // Check-in Execution
  Future<Map<String, dynamic>> executeCheckin({
    required int contractId,
    required String checkInDate,
    required double depositReceived,
    String? roomConditionNotes,
    Map<String, String>? keyHandoverDetails,
    List<int>? assetIds,
  }) async {
    final data = <String, dynamic>{
      'contract_id': contractId,
      'check_in_date': checkInDate,
      'deposit_received': depositReceived,
      'room_condition_notes': roomConditionNotes,
      'key_handover_details': keyHandoverDetails,
      'asset_ids': assetIds,
    };

    final response = await _api.post('/manager/checkin', data: data);
    return response as Map<String, dynamic>;
  }

  // Check-out Execution
  Future<Map<String, dynamic>> executeCheckout({
    required int contractId,
    required String checkOutDate,
    required double damageCharges,
    required double depositRefunded,
    bool outstandingBalanceSettled = true,
    String? finalInspectionNotes,
    List<Map<String, dynamic>>? assetConditions,
  }) async {
    final data = <String, dynamic>{
      'contract_id': contractId,
      'check_out_date': checkOutDate,
      'damage_charges': damageCharges,
      'deposit_refunded': depositRefunded,
      'outstanding_balance_settled': outstandingBalanceSettled ? 1 : 0,
      'final_inspection_notes': finalInspectionNotes,
      'asset_conditions': assetConditions,
    };

    final response = await _api.post('/manager/checkout', data: data);
    return response as Map<String, dynamic>;
  }

  // Maintenance Triage
  Future<List<MaintenanceModel>> getMaintenanceTickets() async {
    final response = await _api.get('/manager/maintenance');
    if (response is List) {
      return response.map((json) => MaintenanceModel.fromJson(json as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<MaintenanceModel> updateMaintenanceStatus(int ticketId, {
    required String status,
    String? resolutionNotes,
  }) async {
    final response = await _api.post('/manager/maintenance/$ticketId/status', data: {
      'status': status,
      'resolution_notes': resolutionNotes,
    });
    return MaintenanceModel.fromJson(response as Map<String, dynamic>);
  }
}
