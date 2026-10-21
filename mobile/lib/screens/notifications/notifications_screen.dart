import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../app/theme.dart';
import '../../providers/notification_provider.dart';
import '../../widgets/empty_state.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<NotificationProvider>(context, listen: false).fetchNotifications();
    });
  }

  @override
  Widget build(BuildContext context) {
    final prov = Provider.of<NotificationProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        actions: [
          if (prov.notifications.isNotEmpty)
            TextButton(
              onPressed: () => prov.markAllAsRead(),
              child: const Text('Mark all read'),
            ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => prov.fetchNotifications(),
        child: prov.isLoading
            ? const Center(child: CircularProgressIndicator())
            : prov.notifications.isEmpty
                ? const EmptyState(
                    icon: Icons.notifications_none,
                    title: 'All Caught Up!',
                    message: 'You have no new alerts, booking updates, or payment notifications.',
                  )
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: prov.notifications.length,
                    itemBuilder: (context, index) {
                      final item = prov.notifications[index];
                      return Card(
                        margin: const EdgeInsets.only(bottom: 8),
                        color: item.isRead
                            ? (isDark ? AppTheme.cardDark : Colors.white)
                            : AppTheme.primary.withOpacity(0.06),
                        child: ListTile(
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                          leading: CircleAvatar(
                            backgroundColor: item.isRead
                                ? Colors.grey.withOpacity(0.2)
                                : AppTheme.primary.withOpacity(0.15),
                            child: Icon(
                              Icons.notifications,
                              color: item.isRead ? Colors.grey : AppTheme.primary,
                              size: 20,
                            ),
                          ),
                          title: Text(
                            item.title,
                            style: GoogleFonts.inter(
                              fontWeight: item.isRead ? FontWeight.normal : FontWeight.bold,
                              fontSize: 14,
                            ),
                          ),
                          subtitle: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const SizedBox(height: 4),
                              Text(item.body, style: GoogleFonts.inter(fontSize: 12)),
                              const SizedBox(height: 4),
                              Text(
                                item.createdAt?.substring(0, 10) ?? '',
                                style: GoogleFonts.inter(fontSize: 11, color: AppTheme.textMutedLight),
                              ),
                            ],
                          ),
                          onTap: () {
                            if (!item.isRead) prov.markAsRead(item.id);
                          },
                        ),
                      );
                    },
                  ),
      ),
    );
  }
}
