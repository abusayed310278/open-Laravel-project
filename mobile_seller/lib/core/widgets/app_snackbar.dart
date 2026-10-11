import 'dart:ui';
import 'package:flutter/material.dart';
import '../theme/app_colors.dart';
import '../../main.dart';

enum SnackBarType { success, error, warning, info }

class AppSnackBar {
  AppSnackBar._();

  static OverlayEntry? _activeEntry;
  static _TopSnackBarWidgetState? _activeWidgetState;

  /// Display a top-notch floating snackbar at the top of the screen.
  static void show(
    BuildContext? context,
    String message, {
    String? title,
    SnackBarType type = SnackBarType.info,
    Duration duration = const Duration(milliseconds: 3200),
  }) {
    final overlayState = _resolveOverlay(context);
    if (overlayState == null) return;

    // Dismiss existing snackbar cleanly
    _dismissCurrent();

    late OverlayEntry entry;
    entry = OverlayEntry(
      builder: (ctx) => _TopSnackBarWidget(
        message: message,
        title: title,
        type: type,
        duration: duration,
        onDismissed: () {
          if (_activeEntry == entry) {
            _activeEntry?.remove();
            _activeEntry = null;
            _activeWidgetState = null;
          }
        },
        onStateCreated: (state) {
          _activeWidgetState = state;
        },
      ),
    );

    _activeEntry = entry;
    overlayState.insert(entry);
  }

  /// Convenience helper for success messages
  static void success(
    BuildContext? context,
    String message, {
    String? title,
    Duration duration = const Duration(milliseconds: 3200),
  }) {
    show(context, message, title: title, type: SnackBarType.success, duration: duration);
  }

  /// Convenience helper for error messages
  static void error(
    BuildContext? context,
    String message, {
    String? title,
    Duration duration = const Duration(milliseconds: 3800),
  }) {
    show(context, message, title: title, type: SnackBarType.error, duration: duration);
  }

  /// Convenience helper for warning messages
  static void warning(
    BuildContext? context,
    String message, {
    String? title,
    Duration duration = const Duration(milliseconds: 3500),
  }) {
    show(context, message, title: title, type: SnackBarType.warning, duration: duration);
  }

  /// Convenience helper for informational messages
  static void info(
    BuildContext? context,
    String message, {
    String? title,
    Duration duration = const Duration(milliseconds: 3200),
  }) {
    show(context, message, title: title, type: SnackBarType.info, duration: duration);
  }

  static void _dismissCurrent() {
    if (_activeWidgetState != null) {
      _activeWidgetState?.dismiss();
      _activeWidgetState = null;
    } else if (_activeEntry != null) {
      _activeEntry?.remove();
      _activeEntry = null;
    }
  }

  static OverlayState? _resolveOverlay(BuildContext? context) {
    if (context != null && context.mounted) {
      final overlay = Overlay.maybeOf(context);
      if (overlay != null) return overlay;
    }
    return OpenboxSellerApp.navigatorKey.currentState?.overlay;
  }
}

class _TopSnackBarWidget extends StatefulWidget {
  final String message;
  final String? title;
  final SnackBarType type;
  final Duration duration;
  final VoidCallback onDismissed;
  final void Function(_TopSnackBarWidgetState state) onStateCreated;

  const _TopSnackBarWidget({
    required this.message,
    this.title,
    required this.type,
    required this.duration,
    required this.onDismissed,
    required this.onStateCreated,
  });

  @override
  State<_TopSnackBarWidget> createState() => _TopSnackBarWidgetState();
}

class _TopSnackBarWidgetState extends State<_TopSnackBarWidget> with TickerProviderStateMixin {
  late AnimationController _slideController;
  late Animation<Offset> _slideAnimation;
  late Animation<double> _fadeAnimation;
  late AnimationController _progressController;

  bool _isDismissing = false;

