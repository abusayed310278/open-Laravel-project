import 'dart:math' as math;
import 'package:flutter/material.dart';

import '../theme/app_colors.dart';
import '../theme/app_spacing.dart';

/// A tremendous, high-end animated loading widget for API requests and screen transitions.
/// Features dual orbiting gradient arcs, a breathing brand halo, pulsing emblem,
/// and sequential bouncing animated progress dots.
class LoadingView extends StatefulWidget {
  final String? message;
  final String? subtitle;
  final IconData? icon;
  final String? logoAsset;
  final double size;

  const LoadingView({
    super.key,
    this.message,
    this.subtitle,
    this.icon,
    this.logoAsset = 'assets/icon/logo.png',
    this.size = 88.0,
  });

  @override
  State<LoadingView> createState() => _LoadingViewState();
}

class _LoadingViewState extends State<LoadingView> with TickerProviderStateMixin {
  late final AnimationController _rotationController;
  late final AnimationController _pulseController;
  late final AnimationController _dotsController;

  late final Animation<double> _pulseAnimation;

  @override
  void initState() {
    super.initState();

    // 1. Continuous smooth rotation for dual orbiting arcs
    _rotationController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 2200),
    )..repeat();

    // 2. Gentle breathing pulse for brand glow and center emblem
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1400),
    )..repeat(reverse: true);

    _pulseAnimation = CurvedAnimation(
      parent: _pulseController,
      curve: Curves.easeInOut,
    );

    // 3. Staggered jumping dots animation for text caption
    _dotsController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    )..repeat();
  }

  @override
  void dispose() {
    _rotationController.dispose();
    _pulseController.dispose();
    _dotsController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final displayMessage = widget.message ?? 'Loading';
    final displaySubtitle = widget.subtitle ?? 'Syncing with Openbox platform...';
    final centerIcon = widget.icon ?? Icons.storefront_rounded;

    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xl, vertical: AppSpacing.xxl),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // ─── Tremendous Orb & Dual Orbital Rings ───
            AnimatedBuilder(
              animation: Listenable.merge([_rotationController, _pulseAnimation]),
              builder: (context, _) {
                final pulseVal = _pulseAnimation.value;
                final scale = 0.94 + (0.09 * pulseVal);
                final haloSize = widget.size * (1.18 + (0.12 * pulseVal));

                return SizedBox(
                  width: haloSize,
                  height: haloSize,
                  child: Stack(
                    alignment: Alignment.center,
                    children: [
                      // Breathing Outer Brand Ambient Halo Glow
                      Container(
                        width: haloSize,
                        height: haloSize,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          gradient: RadialGradient(
                            colors: [
                              AppColors.brand500.withValues(alpha: 0.18 * pulseVal),
                              AppColors.brand400.withValues(alpha: 0.06 * pulseVal),
                              Colors.transparent,
                            ],
                            stops: const [0.35, 0.7, 1.0],
                          ),
                        ),
                      ),

                      // Dual Orbital Gradient Rings Canvas
                      CustomPaint(
                        size: Size(widget.size, widget.size),
                        painter: _TremendousOrbitalPainter(
                          rotationAngle: _rotationController.value * 2 * math.pi,
                          pulseProgress: pulseVal,
                        ),
                      ),

                      // Center Floating Brand Emblem with Glass Card & Shadow
                      Transform.scale(
                        scale: scale,
                        child: Container(
                          width: widget.size * 0.54,
                          height: widget.size * 0.54,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: AppColors.surface,
                            boxShadow: [
                              BoxShadow(
                                color: AppColors.brand500.withValues(alpha: 0.24 + (0.12 * pulseVal)),
                                blurRadius: 16 + (6 * pulseVal),
                                spreadRadius: 1 + (2 * pulseVal),
                                offset: const Offset(0, 4),
                              ),
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.06),
                                blurRadius: 6,
                                offset: const Offset(0, 2),
                              ),
                            ],
                            border: Border.all(
                              color: AppColors.brand100,
                              width: 1.5,
                            ),
                          ),
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(widget.size * 0.27),
                            child: Padding(
                              padding: EdgeInsets.all(widget.size * 0.08),
                              child: widget.logoAsset != null
                                  ? Image.asset(
                                      widget.logoAsset!,
                                      fit: BoxFit.contain,
                                      errorBuilder: (_, _, _) => Icon(
                                        centerIcon,
                                        color: AppColors.brand600,
                                        size: widget.size * 0.28,
                                      ),
                                    )
                                  : Icon(
                                      centerIcon,
                                      color: AppColors.brand600,
                                      size: widget.size * 0.28,
                                    ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                );
              },
            ),

            const SizedBox(height: AppSpacing.xl),

            // ─── Animated Typography & Progress Indicator ───
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                Flexible(
                  child: Text(
                    displayMessage,
                    textAlign: TextAlign.center,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w700,
                      letterSpacing: -0.2,
                      color: AppColors.ink,
                    ),
                  ),
                ),
                const SizedBox(width: 4),
                // 3 Bouncing Sequential Amber Dots
                _buildBouncingDots(),
              ],
            ),

            const SizedBox(height: 6),

            Text(
              displaySubtitle,
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w500,
                color: AppColors.slate400,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBouncingDots() {
    return AnimatedBuilder(
      animation: _dotsController,
      builder: (context, _) {
        final val = _dotsController.value;
        return Row(
          mainAxisSize: MainAxisSize.min,
          children: List.generate(3, (index) {
            // Offset each dot by a phase fraction (0.0, 0.25, 0.5)
            final phase = (val - (index * 0.22)) % 1.0;
            // Sine bounce curve
            final bounce = math.sin(phase * math.pi);
            final clampedBounce = bounce > 0 ? bounce : 0.0;
            final dy = -3.5 * clampedBounce;
            final opacity = 0.35 + (0.65 * clampedBounce);

            return Transform.translate(
              offset: Offset(0, dy),
              child: Container(
                margin: const EdgeInsets.symmetric(horizontal: 1.5),
                width: 3.5,
                height: 3.5,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: AppColors.brand500.withValues(alpha: opacity),
                ),
              ),
            );
          }),
        );
      },
    );
  }
}

