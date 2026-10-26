import 'dart:io';
import 'package:flutter/foundation.dart';

class ApiConfig {
  ApiConfig._();

  /// Live production domain
  static const String liveDomain = 'https://openboxo.com';

  /// Primary development domain (Laragon virtual host: http://open.test)
  static const String devDomain = 'http://open.test';

  /// Backwards-compatible domain alias
  static const String domain = liveDomain;

  /// Toggle between live server and local development environment:
  /// - `true`: Connects to live production server ([liveDomain])
  /// - `false`: Connects to local development domain ([devDomain])
  static const bool isProduction = false;

  /// When [isProduction] is false:
  /// - `true`: Directly uses [devDomain] (http://open.test) for Windows Desktop, Web, iOS, or DNS-configured Emulators.
  /// - `false`: Falls back to direct IP loopback (`10.0.2.2/open/public`) on Android emulators if `.test` DNS is not resolved.
  static const bool useDevDomain = false;

  /// Root host/domain URL without trailing slash
  static String get backendHost {
    if (isProduction) {
      return liveDomain.replaceAll(RegExp(r'/+$'), '');
    }
    if (kIsWeb) {
      return devDomain.replaceAll(RegExp(r'/+$'), '');
    }
    if (Platform.isAndroid) {
      return 'http://10.0.2.2/open/public';
    }
    if (useDevDomain) {
      return devDomain.replaceAll(RegExp(r'/+$'), '');
    }
    return devDomain.replaceAll(RegExp(r'/+$'), '');
  }

  /// Base API endpoint for Seller app (points to `/api/seller`)
  static String get baseUrl => '$backendHost/api/seller';

  /// General API endpoint (points to `/api`)
  static String get rootApiUrl => '$backendHost/api';

  /// Known legacy or local host variations that may be present in database image URLs
  static const List<String> legacyHosts = [
    'http://localhost:8000',
    'https://localhost:8000',
    'http://127.0.0.1:8000',
    'https://127.0.0.1:8000',
    'http://localhost/open/public',
    'https://localhost/open/public',
    'http://127.0.0.1/open/public',
    'https://127.0.0.1/open/public',
    'http://10.0.2.2/open/public',
    'https://10.0.2.2/open/public',
    'http://open.test/open/public',
    'https://open.test/open/public',
    'http://open.test',
    'https://open.test',
    'http://openbox.test',
    'https://openbox.test',
    'http://openboxo.com',
    'https://openboxo.com',
    'http://10.0.2.2',
    'https://10.0.2.2',
    'http://localhost',
    'http://127.0.0.1',
  ];

  /// Normalizes and replaces local/remote host in image URLs with the active [backendHost]
  static String sanitizeUrl(String url) {
    String sanitized = url.trim();
    if (sanitized.isEmpty) return sanitized;

    for (final host in legacyHosts) {
      if (host != backendHost) {
        sanitized = sanitized.replaceAll(host, backendHost);
      }
    }

    if (!sanitized.startsWith('http://') && !sanitized.startsWith('https://')) {
      final cleanPath = sanitized.startsWith('/') ? sanitized : '/$sanitized';
      sanitized = '$backendHost$cleanPath';
    }

    return sanitized;
  }

  /// App Icon & Banner
  static String get appIconUrl => '$backendHost/icon.png';
  static String get appBannerUrl => '$backendHost/banner%20image.png';

  /// Products, Categories, Brands & Sellers public endpoints
  static String get products => '$rootApiUrl/products';
  static String productDetail(String slug) => '$rootApiUrl/products/$slug';
  static String sellerDetail(dynamic sellerId) => '$rootApiUrl/sellers/$sellerId';
  static String get categories => '$rootApiUrl/categories';
  static String get brands => '$rootApiUrl/brands';

  /// Location endpoints (Google Maps Geocoding backend)
  static String get locationCurrent => '$rootApiUrl/location/current';
  static String get locationReverseGeocode => '$rootApiUrl/location/reverse-geocode';
  static String get locationSearch => '$rootApiUrl/location/search';
  static String get locationPopularZones => '$rootApiUrl/location/popular-zones';
}

