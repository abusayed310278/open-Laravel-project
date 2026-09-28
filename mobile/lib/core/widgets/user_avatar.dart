import 'dart:io';
import 'package:flutter/material.dart';

import '../../data/models/product.dart';
import '../theme/app_colors.dart';

class UserAvatar extends StatelessWidget {
  const UserAvatar({
    super.key,
    required this.avatarUrlOrPath,
    required this.name,
    this.radius = 28,
    this.fontSize = 18,
  });

  final String? avatarUrlOrPath;
  final String name;
  final double radius;
  final double fontSize;

  static String getInitials(String name) {
    if (name.trim().isEmpty) return 'U';
    final parts = name.trim().split(' ');
    if (parts.length >= 2 && parts[1].isNotEmpty) {
      return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
    }
    return parts[0][0].toUpperCase();
  }

  @override
  Widget build(BuildContext context) {
    final double diameter = radius * 2;
    final fallbackWidget = Text(
      getInitials(name),
      style: TextStyle(
        color: AppColors.brand700,
        fontWeight: FontWeight.w800,
        fontSize: fontSize,
      ),
    );

    Widget? imageContent;
    final raw = avatarUrlOrPath?.trim() ?? '';

    if (raw.isNotEmpty) {
      if (raw.startsWith('/') ||
          raw.startsWith('file://') ||
          (!raw.startsWith('http://') && !raw.startsWith('https://'))) {
        final filePath = raw.replaceFirst('file://', '');
        final file = File(filePath);
        if (file.existsSync()) {
          imageContent = Image.file(
            file,
            width: diameter,
            height: diameter,
            fit: BoxFit.cover,
            errorBuilder: (_, _, _) => fallbackWidget,
          );
        }
      }

      imageContent ??= Image.network(
        Product.formatImageUrl(raw),
        width: diameter,
        height: diameter,
        fit: BoxFit.cover,
        errorBuilder: (_, _, _) => fallbackWidget,
      );
    }

    return CircleAvatar(
      radius: radius,
      backgroundColor: AppColors.brand100,
      child: imageContent != null ? ClipOval(child: imageContent) : fallbackWidget,
    );
  }
}
