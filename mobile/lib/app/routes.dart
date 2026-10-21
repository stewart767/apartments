import 'package:flutter/material.dart';
import '../models/invoice_model.dart';
import '../models/property_model.dart';
import '../screens/auth/login_screen.dart';
import '../screens/auth/profile_screen.dart';
import '../screens/auth/register_screen.dart';
import '../screens/manager/checkin_screen.dart';
import '../screens/manager/checkout_screen.dart';
import '../screens/manager/manager_dashboard_screen.dart';
import '../screens/notifications/notifications_screen.dart';
import '../screens/owner/create_property_screen.dart';
import '../screens/owner/owner_applications_screen.dart';
import '../screens/owner/owner_dashboard_screen.dart';
import '../screens/owner/owner_payments_screen.dart';
import '../screens/owner/owner_properties_screen.dart';
import '../screens/owner/owner_reports_screen.dart';
import '../screens/splash_screen.dart';
import '../screens/tenant/booking_form_screen.dart';
import '../screens/tenant/contract_detail_screen.dart';
import '../screens/tenant/create_maintenance_screen.dart';
import '../screens/tenant/marketplace_screen.dart';
import '../screens/tenant/property_detail_screen.dart';
import '../screens/tenant/tenant_applications_screen.dart';
import '../screens/tenant/tenant_assets_screen.dart';
import '../screens/tenant/tenant_contracts_screen.dart';
import '../screens/tenant/tenant_invoices_screen.dart';
import '../screens/tenant/tenant_maintenance_screen.dart';
import '../screens/tenant/upload_payment_screen.dart';

class AppRoutes {
  static Route<dynamic> onGenerateRoute(RouteSettings settings) {
    switch (settings.name) {
      case '/':
        return MaterialPageRoute(builder: (_) => const SplashScreen());
      case '/login':
        return MaterialPageRoute(builder: (_) => const LoginScreen());
      case '/register':
        return MaterialPageRoute(builder: (_) => const RegisterScreen());
      case '/profile':
        return MaterialPageRoute(builder: (_) => const ProfileScreen());

      // Tenant Routes
      case '/marketplace':
        return MaterialPageRoute(builder: (_) => const MarketplaceScreen());
      case '/property/detail':
        final propId = settings.arguments as int;
        return MaterialPageRoute(builder: (_) => PropertyDetailScreen(propertyId: propId));
      case '/booking/form':
        final prop = settings.arguments as PropertyModel;
        return MaterialPageRoute(builder: (_) => BookingFormScreen(property: prop));
      case '/tenant/applications':
        return MaterialPageRoute(builder: (_) => const TenantApplicationsScreen());
      case '/tenant/contracts':
        return MaterialPageRoute(builder: (_) => const TenantContractsScreen());
      case '/contract/detail':
        final contractId = settings.arguments as int;
        return MaterialPageRoute(builder: (_) => ContractDetailScreen(contractId: contractId));
      case '/tenant/invoices':
        return MaterialPageRoute(builder: (_) => const TenantInvoicesScreen());
      case '/tenant/payments/upload':
        final inv = settings.arguments as InvoiceModel?;
        return MaterialPageRoute(builder: (_) => UploadPaymentScreen(initialInvoice: inv));
      case '/tenant/maintenance':
        return MaterialPageRoute(builder: (_) => const TenantMaintenanceScreen());
      case '/tenant/maintenance/create':
        return MaterialPageRoute(builder: (_) => const CreateMaintenanceScreen());
      case '/tenant/assets':
        return MaterialPageRoute(builder: (_) => const TenantAssetsScreen());

      // Owner Routes
      case '/owner/dashboard':
        return MaterialPageRoute(builder: (_) => const OwnerDashboardScreen());
      case '/owner/properties':
        return MaterialPageRoute(builder: (_) => const OwnerPropertiesScreen());
      case '/owner/properties/create':
        return MaterialPageRoute(builder: (_) => const CreatePropertyScreen());
      case '/owner/applications':
        return MaterialPageRoute(builder: (_) => const OwnerApplicationsScreen());
      case '/owner/payments':
        return MaterialPageRoute(builder: (_) => const OwnerPaymentsScreen());
      case '/owner/reports':
        return MaterialPageRoute(builder: (_) => const OwnerReportsScreen());

      // Manager Routes
      case '/manager/dashboard':
        return MaterialPageRoute(builder: (_) => const ManagerDashboardScreen());
      case '/manager/checkin':
        return MaterialPageRoute(builder: (_) => const CheckinScreen());
      case '/manager/checkout':
        return MaterialPageRoute(builder: (_) => const CheckoutScreen());

      // Shared
      case '/notifications':
        return MaterialPageRoute(builder: (_) => const NotificationsScreen());

      default:
        return MaterialPageRoute(
          builder: (_) => Scaffold(
            body: Center(child: Text('No route defined for ${settings.name}')),
          ),
        );
    }
  }
}
