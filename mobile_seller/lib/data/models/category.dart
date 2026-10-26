import 'package:flutter/material.dart';

class ProductCategory {
  const ProductCategory({
    required this.id,
    required this.name,
    this.icon = Icons.category_outlined,
    this.slug = '',
    this.image = '',
  });

  final String id;
  final String name;
  final IconData icon;
  final String slug;
  final String image;

  factory ProductCategory.fromJson(Map<String, dynamic> json) {
    return ProductCategory(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      slug: json['slug']?.toString() ?? '',
      image: json['image']?.toString() ?? json['image_url']?.toString() ?? '',
      icon: _getIconForCategory(json['name']?.toString() ?? ''),
    );
  }

  static IconData _getIconForCategory(String name) {
    final lower = name.toLowerCase();
    if (lower.contains('mobile') || lower.contains('phone')) return Icons.smartphone;
    if (lower.contains('laptop') || lower.contains('computer')) return Icons.laptop;
    if (lower.contains('fashion') || lower.contains('clothing')) return Icons.checkroom;
    if (lower.contains('home') || lower.contains('furniture')) return Icons.chair;
    if (lower.contains('electronic')) return Icons.devices;
    return Icons.category_outlined;
  }
}
