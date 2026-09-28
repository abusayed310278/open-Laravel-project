import 'package:flutter/material.dart';
import '../network/api_config.dart';
import '../theme/app_colors.dart';

class AppLogo extends StatelessWidget {
  const AppLogo({
    super.key,
    this.size = 40.0,
    this.borderRadius = 12.0,
  });

  final double size;
  final double borderRadius;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: AppColors.brand500,
        borderRadius: BorderRadius.circular(borderRadius),
      ),
      clipBehavior: Clip.antiAlias,
      child: Image.network(
        ApiConfig.appIconUrl,
        fit: BoxFit.cover,
        errorBuilder: (_, __, ___) => Icon(
          Icons.inventory_2_rounded,
          color: Colors.white,
          size: size * 0.55,
        ),
      ),
    );
  }
}
