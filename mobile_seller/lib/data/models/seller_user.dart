import '../../core/network/api_config.dart';

class SellerUser {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? role;
  final String? roleLabel;
  final String storeName;
  final String storeSlug;
  final String? logoUrl;
  final String? avatarUrl;
  final String? coverUrl;
  final String? description;
  final String? company;
  final String? location;
  final String? address;
  final String? city;
  final String? country;
  final double? latitude;
  final double? longitude;
  final String? website;
  final String? timezone;
  final Map<String, dynamic>? businessHours;
  final List<dynamic>? socialLinks;
  final bool isStoreActive;
  final bool isKycApproved;
  final int listingCredits;
  final String? subscriptionPlan;
  final double availableBalance;
  final double pendingBalance;
  final double totalEarned;

  const SellerUser({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.role,
    this.roleLabel,
    required this.storeName,
    required this.storeSlug,
    this.logoUrl,
    this.avatarUrl,
    this.coverUrl,
    this.description,
    this.company,
    this.location,
    this.address,
    this.city,
    this.country,
    this.latitude,
    this.longitude,
    this.website,
    this.timezone,
    this.businessHours,
    this.socialLinks,
    this.isStoreActive = true,
    this.isKycApproved = false,
    this.listingCredits = 0,
    this.subscriptionPlan,
    this.availableBalance = 0.0,
    this.pendingBalance = 0.0,
    this.totalEarned = 0.0,
  });

  factory SellerUser.fromJson(Map<String, dynamic> json) {
    final sub = json['subscription'] as Map<String, dynamic>?;
    final wallet = json['wallet'] as Map<String, dynamic>?;

    return SellerUser(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      name: json['name']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      phone: json['phone']?.toString(),
      role: json['role']?.toString(),
      roleLabel: json['role_label']?.toString(),
      storeName: json['store_name']?.toString() ?? (json['name']?.toString() ?? ''),
      storeSlug: json['store_slug']?.toString() ?? '',
      logoUrl: (json['logo_url'] ?? json['avatar_url'] ?? json['avatar']) != null
          ? ApiConfig.sanitizeUrl((json['logo_url'] ?? json['avatar_url'] ?? json['avatar']).toString())
          : null,
      avatarUrl: (json['avatar_url'] ?? json['logo_url'] ?? json['avatar']) != null
          ? ApiConfig.sanitizeUrl((json['avatar_url'] ?? json['logo_url'] ?? json['avatar']).toString())
          : null,
      coverUrl: json['cover_url'] != null ? ApiConfig.sanitizeUrl(json['cover_url'].toString()) : null,
      description: json['description']?.toString(),
      company: json['company']?.toString() ?? json['business_name']?.toString(),
      location: json['location']?.toString() ?? json['address']?.toString(),
      address: json['address']?.toString(),
      city: json['city']?.toString(),
      country: json['country']?.toString(),
      latitude: json['latitude'] != null ? double.tryParse(json['latitude'].toString()) : null,
      longitude: json['longitude'] != null ? double.tryParse(json['longitude'].toString()) : null,
      website: json['website']?.toString(),
      timezone: json['timezone']?.toString(),
      businessHours: json['business_hours'] is Map
          ? (json['business_hours'] as Map).map((k, v) => MapEntry(k.toString(), v))
          : null,
      socialLinks: json['social_links'] is List ? List<dynamic>.from(json['social_links'] as List) : null,
      isStoreActive: json['is_store_active'] == true,
      isKycApproved: json['is_kyc_approved'] == true,
      listingCredits: json['listing_credits'] is int
          ? json['listing_credits']
          : int.tryParse(json['listing_credits']?.toString() ?? '0') ?? 0,
      subscriptionPlan: sub?['plan_name']?.toString(),
      availableBalance: (wallet?['available_balance'] is num)
          ? (wallet!['available_balance'] as num).toDouble()
          : double.tryParse(wallet?['available_balance']?.toString() ?? '0') ?? 0.0,
      pendingBalance: (wallet?['pending_balance'] is num)
          ? (wallet!['pending_balance'] as num).toDouble()
          : double.tryParse(wallet?['pending_balance']?.toString() ?? '0') ?? 0.0,
      totalEarned: (wallet?['total_earned'] is num)
          ? (wallet!['total_earned'] as num).toDouble()
          : double.tryParse(wallet?['total_earned']?.toString() ?? '0') ?? 0.0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'phone': phone,
      'role': role,
      'role_label': roleLabel,
      'store_name': storeName,
      'store_slug': storeSlug,
      'logo_url': logoUrl,
      'cover_url': coverUrl,
      'description': description,
      'address': address,
      'city': city,
      'country': country,
      'latitude': latitude,
      'longitude': longitude,
      'website': website,
      'timezone': timezone,
      'business_hours': businessHours,
      'social_links': socialLinks,
      'is_store_active': isStoreActive,
      'is_kyc_approved': isKycApproved,
      'listing_credits': listingCredits,
      'subscription': subscriptionPlan != null ? {'plan_name': subscriptionPlan} : null,
      'wallet': {
        'available_balance': availableBalance,
        'pending_balance': pendingBalance,
        'total_earned': totalEarned,
      },
    };
  }
}