  @override
  void initState() {
    super.initState();
    widget.onStateCreated(this);

    _slideController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 380),
      reverseDuration: const Duration(milliseconds: 280),
    );

    _slideAnimation = Tween<Offset>(
      begin: const Offset(0, -1.25),
      end: Offset.zero,
    ).animate(
      CurvedAnimation(
        parent: _slideController,
        curve: Curves.easeOutBack,
        reverseCurve: Curves.easeInCubic,
      ),
    );

    _fadeAnimation = CurvedAnimation(
      parent: _slideController,
      curve: Curves.easeOut,
      reverseCurve: Curves.easeIn,
    );

    _progressController = AnimationController(
      vsync: this,
      duration: widget.duration,
    );

    _slideController.forward();
    _progressController.forward();

    _progressController.addStatusListener((status) {
      if (status == AnimationStatus.completed) {
        dismiss();
      }
    });
  }

  void dismiss() {
    if (_isDismissing || !mounted) return;
    _isDismissing = true;
    _progressController.stop();
    _slideController.reverse().then((_) {
      if (mounted) {
        widget.onDismissed();
      }
    });
  }

  @override
  void dispose() {
    _progressController.dispose();
    _slideController.dispose();
    super.dispose();
  }

  Color get _accentColor {
    switch (widget.type) {
      case SnackBarType.success:
        return AppColors.success;
      case SnackBarType.error:
        return AppColors.danger;
      case SnackBarType.warning:
        return AppColors.warning;
      case SnackBarType.info:
        return AppColors.brand500;
    }
  }

  IconData get _icon {
    switch (widget.type) {
      case SnackBarType.success:
        return Icons.check_circle_rounded;
      case SnackBarType.error:
        return Icons.error_rounded;
      case SnackBarType.warning:
        return Icons.warning_rounded;
      case SnackBarType.info:
        return Icons.info_rounded;
    }
  }

  String get _defaultTitle {
    switch (widget.type) {
      case SnackBarType.success:
        return 'Success';
      case SnackBarType.error:
        return 'Attention';
      case SnackBarType.warning:
        return 'Warning';
      case SnackBarType.info:
        return 'Notification';
    }
  }

  @override
  Widget build(BuildContext context) {
    final mediaQuery = MediaQuery.of(context);
    final topPadding = mediaQuery.padding.top > 0 ? mediaQuery.padding.top + 6.0 : 16.0;

    return Positioned(
      top: 0,
      left: 0,
      right: 0,
      child: SafeArea(
        top: false,
        child: Padding(
          padding: EdgeInsets.only(top: topPadding, left: 16, right: 16),
          child: SlideTransition(
            position: _slideAnimation,
            child: FadeTransition(
              opacity: _fadeAnimation,
              child: GestureDetector(
                onTap: dismiss,
                onVerticalDragUpdate: (details) {
                  if (details.primaryDelta != null && details.primaryDelta! < -4) {
                    dismiss();
                  }
                },
                child: Material(
                  color: Colors.transparent,
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(16),
                    child: BackdropFilter(
                      filter: ImageFilter.blur(sigmaX: 16, sigmaY: 16),
                      child: Container(
                        decoration: BoxDecoration(
                          color: const Color(0xFF0F172A).withValues(alpha: 0.94),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                            color: _accentColor.withValues(alpha: 0.35),
                            width: 1.2,
                          ),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.28),
                              blurRadius: 24,
                              offset: const Offset(0, 8),
                            ),
                            BoxShadow(
                              color: _accentColor.withValues(alpha: 0.12),
                              blurRadius: 16,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.center,
                                children: [
                                  // Icon badge with glow
                                  Container(
                                    width: 36,
                                    height: 36,
                                    decoration: BoxDecoration(
                                      color: _accentColor.withValues(alpha: 0.16),
                                      shape: BoxShape.circle,
                                      border: Border.all(
                                        color: _accentColor.withValues(alpha: 0.4),
                                        width: 1,
                                      ),
                                    ),
                                    child: Icon(_icon, color: _accentColor, size: 20),
                                  ),
                                  const SizedBox(width: 12),

                                  // Text content
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Text(
                                          widget.title ?? _defaultTitle,
                                          style: TextStyle(
                                            fontSize: 13,
                                            fontWeight: FontWeight.w700,
                                            color: _accentColor,
                                            letterSpacing: 0.2,
                                          ),
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          widget.message,
                                          style: const TextStyle(
                                            fontSize: 12.5,
                                            fontWeight: FontWeight.w500,
                                            color: Color(0xFFE2E8F0),
                                            height: 1.3,
                                          ),
                                          maxLines: 3,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ],
                                    ),
                                  ),
                                  const SizedBox(width: 8),

                                  // Close action button
                                  InkWell(
                                    onTap: dismiss,
                                    borderRadius: BorderRadius.circular(20),
                                    child: Container(
                                      padding: const EdgeInsets.all(4),
                                      decoration: BoxDecoration(
                                        color: Colors.white.withValues(alpha: 0.08),
                                        shape: BoxShape.circle,
                                      ),
                                      child: const Icon(
                                        Icons.close_rounded,
                                        size: 14,
                                        color: Color(0xFF94A3B8),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),

                            // Bottom progress countdown bar
                            AnimatedBuilder(
                              animation: _progressController,
                              builder: (context, _) {
                                return LinearProgressIndicator(
                                  value: 1.0 - _progressController.value,
                                  backgroundColor: Colors.transparent,
                                  valueColor: AlwaysStoppedAnimation<Color>(
                                    _accentColor.withValues(alpha: 0.65),
                                  ),
                                  minHeight: 2,
                                );
                              },
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
