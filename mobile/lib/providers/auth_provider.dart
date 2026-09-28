import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import '../core/network/api_client.dart';
import '../core/network/api_config.dart';
import '../core/network/storage_service.dart';
import '../data/models/user.dart';

enum AuthState { uninitialized, authenticated, unauthenticated, loading }

class AuthProvider extends ChangeNotifier {
  final ApiClient _apiClient;

  AuthState _state = AuthState.uninitialized;
  User? _user;
  String? _errorMessage;

  AuthState get state => _state;
  User? get user => _user;
  bool get isAuthenticated => _state == AuthState.authenticated;
  String? get errorMessage => _errorMessage;

  AuthProvider({ApiClient? apiClient}) : _apiClient = apiClient ?? ApiClient();

  Future<void> checkAuthStatus() async {
    _state = AuthState.loading;
    notifyListeners();

    try {
      final storage = await StorageService.getInstance();
      final token = storage.getToken();

      if (token == null || token.isEmpty) {
        _state = AuthState.unauthenticated;
        _user = null;
        notifyListeners();
        return;
      }

      final response = await _apiClient.get(ApiConfig.me);
      if (response is Map<String, dynamic> && response.containsKey('user')) {
        _user = User.fromJson(response['user']);
        final cachedUserJson = storage.getUserData();
        if (cachedUserJson != null && cachedUserJson.isNotEmpty) {
          try {
            final cached = User.fromJson(jsonDecode(cachedUserJson));
            if (cached.id == _user!.id) {
              _user = User(
                id: _user!.id,
                name: cached.name.isNotEmpty ? cached.name : _user!.name,
                email: _user!.email,
                phone: cached.phone ?? _user!.phone,
                avatar: cached.avatar ?? _user!.avatar,
              );
            }
          } catch (_) {}
        }
        _state = AuthState.authenticated;
      } else {
        _state = AuthState.unauthenticated;
        await storage.clearToken();
      }
    } catch (_) {
      _state = AuthState.unauthenticated;
    }
    notifyListeners();
  }

