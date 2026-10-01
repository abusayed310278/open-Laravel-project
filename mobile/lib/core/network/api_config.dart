import 'dart:io';
import 'package:flutter/foundation.dart';

class ApiConfig {
  ApiConfig._();

  /// Primary domain name (handles https://openboxo.com/ or https://openboxo.com)
  static const String domain = 'https://openboxo.com';

  /// Toggle between live server and local development environment:
  /// - `true`: Connects to [domain] (e.g. https://openboxo.com)
  /// - `false`: Connects to local Laragon / Android emulator loopback
  static const bool isProduction = true;

  /// Root host/domain URL without trailing slash (e.g. `https://openboxo.com`)
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


  

  /// Base API endpoint (e.g. `https://openboxo.com/api`)
  static String get baseUrl => '$backendHost/api';

  /// Backend App Icon URL
  static String get appIconUrl => '$backendHost/icon.png';

  /// Backend App Banner URL (admin fallback banner)
  static String get appBannerUrl => '$backendHost/banner%20image.png';

  /// Known legacy or local host variations that may be present in database image/media URLs
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

  /// Normalizes and replaces any legacy/local host with the active [backendHost]
  static String sanitizeUrl(String url) {
    String sanitized = url.trim();
    for (final host in legacyHosts) {
      if (host != backendHost) {
        sanitized = sanitized.replaceAll(host, backendHost);
      }
    }
    return sanitized;
  }

  // Auth endpoints
  static String get login => '$baseUrl/auth/login';
  static String get register => '$baseUrl/auth/register';
  static String get verifyEmail => '$baseUrl/auth/verify-email';
  static String get forgotPassword => '$baseUrl/auth/forgot-password';
  static String get verifyResetOtp => '$baseUrl/auth/verify-reset-otp';
  static String get resetPassword => '$baseUrl/auth/reset-password';
  static String get me => '$baseUrl/auth/me';
  static String get updateProfile => '$baseUrl/auth/profile';
  static String get logout => '$baseUrl/auth/logout';

  // Products & Categories & Sellers
  static String get products => '$baseUrl/products';
  static String productDetail(String slug) => '$baseUrl/products/$slug';
  static String sellerDetail(dynamic sellerId) => '$baseUrl/sellers/$sellerId';
  static String get categories => '$baseUrl/categories';
  static String get brands => '$baseUrl/brands';

  // Cart
  static String get cart => '$baseUrl/cart';
  static String get addToCart => '$baseUrl/cart/add';
  static String updateCart(int productId) => '$baseUrl/cart/update/$productId';
  static String removeFromCart(int productId) => '$baseUrl/cart/remove/$productId';

  // Checkout & Orders
  static String get checkout => '$baseUrl/checkout';
  static String get paymentMethods => '$baseUrl/checkout/payment-methods';
  static String stripeConfirm(dynamic orderId) => '$baseUrl/checkout/stripe-confirm/$orderId';
  static String get orders => '$baseUrl/orders';
  static String orderDetail(int orderId) => '$baseUrl/orders/$orderId';

  // Addresses
  static String get addresses => '$baseUrl/addresses';
  static String addressDetail(dynamic addressId) => '$baseUrl/addresses/$addressId';
  static String setDefaultAddress(dynamic addressId) => '$baseUrl/addresses/$addressId/default';

  // Vendor Orders Actions
  static String submitPaymentProof(int vendorOrderId) =>
      '$baseUrl/vendor-orders/$vendorOrderId/payment-proof';
  static String requestRefund(int vendorOrderId) =>
      '$baseUrl/vendor-orders/$vendorOrderId/refund-request';
  static String get refunds => '$baseUrl/refunds';
}
