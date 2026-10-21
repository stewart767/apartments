class OwnerReportModel {
  final Map<String, dynamic> occupancy;
  final Map<String, dynamic> financialSummary;
  final List<dynamic> monthlyRevenue;
  final Map<String, dynamic> paymentMethods;
  final Map<String, dynamic> maintenance;

  OwnerReportModel({
    required this.occupancy,
    required this.financialSummary,
    this.monthlyRevenue = const [],
    this.paymentMethods = const {},
    this.maintenance = const {},
  });

  factory OwnerReportModel.fromJson(Map<String, dynamic> json) {
    return OwnerReportModel(
      occupancy: json['occupancy'] is Map<String, dynamic> ? json['occupancy'] : {},
      financialSummary: json['financial_summary'] is Map<String, dynamic> ? json['financial_summary'] : {},
      monthlyRevenue: json['monthly_revenue'] is List ? json['monthly_revenue'] : [],
      paymentMethods: json['payment_methods'] is Map<String, dynamic> ? json['payment_methods'] : {},
      maintenance: json['maintenance'] is Map<String, dynamic> ? json['maintenance'] : {},
    );
  }
}
