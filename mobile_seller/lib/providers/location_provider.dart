import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../core/network/api_client.dart';
import '../core/network/api_config.dart';

class LocationProvider extends ChangeNotifier {
  static const String _prefKeyLocation = 'delivery_location_label';
  static const String _prefKeyAddress = 'delivery_location_address';
  static const String _prefKeyLat = 'delivery_location_lat';
  static const String _prefKeyLng = 'delivery_location_lng';

  final ApiClient _apiClient;

  String _currentLocation = 'Doha, Qatar';
  String _formattedAddress = 'Doha, Qatar';
  double? _latitude;
  double? _longitude;
  bool _isLoading = false;
  bool _isLive = false;
  String? _errorMessage;

  String get currentLocation => _currentLocation;
  String get formattedAddress => _formattedAddress;
  double? get latitude => _latitude;
  double? get longitude => _longitude;
  bool get isLoading => _isLoading;
  bool get isLive => _isLive;
  String? get errorMessage => _errorMessage;

  LocationProvider({ApiClient? apiClient}) : _apiClient = apiClient ?? ApiClient() {
    _loadCachedLocation();
    fetchLiveLocation();
  }

  Future<void> _loadCachedLocation() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final cached = prefs.getString(_prefKeyLocation);
      final address = prefs.getString(_prefKeyAddress);
      final lat = prefs.getDouble(_prefKeyLat);
      final lng = prefs.getDouble(_prefKeyLng);

      if (cached != null && cached.isNotEmpty) {
        _currentLocation = cached;
        if (address != null) _formattedAddress = address;
        _latitude = lat;
        _longitude = lng;
        notifyListeners();
      }
    } catch (_) {}
  }

  /// Fetches live location from Google Maps Geocoding backend
  Future<void> fetchLiveLocation({double? lat, double? lng}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      String url = ApiConfig.locationCurrent;
      if (lat != null && lng != null) {
        url = '$url?lat=$lat&lng=$lng';
      }

      final response = await _apiClient.get(url);

      if (response is Map<String, dynamic> && response['data'] is Map) {
        final data = Map<String, dynamic>.from(response['data'] as Map);
        final label = data['label']?.toString() ?? 'Doha, Qatar';
        final address = data['formatted_address']?.toString() ?? label;
        final resLat = (data['latitude'] is num) ? (data['latitude'] as num).toDouble() : null;
        final resLng = (data['longitude'] is num) ? (data['longitude'] as num).toDouble() : null;

        _currentLocation = label;
        _formattedAddress = address;
        _latitude = resLat;
        _longitude = resLng;
        _isLive = true;

        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(_prefKeyLocation, _currentLocation);
        await prefs.setString(_prefKeyAddress, _formattedAddress);
        if (_latitude != null) await prefs.setDouble(_prefKeyLat, _latitude!);
        if (_longitude != null) await prefs.setDouble(_prefKeyLng, _longitude!);
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Manually select or switch location
  Future<void> selectLocation({
    required String label,
    String? address,
    double? lat,
    double? lng,
  }) async {
    _currentLocation = label;
    _formattedAddress = address ?? label;
    _latitude = lat;
    _longitude = lng;
    _isLive = false;
    notifyListeners();

    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_prefKeyLocation, _currentLocation);
      await prefs.setString(_prefKeyAddress, _formattedAddress);
      if (lat != null) await prefs.setDouble(_prefKeyLat, lat);
      if (lng != null) await prefs.setDouble(_prefKeyLng, lng);
    } catch (_) {}
  }

  /// Search places via Google Maps backend
  Future<List<Map<String, dynamic>>> searchPlaces(String query) async {
    if (query.trim().isEmpty) return [];

    try {
      final url = '${ApiConfig.locationSearch}?query=${Uri.encodeComponent(query)}';
      final response = await _apiClient.get(url);

      if (response is Map<String, dynamic> && response['data'] is List) {
        return (response['data'] as List)
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
      }
    } catch (_) {}

    return [];
  }

  /// Get popular Qatar zones
  Future<List<Map<String, dynamic>>> getPopularZones() async {
    try {
      final response = await _apiClient.get(ApiConfig.locationPopularZones);
      if (response is Map<String, dynamic> && response['data'] is List) {
        return (response['data'] as List)
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList();
      }
    } catch (_) {}

    return const [
      {'label': 'Doha, Qatar', 'city': 'Doha', 'district': 'Central Doha', 'latitude': 25.2854, 'longitude': 51.5310},
      {'label': 'The Pearl, Doha', 'city': 'Doha', 'district': 'The Pearl-Qatar', 'latitude': 25.3713, 'longitude': 51.5513},
      {'label': 'Lusail City, Qatar', 'city': 'Lusail', 'district': 'Marina', 'latitude': 25.4206, 'longitude': 51.4912},
      {'label': 'West Bay, Doha', 'city': 'Doha', 'district': 'Diplomatic Area', 'latitude': 25.3217, 'longitude': 51.5323},
      {'label': 'Al Rayyan, Qatar', 'city': 'Al Rayyan', 'district': 'Education City', 'latitude': 25.3286, 'longitude': 51.4244},
      {'label': 'Al Wakrah, Qatar', 'city': 'Al Wakrah', 'district': 'Old Souq', 'latitude': 25.1768, 'longitude': 51.6034},
    ];
  }
}
