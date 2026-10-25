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

  /// Register a new Store Owner account and sign in on success
  Future<bool> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    required String storeName,
    String? phone,
  }) async {
    _state = AuthState.loading;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiClient.post(
        '/auth/register',
        body: {
          'name': name.trim(),
          'email': email.trim(),
          'phone': phone?.trim().isEmpty ?? true ? null : phone!.trim(),
          'business_name': storeName.trim(),
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
}
