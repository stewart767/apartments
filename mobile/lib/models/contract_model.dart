class ContractModel {
  final int id;
  final String contractNumber;
  final int? applicationId;
  final int tenantId;
  final Map<String, dynamic>? tenant;
  final int propertyId;
  final Map<String, dynamic>? property;
  final Map<String, dynamic>? room;
  final Map<String, dynamic>? bed;
  final String? startDate;
  final String? endDate;
  final double rentAmount;
  final double depositAmount;
  final String billingFrequency;
  final String? rules;
  final String? termsAndConditions;
  final bool tenantAccepted;
  final String? tenantAcceptedAt;
  final bool ownerAccepted;
  final String? ownerAcceptedAt;
  final String status; // 'draft', 'pending_signatures', 'active', 'terminated', 'expired'
  final String? pdfDownloadUrl;
  final CheckInModel? checkIn;
  final CheckOutModel? checkOut;
  final String? createdAt;

  ContractModel({
    required this.id,
    required this.contractNumber,
    this.applicationId,
    required this.tenantId,
    this.tenant,
    required this.propertyId,
    this.property,
    this.room,
    this.bed,
    this.startDate,
    this.endDate,
    required this.rentAmount,
    this.depositAmount = 0.0,
    required this.billingFrequency,
    this.rules,
    this.termsAndConditions,
    this.tenantAccepted = false,
    this.tenantAcceptedAt,
    this.ownerAccepted = false,
    this.ownerAcceptedAt,
    required this.status,
    this.pdfDownloadUrl,
    this.checkIn,
    this.checkOut,
    this.createdAt,
  });

  bool get isActive => status == 'active';
  bool get isPendingSignature => status == 'pending_signatures' && !tenantAccepted;

  factory ContractModel.fromJson(Map<String, dynamic> json) {
    return ContractModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      contractNumber: json['contract_number'] ?? '',
      applicationId: json['application_id'] != null ? int.tryParse(json['application_id'].toString()) : null,
      tenantId: json['tenant_id'] is int ? json['tenant_id'] : int.tryParse(json['tenant_id'].toString()) ?? 0,
      tenant: json['tenant'] is Map<String, dynamic> ? json['tenant'] : null,
      propertyId: json['property_id'] is int ? json['property_id'] : int.tryParse(json['property_id'].toString()) ?? 0,
      property: json['property'] is Map<String, dynamic> ? json['property'] : null,
      room: json['room'] is Map<String, dynamic> ? json['room'] : null,
      bed: json['bed'] is Map<String, dynamic> ? json['bed'] : null,
      startDate: json['start_date'],
      endDate: json['end_date'],
      rentAmount: double.tryParse(json['rent_amount']?.toString() ?? '0') ?? 0.0,
      depositAmount: double.tryParse(json['deposit_amount']?.toString() ?? '0') ?? 0.0,
      billingFrequency: json['billing_frequency'] ?? 'monthly',
      rules: json['rules'],
      termsAndConditions: json['terms_and_conditions'],
      tenantAccepted: json['tenant_accepted'] == true || json['tenant_accepted'] == 1,
      tenantAcceptedAt: json['tenant_accepted_at'],
      ownerAccepted: json['owner_accepted'] == true || json['owner_accepted'] == 1,
      ownerAcceptedAt: json['owner_accepted_at'],
      status: json['status'] ?? 'draft',
      pdfDownloadUrl: json['pdf_download_url'],
      checkIn: json['check_in'] != null ? CheckInModel.fromJson(json['check_in']) : null,
      checkOut: json['check_out'] != null ? CheckOutModel.fromJson(json['check_out']) : null,
      createdAt: json['created_at'],
    );
  }
}

class CheckInModel {
  final String? checkInDate;
  final double depositReceived;
  final String? roomConditionNotes;

  CheckInModel({
    this.checkInDate,
    this.depositReceived = 0.0,
    this.roomConditionNotes,
  });

  factory CheckInModel.fromJson(Map<String, dynamic> json) {
    return CheckInModel(
      checkInDate: json['check_in_date'],
      depositReceived: double.tryParse(json['deposit_received']?.toString() ?? '0') ?? 0.0,
      roomConditionNotes: json['room_condition_notes'],
    );
  }
}

class CheckOutModel {
  final String? checkOutDate;
  final double damageCharges;
  final double depositRefunded;
  final bool outstandingBalanceSettled;

  CheckOutModel({
    this.checkOutDate,
    this.damageCharges = 0.0,
    this.depositRefunded = 0.0,
    this.outstandingBalanceSettled = false,
  });

  factory CheckOutModel.fromJson(Map<String, dynamic> json) {
    return CheckOutModel(
      checkOutDate: json['check_out_date'],
      damageCharges: double.tryParse(json['damage_charges']?.toString() ?? '0') ?? 0.0,
      depositRefunded: double.tryParse(json['deposit_refunded']?.toString() ?? '0') ?? 0.0,
      outstandingBalanceSettled: json['outstanding_balance_settled'] == true || json['outstanding_balance_settled'] == 1,
    );
  }
}
