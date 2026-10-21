class NotificationModel {
  final String id;
  final String title;
  final String body;
  final String? type;
  final Map<String, dynamic>? data;
  final bool isRead;
  final String? createdAt;

  NotificationModel({
    required this.id,
    required this.title,
    required this.body,
    this.type,
    this.data,
    this.isRead = false,
    this.createdAt,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    return NotificationModel(
      id: json['id']?.toString() ?? '',
      title: json['title'] ?? json['data']?['title'] ?? 'Notification',
      body: json['body'] ?? json['data']?['message'] ?? json['data']?['body'] ?? '',
      type: json['type'] ?? json['data']?['type'],
      data: json['data'] is Map<String, dynamic> ? json['data'] : null,
      isRead: json['read_at'] != null || json['is_read'] == true,
      createdAt: json['created_at'],
    );
  }
}
