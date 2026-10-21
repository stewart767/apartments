import 'dart:io';
import 'package:flutter/material.dart';
import '../core/storage_service.dart';
import '../models/user_model.dart';
import '../services/auth_service.dart';

enum AuthStatus { uninitialized, authenticated, authenticating, unauthenticated }

class AuthProvider extends ChangeNotifier {
  final AuthService _authService = AuthService();
  final StorageService _storageService = StorageService.instance;

  AuthStatus _status = AuthStatus.uninitialized;
  UserModel? _currentUser;
  String? _errorMessage;

  AuthStatus get status => _status;
  UserModel? get currentUser => _currentUser;
  String? get errorMessage => _errorMessage;
  bool get isAuthenticated => _status == AuthStatus.authenticated;

  Future<void> initializeAuth() async {
    final hasToken = await _authService.isLoggedIn();
    if (!hasToken) {
      _status = AuthStatus.unauthenticated;
      notifyListeners();
      return;
    }

    try {
      final userMap = await _storageService.getUser();
      if (userMap != null) {
        _currentUser = UserModel.fromJson(userMap);
      }
      _currentUser = await _authService.getProfile();
      _status = AuthStatus.authenticated;
    } catch (e) {
      _status = AuthStatus.unauthenticated;
      await _storageService.clearSession();
    }
    notifyListeners();
  }

  Future<bool> login({required String email, required String password}) async {
    _status = AuthStatus.authenticating;
    _errorMessage = null;
    notifyListeners();

    try {
      _currentUser = await _authService.login(email: email, password: password);
      _status = AuthStatus.authenticated;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      _status = AuthStatus.unauthenticated;
      notifyListeners();
      return false;
    }
  }

  Future<bool> register({
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
    _status = AuthStatus.authenticating;
    _errorMessage = null;
    notifyListeners();

    try {
      _currentUser = await _authService.register(
        name: name,
        email: email,
        password: password,
        passwordConfirmation: passwordConfirmation,
        role: role,
        phone: phone,
        whatsapp: whatsapp,
        gender: gender,
        isStudent: isStudent,
        studentRegNumber: studentRegNumber,
        institutionId: institutionId,
        customInstitutionName: customInstitutionName,
        nationalIdPassport: nationalIdPassport,
        emergencyContact: emergencyContact,
        businessName: businessName,
        idType: idType,
        idNumber: idNumber,
      );
      _status = AuthStatus.authenticated;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      _status = AuthStatus.unauthenticated;
      notifyListeners();
      return false;
    }
  }

  Future<bool> updateProfile({
    String? name,
    String? phone,
    String? whatsapp,
    String? gender,
    String? dateOfBirth,
    File? avatarFile,
    Map<String, dynamic>? tenantDetails,
    Map<String, dynamic>? ownerDetails,
  }) async {
    try {
      _currentUser = await _authService.updateProfile(
        name: name,
        phone: phone,
        whatsapp: whatsapp,
        gender: gender,
        dateOfBirth: dateOfBirth,
        avatarFile: avatarFile,
        tenantDetails: tenantDetails,
        ownerDetails: ownerDetails,
      );
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    await _authService.logout();
    _currentUser = null;
    _status = AuthStatus.unauthenticated;
    notifyListeners();
  }

  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }
}
