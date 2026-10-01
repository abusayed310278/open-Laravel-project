import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

import '../../core/network/api_config.dart';
import '../../core/theme/app_colors.dart';

class PaymentWebViewScreen extends StatefulWidget {
  const PaymentWebViewScreen({
    super.key,
    required this.paymentUrl,
    required this.orderId,
    this.title = 'Secure Payment',
  });

  final String paymentUrl;
  final String orderId;
  final String title;

  @override
  State<PaymentWebViewScreen> createState() => _PaymentWebViewScreenState();
}

class _PaymentWebViewScreenState extends State<PaymentWebViewScreen> {
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

    // Normalize local/legacy hostnames to active backend host
    if (!kIsWeb && Platform.isAndroid) {
      targetUrl = ApiConfig.sanitizeUrl(targetUrl);
    }

    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(Colors.white)
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

  Future<bool> _onWillPop() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Cancel Payment?'),
        content: const Text(
          'Are you sure you want to cancel the payment? Your order will remain pending.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: const Text('Continue Payment'),
          ),
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(true),
            style: TextButton.styleFrom(foregroundColor: AppColors.danger),
            child: const Text('Cancel Payment'),
          ),
        ],
      ),
    );

    return confirmed ?? false;
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) async {
        if (didPop) return;
        final shouldPop = await _onWillPop();
        if (!context.mounted) return;
        if (shouldPop) {
          Navigator.of(context).pop(false);
        }
      },
      child: Scaffold(
        appBar: AppBar(
          title: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.lock_rounded, size: 16, color: AppColors.success),
              const SizedBox(width: 6),
              Text(
                widget.title,
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
              ),
            ],
          ),
          centerTitle: true,
          elevation: 1,
          actions: [
            IconButton(
              icon: const Icon(Icons.refresh_rounded),
              onPressed: () => _controller.reload(),
              tooltip: 'Reload',
            ),
          ],
          bottom: _isLoading
              ? PreferredSize(
                  preferredSize: const Size.fromHeight(2),
                  child: LinearProgressIndicator(
                    value: _progress > 0 ? _progress : null,
                    backgroundColor: Colors.transparent,
                    valueColor: const AlwaysStoppedAnimation<Color>(AppColors.brand500),
                  ),
                )
              : null,
        ),
        body: WebViewWidget(controller: _controller),
      ),
    );
  }
}
