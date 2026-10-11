import 'dart:io';
import 'package:flutter/foundation.dart';

class ApiConfig {
  ApiConfig._();

  /// Primary domain name
  static const String domain = 'https://openboxo.com';

  /// Toggle between live server and local development environment:
  /// - `true`: Connects to live [domain] (e.g. https://openboxo.com)
  /// - `false`: Connects to local Laragon / Android emulator loopback
  static const bool isProduction = false;

  /// Root host/domain URL without trailing slash
  static String get backendHost {
    if (isProduction) {
      return domain.replaceAll(RegExp(r'/+$'), '');
    }
    if (kIsWeb) {
      return 'http://localhost/open/public';
    }
    if (Platform.isAndroid) {
      return 'http://10.0.2.2/open/public';
    }
    return 'http://localhost/open/public';
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
    'http://open.test/open/public',
    'https://open.test/open/public',
    'http://open.test',
    'https://open.test',
    'http://openbox.test',
    'https://openbox.test',
    'http://openboxo.com',
    'https://openboxo.com',
    'http://localhost',
    'http://127.0.0.1',
  ];

  /// Normalizes and replaces local/remote host in image URLs with the active [backendHost]
  static String sanitizeUrl(String url) {
    String sanitized = url.trim();
    for (final host in legacyHosts) {
      if (host != backendHost) {
        sanitized = sanitized.replaceAll(host, backendHost);
      }
    }
    return sanitized;
  }
}
