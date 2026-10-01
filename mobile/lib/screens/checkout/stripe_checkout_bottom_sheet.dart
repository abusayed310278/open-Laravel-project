import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

import '../../core/network/api_config.dart';
import '../../core/theme/app_spacing.dart';

class StripeCheckoutBottomSheet extends StatefulWidget {
  const StripeCheckoutBottomSheet({
    super.key,
    required this.paymentUrl,
    required this.orderId,
    this.title = 'Stripe Secure Checkout',
  });

  final String paymentUrl;
  final String orderId;
  final String title;

  static Future<bool?> show({
    required BuildContext context,
    required String paymentUrl,
    required String orderId,
    String title = 'Stripe Secure Checkout',
  }) {
    return showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      isDismissible: true,
      enableDrag: true,
      builder: (ctx) => StripeCheckoutBottomSheet(
        paymentUrl: paymentUrl,
        orderId: orderId,
        title: title,
      ),
    );
  }

  @override
  State<StripeCheckoutBottomSheet> createState() =>
      _StripeCheckoutBottomSheetState();
}

class _StripeCheckoutBottomSheetState extends State<StripeCheckoutBottomSheet> {
  late final WebViewController _controller;
  double _progress = 0.0;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _initWebView();
  }

  void _initWebView() {
    String targetUrl = widget.paymentUrl.trim();

    if (!kIsWeb && Platform.isAndroid) {
      targetUrl = ApiConfig.sanitizeUrl(targetUrl);
    }

    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(const Color(0xFF0F172A))
      ..setNavigationDelegate(
        NavigationDelegate(
          onProgress: (progress) {
            if (mounted) {
              setState(() {
                _progress = progress / 100.0;
                _isLoading = progress < 100;
              });
            }
          },
          onPageStarted: (url) {
            _checkRedirectStatus(url);
          },
          onPageFinished: (url) {
            if (mounted) {
              setState(() => _isLoading = false);
            }
            _checkRedirectStatus(url);
          },
          onNavigationRequest: (request) {
            final handled = _checkRedirectStatus(request.url);
            if (handled) {
              return NavigationDecision.prevent;
            }
            return NavigationDecision.navigate;
          },
        ),
      )
      ..loadRequest(Uri.parse(targetUrl));
  }

  bool _checkRedirectStatus(String url) {
    final lower = url.toLowerCase();

    // Check for success redirects
    if (lower.contains('stripe-success') ||
        lower.contains('paypal-success') ||
        lower.contains('order-confirmation') ||
        lower.contains('confirmation') ||
        lower.contains('orders.confirmation') ||
        lower.contains('status=order%20placed') ||
        lower.contains('status=payment%20completed')) {
      if (mounted) {
        Navigator.of(context).pop(true);
      }
      return true;
    }

    // Check for cancel / failure redirects
    if (lower.contains('stripe-cancel') ||
        lower.contains('paypal-cancel') ||
        lower.contains('checkout-cancel') ||
        lower.contains('cancel=true')) {
      if (mounted) {
        Navigator.of(context).pop(false);
      }
      return true;
    }

    return false;
  }

  @override
  Widget build(BuildContext context) {
    final sheetHeight = MediaQuery.of(context).size.height * 0.90;

    return Container(
      height: sheetHeight,
      decoration: const BoxDecoration(
        color: Color(0xFF0F172A),
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        boxShadow: [
          BoxShadow(
            color: Colors.black54,
            blurRadius: 20,
            spreadRadius: 2,
            offset: Offset(0, -3),
          ),
        ],
      ),
      child: Column(
        children: [
          // Drag handle
          Center(
            child: Container(
              margin: const EdgeInsets.only(top: 10, bottom: 6),
              width: 40,
              height: 4.5,
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.25),
                borderRadius: BorderRadius.circular(AppRadius.pill),
              ),
            ),
          ),

          // Header
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            child: Row(
              children: [
                Container(
                  width: 22,
                  height: 22,
                  decoration: const BoxDecoration(
                    color: Color(0xFF6366F1),
                    shape: BoxShape.circle,
                  ),
                  child: const Center(
                    child: Text(
                      'S',
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        fontSize: 13,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                const Icon(Icons.lock_rounded, size: 14, color: Color(0xFF34D399)),
                const SizedBox(width: 4),
                Text(
                  widget.title,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    color: Colors.white,
                  ),
                ),
                const Spacer(),
                IconButton(
                  visualDensity: VisualDensity.compact,
                  icon: const Icon(Icons.close_rounded, color: Colors.white70, size: 22),
                  onPressed: () => Navigator.of(context).pop(false),
                ),
              ],
            ),
          ),

          // Progress indicator
          if (_isLoading)
            LinearProgressIndicator(
              value: _progress > 0 ? _progress : null,
              backgroundColor: Colors.transparent,
              valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFF6366F1)),
              minHeight: 2.5,
            )
          else
            const Divider(height: 1, color: Color(0xFF1E293B)),

          // WebView inside BottomSheet
          Expanded(
            child: ClipRRect(
              child: WebViewWidget(controller: _controller),
            ),
          ),
        ],
      ),
    );
  }
}