  Future<bool> login(String email, String password) async {
    _state = AuthState.loading;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.post(
        ApiConfig.login,
        body: {
          'email': email,
          'password': password,
        },
      );

      if (response is Map<String, dynamic>) {
        final token = response['token']?.toString();
        final userData = response['user'] as Map<String, dynamic>?;

        if (token != null && userData != null) {
          final storage = await StorageService.getInstance();
          await storage.saveToken(token);

          _user = User.fromJson(userData);
          _state = AuthState.authenticated;
          notifyListeners();
          return true;
        }
      }
      throw ApiException('Invalid response from server');
    } catch (e) {
      _errorMessage = e is ApiException ? e.message : e.toString().replaceAll('ApiException: ', '');
      _state = AuthState.unauthenticated;
      notifyListeners();
      return false;
    }
  }

  Future<Map<String, dynamic>> register(String name, String email, String password, {String? phone}) async {
    _state = AuthState.loading;
    _errorMessage = null;
    notifyListeners();

    try {
      final body = <String, dynamic>{
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': password,
      };
      if (phone != null && phone.trim().isNotEmpty) {
        body['phone'] = phone.trim();
      }

      final response = await _apiClient.post(
        ApiConfig.register,
        body: body,
      );

      _state = AuthState.unauthenticated;
      notifyListeners();

      if (response is Map<String, dynamic>) {
        return {
          'success': true,
          'message': response['message']?.toString() ?? 'Registration successful',
          'email': response['email']?.toString() ?? email,
          'otp': response['otp']?.toString() ?? '',
        };
      }
      return {'success': false, 'message': 'Registration failed'};
    } catch (e) {
      _errorMessage = e is ApiException ? e.message : e.toString().replaceAll('ApiException: ', '');
      _state = AuthState.unauthenticated;
      notifyListeners();
      return {'success': false, 'message': _errorMessage};
    }
  }

  Future<bool> verifyEmailOtp(String email, String otp) async {
    _state = AuthState.loading;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.post(
        ApiConfig.verifyEmail,
        body: {
          'email': email,
          'otp': otp,
        },
      );

      _state = AuthState.unauthenticated;
      notifyListeners();

      if (response is Map<String, dynamic>) {
        return true;
      }
      return false;
    } catch (e) {
      _errorMessage = e is ApiException ? e.message : e.toString().replaceAll('ApiException: ', '');
      _state = AuthState.unauthenticated;
      notifyListeners();
      return false;
    }
  }

  Future<Map<String, dynamic>> forgotPassword(String email) async {
    _state = AuthState.loading;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.post(
        ApiConfig.forgotPassword,
        body: {'email': email},
      );

      _state = AuthState.unauthenticated;
      notifyListeners();

      if (response is Map<String, dynamic>) {
        return {
          'success': true,
          'message': response['message']?.toString() ?? 'OTP sent to your email',
          'otp': response['otp']?.toString() ?? response['token']?.toString() ?? '',
        };
      }
      return {'success': false, 'message': 'Failed to send OTP'};
    } catch (e) {
      _errorMessage = e.toString();
      _state = AuthState.unauthenticated;
      notifyListeners();
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<bool> verifyResetOtp(String email, String otp) async {
    _state = AuthState.loading;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.post(
        ApiConfig.verifyResetOtp,
        body: {
          'email': email,
          'otp': otp,
        },
      );

      _state = AuthState.unauthenticated;
      notifyListeners();

      if (response is Map<String, dynamic>) {
        return true;
      }
      return false;
    } catch (e) {
      _errorMessage = e is ApiException ? e.message : e.toString().replaceAll('ApiException: ', '');
      _state = AuthState.unauthenticated;
      notifyListeners();
      return false;
    }
  }

  Future<bool> resetPassword(String email, String token, String newPassword) async {
    _state = AuthState.loading;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.post(
        ApiConfig.resetPassword,
        body: {
          'email': email,
          'token': token,
          'password': newPassword,
          'password_confirmation': newPassword,
        },
      );

      _state = AuthState.unauthenticated;
      notifyListeners();

      if (response is Map<String, dynamic>) {
        return true;
      }
      return false;
    } catch (e) {
      _errorMessage = e.toString();
      _state = AuthState.unauthenticated;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    try {
      await _apiClient.post(ApiConfig.logout);
    } catch (_) {}

    final storage = await StorageService.getInstance();
    await storage.clearToken();

    _user = null;
    _state = AuthState.unauthenticated;
    notifyListeners();
  }

  Future<void> updateLocalProfile({required String name, String? phone, String? avatar}) async {
    if (_user != null) {
      _user = User(
        id: _user!.id,
        name: name,
        email: _user!.email,
        phone: phone ?? _user!.phone,
        avatar: avatar ?? _user!.avatar,
      );
      notifyListeners();

      try {
        final storage = await StorageService.getInstance();
        await storage.saveUserData(jsonEncode(_user!.toJson()));
      } catch (_) {}
    }
  }

  Future<bool> updateProfile({
    required String name,
    String? phone,
    File? avatarFile,
    bool removeAvatar = false,
  }) async {
    _errorMessage = null;

    try {
      dynamic response;

      if (avatarFile != null && avatarFile.existsSync()) {
        final fields = <String, String>{
          'name': name,
        };
        if (phone != null && phone.trim().isNotEmpty) {
          fields['phone'] = phone.trim();
        }
        response = await _apiClient.postMultipart(
          ApiConfig.updateProfile,
          file: avatarFile,
          fileParamName: 'avatar',
          fields: fields,
        );
      } else {
        final body = <String, dynamic>{
          'name': name,
          'phone': phone?.trim(),
        };
        if (removeAvatar) {
          body['remove_avatar'] = 1;
        }
        response = await _apiClient.post(
          ApiConfig.updateProfile,
          body: body,
        );
      }

      if (response is Map<String, dynamic> && response.containsKey('user')) {
        _user = User.fromJson(response['user']);
        final storage = await StorageService.getInstance();
        await storage.saveUserData(jsonEncode(_user!.toJson()));
        notifyListeners();
        return true;
      }
      return false;
    } catch (e) {
      _errorMessage = e is ApiException ? e.message : e.toString();
      await updateLocalProfile(
        name: name,
        phone: phone,
        avatar: avatarFile?.path ?? (removeAvatar ? '' : null),
      );
      return false;
    }
  }
}
