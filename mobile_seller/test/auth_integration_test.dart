import 'dart:convert';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:mobile_seller/core/network/api_client.dart';
import 'package:mobile_seller/providers/seller_auth_provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() {
    SharedPreferences.setMockInitialValues({});
  });

  group('SellerAuthProvider API Integration Tests', () {
    test('login succeeds when API returns token and user data', () async {
      final mockClient = MockClient((request) async {
        if (request.url.path.endsWith('/auth/login')) {
          return http.Response(
            jsonEncode({
              'message': 'Login successful',
              'token': 'test_token_123',
              'user': {
                'id': 2,
                'name': 'Sarah Ahmed',
                'email': 'sarah.ahmed@example.com',
                'role': 'customer',
                'role_label': 'Personal (Buy & Sell)',
                'store_name': 'Sarah Ahmed',
                'is_store_active': true,
                'is_kyc_approved': false,
                'wallet': {
                  'available_balance': 0.0,
                  'pending_balance': 0.0,
                  'total_earned': 0.0,
                  'currency': 'QAR',
                },
              },
            }),
            200,
            headers: {'content-type': 'application/json'},
          );
        }
        return http.Response('Not Found', 404);
      });

      final apiClient = ApiClient(client: mockClient);
      final provider = SellerAuthProvider(apiClient: apiClient);

      final success = await provider.login('sarah.ahmed@example.com', 'password');

      expect(success, isTrue);
      expect(provider.isAuthenticated, isTrue);
      expect(provider.user?.email, equals('sarah.ahmed@example.com'));
      expect(provider.errorMessage, isNull);
    });

    test('login fails when invalid credentials provided', () async {
      final mockClient = MockClient((request) async {
        return http.Response(
          jsonEncode({
            'message': 'The provided credentials do not match our records.',
            'errors': {
              'email': ['The provided credentials do not match our records.'],
            },
          }),
          422,
          headers: {'content-type': 'application/json'},
        );
      });

      final apiClient = ApiClient(client: mockClient);
      final provider = SellerAuthProvider(apiClient: apiClient);

      final success = await provider.login('wrong@example.com', 'bad_password');

      expect(success, isFalse);
      expect(provider.isAuthenticated, isFalse);
      expect(provider.errorMessage, contains('The provided credentials do not match our records'));
    });

    test('register succeeds and stores user token', () async {
      final mockClient = MockClient((request) async {
        if (request.url.path.endsWith('/auth/register')) {
          final body = jsonDecode(request.body);
          expect(body['email'], equals('new_seller@example.com'));
          expect(body['business_name'], equals('New Seller Store'));

          return http.Response(
            jsonEncode({
              'message': 'Registration successful',
              'token': 'new_token_456',
              'user': {
                'id': 10,
                'name': 'New Seller',
                'email': 'new_seller@example.com',
                'role': 'business',
                'role_label': 'Store Owner',
                'store_name': 'New Seller Store',
                'is_store_active': true,
                'is_kyc_approved': false,
                'wallet': {
                  'available_balance': 0.0,
                  'pending_balance': 0.0,
                  'total_earned': 0.0,
                  'currency': 'QAR',
                },
              },
            }),
            201,
            headers: {'content-type': 'application/json'},
          );
        }
        return http.Response('Not Found', 404);
      });

      final apiClient = ApiClient(client: mockClient);
      final provider = SellerAuthProvider(apiClient: apiClient);

      final success = await provider.register(
        name: 'New Seller',
        email: 'new_seller@example.com',
        phone: '+974 5555 1234',
        storeName: 'New Seller Store',
        role: 'business',
        password: 'password123',
        passwordConfirmation: 'password123',
      );

      expect(success, isTrue);
      expect(provider.isAuthenticated, isTrue);
      expect(provider.user?.name, equals('New Seller'));
      expect(provider.user?.storeName, equals('New Seller Store'));
    });

    test('forgotPassword sends OTP email successfully', () async {
      final mockClient = MockClient((request) async {
        if (request.url.path.endsWith('/auth/forgot-password')) {
          final body = jsonDecode(request.body);
          expect(body['email'], equals('sarah.ahmed@example.com'));

          return http.Response(
            jsonEncode({
              'message': 'Password reset OTP has been sent to your email address.',
              'email': 'sarah.ahmed@example.com',
              'otp': '654321',
            }),
            200,
            headers: {'content-type': 'application/json'},
          );
        }
        return http.Response('Not Found', 404);
      });

      final apiClient = ApiClient(client: mockClient);
      final provider = SellerAuthProvider(apiClient: apiClient);

      final result = await provider.forgotPassword('sarah.ahmed@example.com');

      expect(result, isNotNull);
      expect(result?['otp'], equals('654321'));
      expect(provider.errorMessage, isNull);
    });

    test('verifyResetOtp validates code successfully', () async {
      final mockClient = MockClient((request) async {
        if (request.url.path.endsWith('/auth/verify-reset-otp')) {
          final body = jsonDecode(request.body);
          expect(body['email'], equals('sarah.ahmed@example.com'));
          expect(body['otp'], equals('654321'));

          return http.Response(
            jsonEncode({
              'message': 'OTP verified successfully. Please enter your new password.',
              'token': '654321',
            }),
            200,
            headers: {'content-type': 'application/json'},
          );
        }
        return http.Response('Not Found', 404);
      });

      final apiClient = ApiClient(client: mockClient);
      final provider = SellerAuthProvider(apiClient: apiClient);

      final success = await provider.verifyResetOtp(
        email: 'sarah.ahmed@example.com',
        otp: '654321',
      );

      expect(success, isTrue);
      expect(provider.errorMessage, isNull);
    });

    test('resetPassword sets new password successfully', () async {
      final mockClient = MockClient((request) async {
        if (request.url.path.endsWith('/auth/reset-password')) {
          final body = jsonDecode(request.body);
          expect(body['email'], equals('sarah.ahmed@example.com'));
          expect(body['token'], equals('654321'));
          expect(body['password'], equals('new_secret_password'));
          expect(body['password_confirmation'], equals('new_secret_password'));

          return http.Response(
            jsonEncode({
              'message': 'Password has been reset successfully. Please sign in.',
            }),
            200,
            headers: {'content-type': 'application/json'},
          );
        }
        return http.Response('Not Found', 404);
      });

      final apiClient = ApiClient(client: mockClient);
      final provider = SellerAuthProvider(apiClient: apiClient);

      final success = await provider.resetPassword(
        email: 'sarah.ahmed@example.com',
        otp: '654321',
        password: 'new_secret_password',
        passwordConfirmation: 'new_secret_password',
      );

      expect(success, isTrue);
      expect(provider.errorMessage, isNull);
    });

    test('socialLogin succeeds with Google provider', () async {
      final mockClient = MockClient((request) async {
        if (request.url.path.endsWith('/auth/social-login')) {
          final body = jsonDecode(request.body);
          expect(body['provider'], equals('google'));
          expect(body['email'], equals('google.seller@example.com'));

          return http.Response(
            jsonEncode({
              'message': 'Google sign-in successful',
              'token': 'google_token_789',
              'user': {
                'id': 15,
                'name': 'Google Seller',
                'email': 'google.seller@example.com',
                'role': 'business',
                'role_label': 'Store Owner',
                'store_name': 'Google Tech Store',
                'is_store_active': true,
                'is_kyc_approved': false,
                'wallet': {
                  'available_balance': 0.0,
                  'pending_balance': 0.0,
                  'total_earned': 0.0,
                  'currency': 'QAR',
                },
              },
            }),
            200,
            headers: {'content-type': 'application/json'},
          );
        }
        return http.Response('Not Found', 404);
      });

      final apiClient = ApiClient(client: mockClient);
      final provider = SellerAuthProvider(apiClient: apiClient);

      final success = await provider.socialLogin(
        provider: 'google',
        email: 'google.seller@example.com',
        name: 'Google Seller',
        providerId: 'google_id_123',
        storeName: 'Google Tech Store',
        role: 'business',
      );

      expect(success, isTrue);
      expect(provider.isAuthenticated, isTrue);
      expect(provider.user?.name, equals('Google Seller'));
      expect(provider.user?.email, equals('google.seller@example.com'));
    });

    test('socialLogin succeeds with Facebook provider', () async {
      final mockClient = MockClient((request) async {
        if (request.url.path.endsWith('/auth/social-login')) {
          final body = jsonDecode(request.body);
          expect(body['provider'], equals('facebook'));
          expect(body['email'], equals('fb.seller@example.com'));

          return http.Response(
            jsonEncode({
              'message': 'Facebook sign-in successful',
              'token': 'facebook_token_999',
              'user': {
                'id': 16,
                'name': 'Facebook Seller',
                'email': 'fb.seller@example.com',
                'role': 'customer',
                'role_label': 'Personal (Buy & Sell)',
                'store_name': 'Facebook Seller',
                'is_store_active': true,
                'is_kyc_approved': false,
                'wallet': {
                  'available_balance': 0.0,
                  'pending_balance': 0.0,
                  'total_earned': 0.0,
                  'currency': 'QAR',
                },
              },
            }),
            200,
            headers: {'content-type': 'application/json'},
          );
        }
        return http.Response('Not Found', 404);
      });

      final apiClient = ApiClient(client: mockClient);
      final provider = SellerAuthProvider(apiClient: apiClient);

      final success = await provider.socialLogin(
        provider: 'facebook',
        email: 'fb.seller@example.com',
        name: 'Facebook Seller',
        providerId: 'fb_id_456',
        role: 'customer',
      );

      expect(success, isTrue);
      expect(provider.isAuthenticated, isTrue);
      expect(provider.user?.name, equals('Facebook Seller'));
      expect(provider.user?.email, equals('fb.seller@example.com'));
    });
  });
}
