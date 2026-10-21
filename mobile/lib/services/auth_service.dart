import 'dart:io';
import '../core/api_client.dart';
import '../core/storage_service.dart';
import '../models/user_model.dart';

class AuthService {
  final ApiClient _api = ApiClient.instance;
  final StorageService _storage = StorageService.instance;

  Future<UserModel> login({
    required String email,
    required String password,
    String? deviceName,
  }) async {
    final response = await _api.post('/auth/login', data: {
      'email': email,
      'password': password,
      'device_name': deviceName ?? 'SmartNest Mobile App',
    });

    final token = response['token'];
    final userJson = response['user'];

    await _storage.saveToken(token);
    await _storage.saveUser(userJson);

    return UserModel.fromJson(userJson);
  }

  Future<UserModel> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    required String role,
    String? phone,
    String? whatsapp,
    String? gender,
    bool isStudent = false,
    String? studentRegNumber,
    int? institutionId,
    String? customInstitutionName,
    String? nationalIdPassport,
    String? emergencyContact,
    String? businessName,
    String? idType,
    String? idNumber,
  }) async {
    final data = {
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
      'role': role,
      'phone': phone,
      'whatsapp': whatsapp,
      'gender': gender,
      'is_student': isStudent ? 1 : 0,
      'student_reg_number': studentRegNumber,
      'institution_id': institutionId,
      'custom_institution_name': customInstitutionName,
      'national_id_passport': nationalIdPassport,
      'emergency_contact': emergencyContact,
      'business_name': businessName,
      'id_type': idType,
      'id_number': idNumber,
    };

    final response = await _api.post('/auth/register', data: data);
    final token = response['token'];
    final userJson = response['user'];

    await _storage.saveToken(token);
    await _storage.saveUser(userJson);

    return UserModel.fromJson(userJson);
  }

  Future<UserModel> getProfile() async {
    final response = await _api.get('/auth/profile');
    await _storage.saveUser(response);
    return UserModel.fromJson(response);
  }

  Future<UserModel> updateProfile({
    String? name,
    String? phone,
    String? whatsapp,
    String? gender,
    String? dateOfBirth,
    File? avatarFile,
    Map<String, dynamic>? tenantDetails,
    Map<String, dynamic>? ownerDetails,
  }) async {
    final fields = <String, dynamic>{};
    if (name != null) fields['name'] = name;
    if (phone != null) fields['phone'] = phone;
    if (whatsapp != null) fields['whatsapp'] = whatsapp;
    if (gender != null) fields['gender'] = gender;
    if (dateOfBirth != null) fields['date_of_birth'] = dateOfBirth;

    if (tenantDetails != null) {
      tenantDetails.forEach((k, v) => fields['tenant_detail[$k]'] = v);
    }
    if (ownerDetails != null) {
      ownerDetails.forEach((k, v) => fields['owner_detail[$k]'] = v);
    }

    dynamic response;
    if (avatarFile != null) {
      response = await _api.postMultipart(
        '/auth/profile',
        fields: fields,
        files: {'avatar': avatarFile},
      );
    } else {
      response = await _api.put('/auth/profile', data: fields);
    }

    await _storage.saveUser(response);
    return UserModel.fromJson(response);
  }

  Future<void> updateFcmToken(String fcmToken) async {
    await _api.post('/auth/fcm-token', data: {'fcm_token': fcmToken});
  }

  Future<void> logout() async {
    try {
      await _api.post('/auth/logout');
    } catch (_) {
      // Ignore network errors on logout
    } finally {
      await _storage.clearSession();
    }
  }

  Future<bool> isLoggedIn() async {
    final token = await _storage.getToken();
    return token != null && token.isNotEmpty;
  }
}