/// Custom painter that renders two concentric counter-rotating gradient arcs
/// with rounded caps and glowing head dots.
class _TremendousOrbitalPainter extends CustomPainter {
  final double rotationAngle;
  final double pulseProgress;

  _TremendousOrbitalPainter({
    required this.rotationAngle,
    required this.pulseProgress,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final outerRadius = size.width / 2;
    final innerRadius = outerRadius * 0.76;

    // 1. Subtle background tracks
    final bgPaint = Paint()
      ..color = AppColors.slate100.withValues(alpha: 0.9)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3.0;

    canvas.drawCircle(center, outerRadius, bgPaint);
    canvas.drawCircle(center, innerRadius, bgPaint..strokeWidth = 2.0);

    // 2. Outer Orbital Arc (Sweeping clockwise)
    final outerSweepAngle = math.pi * 1.35;
    final outerRect = Rect.fromCircle(center: center, radius: outerRadius);

    final outerGradient = SweepGradient(
      startAngle: 0.0,
      endAngle: outerSweepAngle,
      colors: [
        AppColors.brand500.withValues(alpha: 0.0),
        AppColors.brand400,
        AppColors.brand600,
      ],
      stops: const [0.0, 0.45, 1.0],
      transform: GradientRotation(rotationAngle),
    );

    final outerPaint = Paint()
      ..shader = outerGradient.createShader(outerRect)
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round
      ..strokeWidth = 3.8;

    canvas.drawArc(outerRect, rotationAngle, outerSweepAngle, false, outerPaint);

    // Glowing dot at the head of the outer arc
    final outerHeadAngle = rotationAngle + outerSweepAngle;
    final outerHeadOffset = Offset(
      center.dx + outerRadius * math.cos(outerHeadAngle),
      center.dy + outerRadius * math.sin(outerHeadAngle),
    );
    final dotGlowPaint = Paint()
      ..color = AppColors.brand400.withValues(alpha: 0.4)
      ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 3);
    canvas.drawCircle(outerHeadOffset, 3.5, dotGlowPaint);
    canvas.drawCircle(outerHeadOffset, 2.4, Paint()..color = AppColors.brand600);

    // 3. Inner Orbital Arc (Counter-rotating counter-clockwise)
    final innerRotation = -rotationAngle * 1.35;
    final innerSweepAngle = math.pi * 1.15;
    final innerRect = Rect.fromCircle(center: center, radius: innerRadius);

    final innerGradient = SweepGradient(
      startAngle: 0.0,
      endAngle: innerSweepAngle,
      colors: [
        AppColors.brand300.withValues(alpha: 0.0),
        AppColors.brand400,
        AppColors.brand500,
      ],
      stops: const [0.0, 0.5, 1.0],
      transform: GradientRotation(innerRotation),
    );

    final innerPaint = Paint()
      ..shader = innerGradient.createShader(innerRect)
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round
      ..strokeWidth = 2.6;

    canvas.drawArc(innerRect, innerRotation, innerSweepAngle, false, innerPaint);
  }

  @override
  bool shouldRepaint(covariant _TremendousOrbitalPainter oldDelegate) {
    return oldDelegate.rotationAngle != rotationAngle || oldDelegate.pulseProgress != pulseProgress;
  }
}
