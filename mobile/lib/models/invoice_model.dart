import 'payment_model.dart';

class InvoiceModel {
  final int id;
  final String invoiceNumber;
  final int contractId;
  final int propertyId;
  final String? propertyName;
  final int tenantId;
  final String invoiceType;
  final double amount;
  final String? dueDate;
  final String status; // 'pending', 'paid', 'overdue', 'cancelled'
  final String? notes;
  final List<PaymentModel> payments;
  final String? createdAt;

  InvoiceModel({
    required this.id,
    required this.invoiceNumber,
    required this.contractId,
    required this.propertyId,
    this.propertyName,
    required this.tenantId,
    required this.invoiceType,
    required this.amount,
    this.dueDate,
    required this.status,
    this.notes,
    this.payments = const [],
    this.createdAt,
  });

  bool get isPaid => status == 'paid';
  bool get isPending => status == 'pending';
  bool get isOverdue => status == 'overdue';

  factory InvoiceModel.fromJson(Map<String, dynamic> json) {
    var paymentsList = <PaymentModel>[];
    if (json['payments'] is List) {
      paymentsList = (json['payments'] as List)
          .map((p) => PaymentModel.fromJson(p is Map<String, dynamic> ? p : {}))
          .toList();
    }
    return InvoiceModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      invoiceNumber: json['invoice_number'] ?? '',
      contractId: json['contract_id'] is int ? json['contract_id'] : int.tryParse(json['contract_id'].toString()) ?? 0,
      propertyId: json['property_id'] is int ? json['property_id'] : int.tryParse(json['property_id'].toString()) ?? 0,
      propertyName: json['property_name'],
      tenantId: json['tenant_id'] is int ? json['tenant_id'] : int.tryParse(json['tenant_id'].toString()) ?? 0,
      invoiceType: json['invoice_type'] ?? 'rent',
      amount: double.tryParse(json['amount']?.toString() ?? '0') ?? 0.0,
      dueDate: json['due_date'],
      status: json['status'] ?? 'pending',
      notes: json['notes'],
      payments: paymentsList,
      createdAt: json['created_at'],
    );
  }
}
