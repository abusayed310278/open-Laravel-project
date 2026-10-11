import 'dart:ui';
import 'package:flutter/material.dart';

import 'loading_view.dart';

/// Full-screen frosted-glass overlay with the tremendous animated loader
/// for blocking async API tasks (e.g. submitting forms, uploading media).
class LoadingOverlay extends StatelessWidget {
  final bool isLoading;
  final Widget child;
  final String? message;
  final String? subtitle;

  const LoadingOverlay({
    super.key,
    required this.isLoading,
    required this.child,
    this.message,
    this.subtitle,
  });

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        child,
        if (isLoading)
          Positioned.fill(
            child: AbsorbPointer(
              child: BackdropFilter(
                filter: ImageFilter.blur(sigmaX: 4.0, sigmaY: 4.0),
                child: Container(
                  color: Colors.black.withOpacity(0.2),
                  child: LoadingView(
                    message: message ?? 'Processing request...',
                    subtitle: subtitle ?? 'Please wait a moment',
                  ),
                ),
              ),
            ),
          ),
      ],
    );
  }
}
