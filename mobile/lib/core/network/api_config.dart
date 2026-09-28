import 'dart:io';
import 'package:flutter/foundation.dart';

class ApiConfig {
  ApiConfig._();

  /// Laragon local web server endpoint:
  /// - `http://10.0.2.2/open/public/api` for Android Emulator
  /// - `http://localhost/open/public/api` for Windows / Web / Desktop
  static String get baseUrl {
    if (kIsWeb) {
      return 'http://localhost/open/public/api';
    }
    if (Platform.isAndroid) {
      return 'http://10.0.2.2/open/public/api';
    }
    return 'http://localhost/open/public/api';
  }

  /// Backend App Icon URL from Laragon backend public directory
  static String get appIconUrl => '${baseUrl.replaceAll('/api', '')}/icon.png';

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

  // Products & Categories
  static String get products => '$baseUrl/products';
  static String productDetail(String slug) => '$baseUrl/products/$slug';
  static String get categories => '$baseUrl/categories';
  static String get brands => '$baseUrl/brands';

  // Cart
  static String get cart => '$baseUrl/cart';
  static String get addToCart => '$baseUrl/cart/add';
  static String updateCart(int productId) => '$baseUrl/cart/update/$productId';
  static String removeFromCart(int productId) => '$baseUrl/cart/remove/$productId';

  // Checkout & Orders
  static String get checkout => '$baseUrl/checkout';
  static String get orders => '$baseUrl/orders';
  static String orderDetail(int orderId) => '$baseUrl/orders/$orderId';

  // Vendor Orders Actions
  static String submitPaymentProof(int vendorOrderId) =>
      '$baseUrl/vendor-orders/$vendorOrderId/payment-proof';
  static String requestRefund(int vendorOrderId) =>
      '$baseUrl/vendor-orders/$vendorOrderId/refund-request';
  static String get refunds => '$baseUrl/refunds';
}
