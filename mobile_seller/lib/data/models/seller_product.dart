import '../../core/network/api_config.dart';

class SellerProduct {
  final int id;
  final String title;
  final String? slug;
  final String? categoryName;
  final String? brandName;
  final double price;
  final double? comparePrice;
  final int quantity;
  final String? condition;
  final String? conditionLabel;
  final String? status;
  final String? approvalStatus;
  final String? publicationStatus;
  final bool isPublished;
  final String? thumbnail;
  final int viewsCount;
  final String? createdAt;

  const SellerProduct({
    required this.id,
    required this.title,
    this.slug,
    this.categoryName,
    this.brandName,
    required this.price,
    this.comparePrice,
    required this.quantity,
    this.condition,
    this.conditionLabel,
    this.status,
    this.approvalStatus,
    this.publicationStatus,
    this.isPublished = false,
    this.thumbnail,
    this.viewsCount = 0,
    this.createdAt,
  });

  factory SellerProduct.fromJson(Map<String, dynamic> json) {
    return SellerProduct(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      title: json['title']?.toString() ?? '',
      slug: json['slug']?.toString(),
      categoryName: json['category_name']?.toString(),
      brandName: json['brand_name']?.toString(),
      price: (json['price'] is num) ? (json['price'] as num).toDouble() : 0.0,
      comparePrice: (json['compare_price'] is num) ? (json['compare_price'] as num).toDouble() : null,
      quantity: json['quantity'] is int ? json['quantity'] : 0,
      condition: json['condition']?.toString(),
      conditionLabel: json['condition_label']?.toString() ?? json['condition']?.toString(),
      status: json['status']?.toString(),
      approvalStatus: json['approval_status']?.toString(),
      publicationStatus: json['publication_status']?.toString(),
      isPublished: json['is_published'] == true,
      thumbnail: json['thumbnail'] != null ? ApiConfig.sanitizeUrl(json['thumbnail'].toString()) : null,
      viewsCount: json['views_count'] is int ? json['views_count'] : 0,
      createdAt: json['created_at']?.toString(),
    );
  }
}

class ProductImageItem {
  final int id;
  final String? url;
  final bool isPrimary;
  final int sortOrder;

  const ProductImageItem({
    required this.id,
    this.url,
    this.isPrimary = false,
    this.sortOrder = 0,
  });

  factory ProductImageItem.fromJson(Map<String, dynamic> json) {
    return ProductImageItem(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      url: json['url'] != null ? ApiConfig.sanitizeUrl(json['url'].toString()) : null,
      isPrimary: json['is_primary'] == true,
      sortOrder: json['sort_order'] is int ? json['sort_order'] : 0,
    );
  }
}

class ProductAttributeItem {
  final int attributeId;
  final String? attributeName;
  final int? valueId;
  final String? value;

  const ProductAttributeItem({
    required this.attributeId,
    this.attributeName,
    this.valueId,
    this.value,
  });

  factory ProductAttributeItem.fromJson(Map<String, dynamic> json) {
    return ProductAttributeItem(
      attributeId: json['attribute_id'] is int ? json['attribute_id'] : int.parse(json['attribute_id'].toString()),
      attributeName: json['attribute_name']?.toString(),
      valueId: json['value_id'] as int?,
      value: json['value']?.toString(),
    );
  }
}

class SellerProductDetail {
  final int id;
  final String title;
  final String? slug;
  final String? description;
  final String? shortDescription;
  final int? categoryId;
  final String? categoryName;
  final int? brandId;
  final String? brandName;
  final String? condition;
  final String? grade;
  final double price;
  final double? comparePrice;
  final String? sku;
  final int quantity;
  final bool isNegotiable;
  final double? weight;
  final String? shippingType;
  final double? shippingFlatRate;
  final String? status;
  final String? approvalStatus;
  final String? publicationStatus;
  final String? rejectionReason;
  final bool isLocked;
  final List<ProductImageItem> images;
  final List<ProductAttributeItem> attributes;
  final String? createdAt;

  const SellerProductDetail({
    required this.id,
    required this.title,
    this.slug,
    this.description,
    this.shortDescription,
    this.categoryId,
    this.categoryName,
    this.brandId,
    this.brandName,
    this.condition,
    this.grade,
    required this.price,
    this.comparePrice,
    this.sku,
    required this.quantity,
    this.isNegotiable = false,
    this.weight,
    this.shippingType,
    this.shippingFlatRate,
    this.status,
    this.approvalStatus,
    this.publicationStatus,
    this.rejectionReason,
    this.isLocked = false,
    required this.images,
    required this.attributes,
    this.createdAt,
  });

  factory SellerProductDetail.fromJson(Map<String, dynamic> json) {
    final imagesList = (json['images'] as List?)
            ?.map((e) => ProductImageItem.fromJson(e as Map<String, dynamic>))
            .toList() ??
        [];
    final attrsList = (json['attributes'] as List?)
            ?.map((e) => ProductAttributeItem.fromJson(e as Map<String, dynamic>))
            .toList() ??
        [];

    return SellerProductDetail(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      title: json['title']?.toString() ?? '',
      slug: json['slug']?.toString(),
      description: json['description']?.toString(),
      shortDescription: json['short_description']?.toString(),
      categoryId: json['category_id'] as int?,
      categoryName: json['category_name']?.toString(),
      brandId: json['brand_id'] as int?,
      brandName: json['brand_name']?.toString(),
      condition: json['condition']?.toString(),
      grade: json['grade']?.toString(),
      price: (json['price'] is num) ? (json['price'] as num).toDouble() : 0.0,
      comparePrice: (json['compare_price'] is num) ? (json['compare_price'] as num).toDouble() : null,
      sku: json['sku']?.toString(),
      quantity: json['quantity'] is int ? json['quantity'] : 0,
      isNegotiable: json['is_negotiable'] == true,
      weight: (json['weight'] is num) ? (json['weight'] as num).toDouble() : null,
      shippingType: json['shipping_type']?.toString(),
      shippingFlatRate: (json['shipping_flat_rate'] is num) ? (json['shipping_flat_rate'] as num).toDouble() : null,
      status: json['status']?.toString(),
      approvalStatus: json['approval_status']?.toString(),
      publicationStatus: json['publication_status']?.toString(),
      rejectionReason: json['rejection_reason']?.toString(),
      isLocked: json['is_locked'] == true,
      images: imagesList,
      attributes: attrsList,
      createdAt: json['created_at']?.toString(),
    );
  }
}
