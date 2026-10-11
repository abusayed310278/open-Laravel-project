import 'package:shared_preferences/shared_preferences.dart';

class StorageService {
  static const String _keyToken = 'seller_auth_token';
  static const String _keySellerJson = 'seller_data';

  static StorageService? _instance;
  static SharedPreferences? _prefs;

  StorageService._();

  static Future<StorageService> getInstance() async {
    _instance ??= StorageService._();
    if (_prefs == null) {
      try {
        _prefs = await SharedPreferences.getInstance();
      } catch (_) {}
    }
    return _instance!;
  }

  Future<bool> saveToken(String token) async {
    try {
      final prefs = _prefs ?? await SharedPreferences.getInstance();
      return await prefs.setString(_keyToken, token);
    } catch (_) {
      return false;
    }
  }

  String? getToken() {
    try {
      return _prefs?.getString(_keyToken);
    } catch (_) {
      return null;
    }
  }

  Future<bool> clearToken() async {
    try {
      final prefs = _prefs ?? await SharedPreferences.getInstance();
      await prefs.remove(_keySellerJson);
      return await prefs.remove(_keyToken);
    } catch (_) {
      return false;
    }
  }

  Future<bool> saveSellerData(String sellerJson) async {
    try {
      final prefs = _prefs ?? await SharedPreferences.getInstance();
      return await prefs.setString(_keySellerJson, sellerJson);
    } catch (_) {
      return false;
    }
  }

  String? getSellerData() {
    try {
      return _prefs?.getString(_keySellerJson);
    } catch (_) {
      return null;
    }
  }

  Future<bool> clearAll() async {
    try {
      final prefs = _prefs ?? await SharedPreferences.getInstance();
      return await prefs.clear();
    } catch (_) {
      return false;
    }
  }
}
