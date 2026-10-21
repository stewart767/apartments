import '../core/api_client.dart';
import '../models/notification_model.dart';

class NotificationService {
  final ApiClient _api = ApiClient.instance;

  Future<List<NotificationModel>> getNotifications() async {
    final response = await _api.get('/notifications');
    if (response is List) {
      return response.map((json) => NotificationModel.fromJson(json as Map<String, dynamic>)).toList();
    }
    return [];
  }

  Future<int> getUnreadCount() async {
    final response = await _api.get('/notifications/unread-count');
    if (response is Map<String, dynamic> && response.containsKey('unread_count')) {
      return int.tryParse(response['unread_count'].toString()) ?? 0;
    }
    return 0;
  }

  Future<void> markAsRead(String id) async {
    await _api.post('/notifications/$id/read');
  }

  Future<void> markAllAsRead() async {
    await _api.post('/notifications/mark-all-read');
  }
}
