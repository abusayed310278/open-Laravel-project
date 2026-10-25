import 'package:flutter/material.dart';
import '../theme/app_colors.dart';
import '../theme/app_spacing.dart';

enum SocialProvider { google, facebook }

class SocialLoginButton extends StatelessWidget {
  final SocialProvider provider;
  final VoidCallback? onPressed;
  final bool isLoading;
  final bool isSignUp;
  final bool isIconOnly;

  const SocialLoginButton({
    super.key,
    required this.provider,
    required this.onPressed,
    this.isLoading = false,
    this.isSignUp = false,
    this.isIconOnly = false,
  });

  @override
  Widget build(BuildContext context) {
    final isGoogle = provider == SocialProvider.google;
    final actionText = isSignUp ? 'Sign up' : 'Continue';
    final label = isGoogle ? '$actionText with Google' : '$actionText with Facebook';

    if (isGoogle) {
      return OutlinedButton(
        onPressed: isLoading ? null : onPressed,
        style: OutlinedButton.styleFrom(
          backgroundColor: AppColors.surface,
          foregroundColor: AppColors.ink,
          side: const BorderSide(color: AppColors.slate200, width: 1.2),
          padding: isIconOnly ? EdgeInsets.zero : const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
          fixedSize: isIconOnly ? const Size(48, 48) : null,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppRadius.md),
          ),
          elevation: 0,
        ),
        child: isLoading
            ? const SizedBox(
                width: 22,
                height: 22,
                child: CircularProgressIndicator(strokeWidth: 2, valueColor: AlwaysStoppedAnimation(AppColors.slate600)),
              )
            : isIconOnly
                ? Center(child: _buildGoogleIcon(size: 24))
                : Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      _buildGoogleIcon(),
                      const SizedBox(width: AppSpacing.md),
                      Text(
                        label,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: AppColors.ink,
                          letterSpacing: -0.2,
                        ),
                      ),
                    ],
                  ),
      );
    }

    // Facebook Button
    return OutlinedButton(
      onPressed: isLoading ? null : onPressed,
      style: OutlinedButton.styleFrom(
        backgroundColor: AppColors.surface,
        foregroundColor: AppColors.ink,
        side: const BorderSide(color: AppColors.slate200, width: 1.2),
        padding: isIconOnly ? EdgeInsets.zero : const EdgeInsets.symmetric(horizontal: 16, vertical: 13),
        fixedSize: isIconOnly ? const Size(48, 48) : null,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppRadius.md),
        ),
        elevation: 0,
      ),
      child: isLoading
          ? const SizedBox(
              width: 22,
              height: 22,
              child: CircularProgressIndicator(strokeWidth: 2, valueColor: AlwaysStoppedAnimation(AppColors.slate600)),
            )
          : isIconOnly
              ? Center(child: _buildFacebookIcon(size: 24))
              : Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    _buildFacebookIcon(),
                    const SizedBox(width: AppSpacing.md),
                    Text(
                      label,
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                        color: AppColors.ink,
                        letterSpacing: -0.2,
                      ),
                    ),
                  ],
                ),
    );
  }

  Widget _buildGoogleIcon({double size = 20}) {
    return Container(
      width: size,
      height: size,
      decoration: const BoxDecoration(shape: BoxShape.circle),
      child: CustomPaint(
        painter: _GoogleIconPainter(),
      ),
    );
  }

  Widget _buildFacebookIcon({double size = 20}) {
    return Container(
      width: size,
      height: size,
      decoration: const BoxDecoration(
        color: Color(0xFF1877F2),
        shape: BoxShape.circle,
      ),
      alignment: Alignment.center,
      child: Text(
        'f',
        style: TextStyle(
          color: Colors.white,
          fontWeight: FontWeight.w900,
          fontSize: size * 0.8,
          height: 1.1,
          fontFamily: 'sans-serif',
        ),
      ),
    );
  }
}

/// Custom painter for the 4-color Google "G" logo
class _GoogleIconPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final double w = size.width;
    final double h = size.height;

    // Draw Google 4-color G using arcs
    final center = Offset(w / 2, h / 2);
    final radius = w / 2;

    final paintBlue = Paint()
      ..color = const Color(0xFF4285F4)
      ..style = PaintingStyle.fill;
    final paintRed = Paint()
      ..color = const Color(0xFFEA4335)
      ..style = PaintingStyle.fill;
    final paintYellow = Paint()
      ..color = const Color(0xFFFBBC05)
      ..style = PaintingStyle.fill;
    final paintGreen = Paint()
      ..color = const Color(0xFF34A853)
      ..style = PaintingStyle.fill;

    final rect = Rect.fromCircle(center: center, radius: radius);

    // Red top
    final pathRed = Path()
      ..moveTo(center.dx, center.dy)
      ..arcTo(rect, -2.35, 1.57, false)
      ..close();
    canvas.drawPath(pathRed, paintRed);

    // Yellow left
    final pathYellow = Path()
      ..moveTo(center.dx, center.dy)
      ..arcTo(rect, 2.35, 1.57, false)
      ..close();
    canvas.drawPath(pathYellow, paintYellow);

    // Green bottom
    final pathGreen = Path()
      ..moveTo(center.dx, center.dy)
      ..arcTo(rect, 0.78, 1.57, false)
      ..close();
    canvas.drawPath(pathGreen, paintGreen);

    // Blue right bar and section
    final pathBlue = Path()
      ..moveTo(center.dx, center.dy)
      ..arcTo(rect, -0.78, 1.57, false)
      ..close();
    canvas.drawPath(pathBlue, paintBlue);

    // Inner white circle to make donut
    final innerPaint = Paint()
      ..color = Colors.white
      ..style = PaintingStyle.fill;
    canvas.drawCircle(center, radius * 0.58, innerPaint);

    // Blue crossbar
    final barRect = Rect.fromLTWH(center.dx - 1, center.dy - radius * 0.22, radius + 1, radius * 0.44);
    canvas.drawRect(barRect, paintBlue);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
