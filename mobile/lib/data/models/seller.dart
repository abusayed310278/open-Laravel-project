import '../../core/network/api_config.dart';
import 'product.dart';

enum SellerType { openbox, business, saler }

class Seller {
  const Seller({
    required this.id,
    required this.name,
    required this.type,
    this.avatarInitial = 'S',
    this.avatarUrl = '',
    this.isVerified = false,
    this.rating = 0,
    this.reviewCount = 0,
    this.city = '',
    this.productCount = 0,
    this.memberSince = '',
  });

  final String id;
  final String name;
  final SellerType type;
  final String avatarInitial;
  final String avatarUrl;
  final bool isVerified;
  final double rating;
  final int reviewCount;
  final String city;
  final int productCount;
  final String memberSince;

  factory Seller.fromJson(Map<String, dynamic> json) {
    final nameStr = json['name']?.toString() ??
        json['store_name']?.toString() ??
        json['shop_name']?.toString() ??
        json['business_name']?.toString() ??
        json['seller_name']?.toString() ??
        'Seller';

    final rawAvatar = json['avatar_url']?.toString() ??
        json['avatar']?.toString() ??
        json['logo_url']?.toString() ??
        json['logo']?.toString() ??
        json['profile_photo']?.toString() ??
        json['profile_image']?.toString() ??
        json['image']?.toString() ??
        json['photo']?.toString();

    String formattedAvatar = Product.formatImageUrl(rawAvatar);
    if (formattedAvatar.isEmpty && nameStr.toLowerCase().contains('admin')) {
      formattedAvatar = ApiConfig.appIconUrl;
    }

    String initial = 'S';
    if (nameStr.isNotEmpty) {
      final words = nameStr.trim().split(RegExp(r'\s+'));
      if (words.length >= 2) {
        initial = '${words[0][0]}${words[1][0]}'.toUpperCase();
      } else {
        initial = nameStr[0].toUpperCase();
      }
    }

    return Seller(
      id: json['id']?.toString() ?? '',
      name: nameStr,
      type: SellerType.business,
      avatarInitial: initial,
      avatarUrl: formattedAvatar,
      isVerified: json['is_verified'] == true || json['verified'] == true || json['is_verified'] == 1,
      rating: (json['rating'] is num) ? (json['rating'] as num).toDouble() : 4.5,
      reviewCount: (json['review_count'] is num) ? (json['review_count'] as num).toInt() : 0,
      city: json['city']?.toString() ?? json['location']?.toString() ?? json['address']?.toString() ?? '',
      productCount: (json['product_count'] is num) ? (json['product_count'] as num).toInt() : 0,
      memberSince: json['member_since']?.toString() ?? json['created_at']?.toString().split('T').first ?? '',
    );
  }
}
