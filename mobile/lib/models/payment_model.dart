class PaymentModel {
  final int id;
  final String paymentReference;
  final int tenantId;
  final Map<String, dynamic>? tenant;
  final int propertyId;
  final String? propertyName;
  final int contractId;
  final String? contractNumber;
  final int? invoiceId;
  final String paymentType;
  final double amount;
  final String? dueDate;
  final String? paidDate;
  final String? paymentMethod;
  final String status; // 'pending', 'under_review', 'paid', 'rejected'
  final String? receiptUrl;
  final String? notes;
  final String? createdAt;

  PaymentModel({
    required this.id,
    required this.paymentReference,
    required this.tenantId,
    this.tenant,
    required this.propertyId,
    this.propertyName,
    required this.contractId,
    this.contractNumber,
    this.invoiceId,
    required this.paymentType,
    required this.amount,
    this.dueDate,
    this.paidDate,
    this.paymentMethod,
    required this.status,
    this.receiptUrl,
    this.notes,
    this.createdAt,
  });

  bool get isPaid => status == 'paid';
  bool get isUnderReview => status == 'under_review';
  bool get isPending => status == 'pending';
  bool get isRejected => status == 'rejected';

  factory PaymentModel.fromJson(Map<String, dynamic> json) {
    return PaymentModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      paymentReference: json['payment_reference'] ?? '',
      tenantId: json['tenant_id'] is int ? json['tenant_id'] : int.tryParse(json['tenant_id'].toString()) ?? 0,
      tenant: json['tenant'] is Map<String, dynamic> ? json['tenant'] : null,
      propertyId: json['property_id'] is int ? json['property_id'] : int.tryParse(json['property_id'].toString()) ?? 0,
      propertyName: json['property_name'],
      contractId: json['contract_id'] is int ? json['contract_id'] : int.tryParse(json['contract_id'].toString()) ?? 0,
      contractNumber: json['contract_number'],
      invoiceId: json['invoice_id'] != null ? int.tryParse(json['invoice_id'].toString()) : null,
      paymentType: json['payment_type'] ?? 'rent',
      amount: double.tryParse(json['amount']?.toString() ?? '0') ?? 0.0,
      dueDate: json['due_date'],
      paidDate: json['paid_date'],
      paymentMethod: json['payment_method'],
      status: json['status'] ?? 'pending',
      receiptUrl: json['receipt_url'],
      notes: json['notes'],
      createdAt: json['created_at'],
    );
  }
}
