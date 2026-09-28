import 'package:shared_preferences/shared_preferences.dart';

class StorageService {
  static const String _keyToken = 'auth_token';
  static const String _keyUserJson = 'user_data';

  static StorageService? _instance;
  static SharedPreferences? _prefs;

  StorageService._();

  static Future<StorageService> getInstance() async {
    _instance ??= StorageService._();
    if (_prefs == null) {
      try {
        _prefs = await SharedPreferences.getInstance();
      } catch (e) {
        // Fallback gracefully if native plugin channel is unavailable
      }
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
      await prefs.remove(_keyUserJson);
      return await prefs.remove(_keyToken);
    } catch (_) {
      return false;
    }
  }

  Future<bool> saveUserData(String userJson) async {
    try {
      final prefs = _prefs ?? await SharedPreferences.getInstance();
      return await prefs.setString(_keyUserJson, userJson);
    } catch (_) {
      return false;
    }
  }

  String? getUserData() {
    try {
      return _prefs?.getString(_keyUserJson);
    } catch (_) {
      return null;
    }
  }
}
