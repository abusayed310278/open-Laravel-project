import 'package:flutter/material.dart';
import 'package:flutter_stripe/flutter_stripe.dart';

class StripeService {
  StripeService._();
  static final StripeService instance = StripeService._();

  static const String defaultPublishableKey =
      'pk_live_51QypHVDzVjBx8VzWZawVtIspqfkYgUClj7kUDn9TUVXFpXFl2YWJZRYLjW1p5GV421bTHhsPUXtXy199xM7fPSek00GOGAroYH';

  bool _initialized = false;

  Future<void> init({String? publishableKey}) async {
    final key = (publishableKey != null && publishableKey.isNotEmpty)
        ? publishableKey
        : defaultPublishableKey;
    Stripe.publishableKey = key;
    Stripe.merchantIdentifier = 'merchant.com.openbox.marketplace';
    await Stripe.instance.applySettings();
    _initialized = true;
  }

  /// Presents the official native Stripe PaymentSheet bottom sheet
  Future<bool> presentOfficialPaymentSheet({
    required String clientSecret,
    String? merchantDisplayName,
    String? customerId,
    String? customerEphemeralKeySecret,
  }) async {
    if (!_initialized) {
      await init();
    }

    try {
      await Stripe.instance.initPaymentSheet(
        paymentSheetParameters: SetupPaymentSheetParameters(
          customFlow: false,
          merchantDisplayName: merchantDisplayName ?? 'Openbox Marketplace',
          paymentIntentClientSecret: clientSecret,
          customerId: customerId,
          customerEphemeralKeySecret: customerEphemeralKeySecret,
          style: ThemeMode.system,
          appearance: const PaymentSheetAppearance(
            colors: PaymentSheetAppearanceColors(
              primary: Color(0xFF6366F1),
            ),
          ),
        ),
      );

      await Stripe.instance.presentPaymentSheet();
      return true;
    } on StripeException catch (e) {
      if (e.error.code == FailureCode.Canceled) {
        debugPrint('Stripe payment sheet was cancelled by user');
        return false;
      }
      debugPrint('Stripe payment error: ${e.error.localizedMessage}');
      rethrow;
    } catch (e) {
      debugPrint('Error presenting Stripe PaymentSheet: $e');
      rethrow;
    }
  }
}
