import 'package:flutter/material.dart';
import '../models/notification_model.dart';
import '../services/notification_service.dart';

class NotificationProvider extends ChangeNotifier {
  final NotificationService _service = NotificationService();

  List<NotificationModel> _notifications = [];
  int _unreadCount = 0;
  bool _isLoading = false;

  List<NotificationModel> get notifications => _notifications;
  int get unreadCount => _unreadCount;
  bool get isLoading => _isLoading;

  Future<void> fetchNotifications() async {
    _isLoading = true;
    notifyListeners();

    try {
      _notifications = await _service.getNotifications();
      _unreadCount = await _service.getUnreadCount();
      _isLoading = false;
    } catch (_) {
      _isLoading = false;
    }
    notifyListeners();
  }

  Future<void> markAsRead(String id) async {
    try {
      await _service.markAsRead(id);
      final idx = _notifications.indexWhere((n) => n.id == id);
      if (idx != -1) {
        _notifications[idx] = NotificationModel(
          id: _notifications[idx].id,
          title: _notifications[idx].title,
          body: _notifications[idx].body,
          type: _notifications[idx].type,
          data: _notifications[idx].data,
          isRead: true,
          createdAt: _notifications[idx].createdAt,
        );
      }
      if (_unreadCount > 0) _unreadCount--;
      notifyListeners();
    } catch (_) {}
  }

  Future<void> markAllAsRead() async {
    try {
      await _service.markAllAsRead();
      _notifications = _notifications.map((n) => NotificationModel(
        id: n.id,
        title: n.title,
        body: n.body,
        type: n.type,
        data: n.data,
        isRead: true,
        createdAt: n.createdAt,
      )).toList();
      _unreadCount = 0;
      notifyListeners();
    } catch (_) {}
  }
}
