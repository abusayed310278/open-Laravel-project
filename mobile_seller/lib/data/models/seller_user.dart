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
  final String? coverUrl;
  final String? description;
  final String? company;
  final String? location;
  final String? address;
  final String? city;
  final String? country;
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
    this.coverUrl,
    this.description,
    this.company,
    this.location,
    this.address,
    this.city,
    this.country,
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
      logoUrl: json['logo_url'] != null ? ApiConfig.sanitizeUrl(json['logo_url'].toString()) : null,
      coverUrl: json['cover_url'] != null ? ApiConfig.sanitizeUrl(json['cover_url'].toString()) : null,
      description: json['description']?.toString(),
      company: json['company']?.toString() ?? json['business_name']?.toString(),
      location: json['location']?.toString() ?? json['address']?.toString(),
      address: json['address']?.toString(),
      city: json['city']?.toString(),
      country: json['country']?.toString(),
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
