import 'package:flutter/foundation.dart';

class AppConstants {
  // Environment & Base URL Configuration
  static const String envApiUrl = String.fromEnvironment('API_BASE_URL', defaultValue: '');
  static const String defaultLocalIp = '192.168.1.145';
  static const String defaultProdUrl = 'https://www.reliancehomes.co.tz/api/v1';

  static String _activeBaseUrl = '';

  static String get baseUrl {
    if (_activeBaseUrl.isNotEmpty) {
      return _activeBaseUrl;
    }
    if (envApiUrl.isNotEmpty) {
      return envApiUrl;
    }
    if (kReleaseMode) {
      return defaultProdUrl;
    }
    if (kIsWeb) {
      return 'http://localhost/apartments/public/api/v1';
    }
    // Default development host (local Wi-Fi address reachable from physical phones & emulator)
    return 'http://$defaultLocalIp/apartments/public/api/v1';
  }

  static void setBaseUrl(String url) {
    _activeBaseUrl = url.trim().replaceAll(RegExp(r'/+$'), '');
  }

  static const String appName = 'SmartNest';
  static const String appTagline = 'Smart Property & Hostel Management';
  static const String appVersion = '1.0.0';

  // Storage Keys
  static const String tokenKey = 'smartnest_auth_token';
  static const String userKey = 'smartnest_user_profile';
  static const String roleKey = 'smartnest_user_role';
  static const String themeModeKey = 'smartnest_theme_mode';

  // User Roles
  static const String roleSuperAdmin = 'super_admin';
  static const String roleOwner = 'owner';
  static const String roleManager = 'property_manager';
  static const String roleTenant = 'tenant';

  // Property Rental Structures
  static const String rentEntireProperty = 'entire_property';
  static const String rentPerRoom = 'per_room';
  static const String rentPerBed = 'per_bed';
  static const String rentPerPerson = 'per_person';
  static const String rentPerUnit = 'per_unit';
  static const String rentMixed = 'mixed';

  // Contract Statuses
  static const String contractDraft = 'draft';
  static const String contractPending = 'pending_signatures';
  static const String contractActive = 'active';
  static const String contractTerminated = 'terminated';
  static const String contractExpired = 'expired';

  // Invoice / Payment Statuses
  static const String paymentPending = 'pending';
  static const String paymentUnderReview = 'under_review';
  static const String paymentPaid = 'paid';
  static const String paymentOverdue = 'overdue';
  static const String paymentRejected = 'rejected';

  // Maintenance Priorities & Statuses
  static const String priorityLow = 'low';
  static const String priorityMedium = 'medium';
  static const String priorityHigh = 'high';
  static const String priorityEmergency = 'emergency';

  static const String maintenancePending = 'pending';
  static const String maintenanceAssigned = 'assigned';
  static const String maintenanceInProgress = 'in_progress';
  static const String maintenanceCompleted = 'completed';
  static const String maintenanceCancelled = 'cancelled';
}
