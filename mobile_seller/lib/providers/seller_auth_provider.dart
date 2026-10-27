import 'dart:convert';
import 'package:flutter/foundation.dart';
import '../core/network/api_client.dart';
import '../core/network/storage_service.dart';
import '../data/models/seller_user.dart';

enum AuthState { uninitialized, authenticated, unauthenticated, loading }

class SellerAuthProvider extends ChangeNotifier {
  final ApiClient _apiClient;

  AuthState _state = AuthState.uninitialized;
  SellerUser? _user;
  String? _errorMessage;

  AuthState get state => _state;
  SellerUser? get user => _user;
  bool get isAuthenticated => _state == AuthState.authenticated;
  String? get errorMessage => _errorMessage;

  SellerAuthProvider({ApiClient? apiClient}) : _apiClient = apiClient ?? ApiClient();

  /// Check whether seller is already logged in on app startup
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

      final cachedJson = storage.getSellerData();
      if (cachedJson != null && cachedJson.isNotEmpty) {
        try {
          _user = SellerUser.fromJson(jsonDecode(cachedJson));
        } catch (_) {}
      }

      final response = await _apiClient.get('/auth/me');
      if (response is Map<String, dynamic> && response.containsKey('user')) {
        _user = SellerUser.fromJson(response['user'] as Map<String, dynamic>);
        await storage.saveSellerData(jsonEncode(_user!.toJson()));
        _state = AuthState.authenticated;
      } else {
        _state = AuthState.unauthenticated;
        await storage.clearToken();
      }
    } catch (_) {
      // If server unreachable but token exists and cached user is present, keep authenticated offline
      if (_user != null) {
        _state = AuthState.authenticated;
      } else {
        _state = AuthState.unauthenticated;
      }
    } finally {
      notifyListeners();
    }
  }

  /// Sign in seller with email and password
  Future<bool> login(String email, String password) async {
    _state = AuthState.loading;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.post(
        '/auth/login',
        body: {
          'email': email.trim(),
          'password': password,
        },
      );

      if (response is Map<String, dynamic> && response.containsKey('token')) {
        final token = response['token'].toString();
        final userData = response['user'] as Map<String, dynamic>;

        final storage = await StorageService.getInstance();
        await storage.saveToken(token);

        _user = SellerUser.fromJson(userData);
        await storage.saveSellerData(jsonEncode(_user!.toJson()));

        _state = AuthState.authenticated;
        notifyListeners();
        return true;
      } else {
        throw ApiException('Invalid response received from server.');
      }
    } catch (e) {
      _errorMessage = e.toString();
      _state = AuthState.unauthenticated;
      notifyListeners();
      return false;
    }
  }

  /// Register a new account (Seller, Store Owner, or Customer) and sign in on success
  Future<bool> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? storeName,
    String? phone,
    String? role,
  }) async {
    _state = AuthState.loading;
    _errorMessage = null;
    notifyListeners();

    try {
      final businessName = (storeName != null && storeName.trim().isNotEmpty)
          ? storeName.trim()
          : name.trim();

      final response = await _apiClient.post(
        '/auth/register',
        body: {
          'name': name.trim(),
          'email': email.trim(),
          'phone': phone?.trim().isEmpty ?? true ? null : phone!.trim(),
          'business_name': businessName,
          'store_name': businessName,
          'role': role ?? 'business',
          'password': password,
          'password_confirmation': passwordConfirmation,
        },
      );

      if (response is Map<String, dynamic> && response.containsKey('token')) {
        final token = response['token'].toString();
        final userData = response['user'] as Map<String, dynamic>;

        final storage = await StorageService.getInstance();
        await storage.saveToken(token);

        _user = SellerUser.fromJson(userData);
        await storage.saveSellerData(jsonEncode(_user!.toJson()));

        _state = AuthState.authenticated;
        notifyListeners();
        return true;
      } else {
        throw ApiException('Invalid response received from server.');
      }
    } catch (e) {
      _errorMessage = e.toString();
      _state = AuthState.unauthenticated;
      notifyListeners();
      return false;
    }
  }

  /// Request password reset OTP email
  Future<Map<String, dynamic>?> forgotPassword(String email) async {
    _state = AuthState.loading;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.post(
        '/auth/forgot-password',
        body: {
          'email': email.trim(),
        },
      );

      _state = AuthState.unauthenticated;
      notifyListeners();

      if (response is Map<String, dynamic>) {
        return response;
      }
      return {'message': 'OTP sent successfully.'};
    } catch (e) {
      _errorMessage = e.toString();
      _state = AuthState.unauthenticated;
      notifyListeners();
      return null;
    }
  }

  /// Verify password reset OTP code
  Future<bool> verifyResetOtp({
    required String email,
    required String otp,
  }) async {
    _state = AuthState.loading;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.post(
        '/auth/verify-reset-otp',
        body: {
          'email': email.trim(),
          'otp': otp.trim(),
        },
      );

      _state = AuthState.unauthenticated;
      notifyListeners();

      return response is Map<String, dynamic>;
    } catch (e) {
      _errorMessage = e.toString();
      _state = AuthState.unauthenticated;
      notifyListeners();
      return false;
    }
  }

  /// Reset password using verified OTP code
  Future<bool> resetPassword({
    required String email,
    required String otp,
    required String password,
    required String passwordConfirmation,
  }) async {
    _state = AuthState.loading;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.post(
        '/auth/reset-password',
        body: {
          'email': email.trim(),
          'token': otp.trim(),
          'password': password,
          'password_confirmation': passwordConfirmation,
        },
      );

      _state = AuthState.unauthenticated;
      notifyListeners();

      return response is Map<String, dynamic>;
    } catch (e) {
      _errorMessage = e.toString();
      _state = AuthState.unauthenticated;
      notifyListeners();
      return false;
    }
  }

  /// Authenticate or register using Google or Facebook
  Future<bool> socialLogin({
    required String provider,
    String? accessToken,
    String? email,
    String? name,
    String? providerId,
    String? avatar,
    String? role,
    String? storeName,
  }) async {
    _state = AuthState.loading;
    _errorMessage = null;
    notifyListeners();

    try {
      final body = <String, dynamic>{
        'provider': provider,
      };
      if (accessToken != null && accessToken.isNotEmpty) {
        body['access_token'] = accessToken;
      }
      if (email != null && email.isNotEmpty) {
        body['email'] = email;
      }
      if (name != null && name.isNotEmpty) {
        body['name'] = name;
      }
      if (providerId != null && providerId.isNotEmpty) {
        body['provider_id'] = providerId;
      }
      if (avatar != null && avatar.isNotEmpty) {
        body['avatar'] = avatar;
      }
      if (role != null && role.isNotEmpty) {
        body['role'] = role;
      }
      if (storeName != null && storeName.isNotEmpty) {
        body['store_name'] = storeName;
        body['business_name'] = storeName;
      }

      final response = await _apiClient.post(
        '/auth/social-login',
        body: body,
      );

      if (response is Map<String, dynamic> && response.containsKey('token')) {
        final token = response['token'].toString();
        final userData = response['user'] as Map<String, dynamic>;

        final storage = await StorageService.getInstance();
        await storage.saveToken(token);

        _user = SellerUser.fromJson(userData);
        await storage.saveSellerData(jsonEncode(_user!.toJson()));

        _state = AuthState.authenticated;
        notifyListeners();
        return true;
      } else {
        throw ApiException('Invalid response received from server.');
      }
    } catch (e) {
      _errorMessage = e.toString();
      _state = AuthState.unauthenticated;
      notifyListeners();
      return false;
    }
  }

  /// Clear any active error message
  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }

  /// Logout seller and clear session
  Future<void> logout() async {
    try {
      await _apiClient.post('/auth/logout');
    } catch (_) {}

    final storage = await StorageService.getInstance();
    await storage.clearToken();

    _user = null;
    _state = AuthState.unauthenticated;
    _errorMessage = null;
    notifyListeners();
  }

  /// Refresh seller profile data
  Future<void> refreshProfile() async {
    try {
      final response = await _apiClient.get('/auth/me');
      if (response is Map<String, dynamic> && response.containsKey('user')) {
        _user = SellerUser.fromJson(response['user'] as Map<String, dynamic>);
        final storage = await StorageService.getInstance();
        await storage.saveSellerData(jsonEncode(_user!.toJson()));
        notifyListeners();
      }
    } catch (_) {}
  }

  /// Apply an already-fetched user (e.g. from a save/update response) without
  /// a round-trip to the server, so callers aren't left showing stale data if
  /// a follow-up [refreshProfile] call fails.
  Future<void> setUser(SellerUser user) async {
    _user = user;
    try {
      final storage = await StorageService.getInstance();
      await storage.saveSellerData(jsonEncode(_user!.toJson()));
    } catch (_) {}
    notifyListeners();
  }
}
