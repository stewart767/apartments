import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'app/routes.dart';
import 'app/theme.dart';
import 'core/api_client.dart';
import 'core/constants.dart';
import 'core/storage_service.dart';
import 'providers/auth_provider.dart';
import 'providers/manager_provider.dart';
import 'providers/notification_provider.dart';
import 'providers/owner_provider.dart';
import 'providers/property_provider.dart';
import 'providers/tenant_provider.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final savedUrl = await StorageService.instance.getServerUrl();
  if (savedUrl != null && savedUrl.isNotEmpty) {
    ApiClient.instance.setBaseUrl(savedUrl);
  }
  runApp(const SmartNestApp());
}

class SmartNestApp extends StatelessWidget {
  const SmartNestApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => PropertyProvider()),
        ChangeNotifierProvider(create: (_) => TenantProvider()),
        ChangeNotifierProvider(create: (_) => OwnerProvider()),
        ChangeNotifierProvider(create: (_) => ManagerProvider()),
        ChangeNotifierProvider(create: (_) => NotificationProvider()),
      ],
      child: MaterialApp(
        title: AppConstants.appName,
        debugShowCheckedModeBanner: false,
        theme: AppTheme.lightTheme,
        darkTheme: AppTheme.darkTheme,
        themeMode: ThemeMode.system,
        initialRoute: '/',
        onGenerateRoute: AppRoutes.onGenerateRoute,
      ),
    );
  }
}
