import 'dart:convert';
import '../../core/network/api_config.dart';
import 'seller.dart';

/// Condition grade — see OPENBOX_PROJECT_DEVELOPMENT.md's grading system
/// (Grade A "Like New", B "Good", C "Fair").
enum ProductGrade { a, b, c }

extension ProductGradeX on ProductGrade {
  String get label => switch (this) {
        ProductGrade.a => 'Grade A · Like New',
        ProductGrade.b => 'Grade B · Good',
        ProductGrade.c => 'Grade C · Fair',
      };

  String get short => switch (this) {
        ProductGrade.a => 'A',
        ProductGrade.b => 'B',
        ProductGrade.c => 'C',
      };
}

class ReviewItem {
  final String id;
  final String author;
  final double rating;
  final String title;
  final String comment;
  final String date;

  const ReviewItem({
    required this.id,
    required this.author,
    required this.rating,
    required this.title,
    required this.comment,
    required this.date,
  });

  factory ReviewItem.fromJson(Map<String, dynamic> json) {
    final reviewer = json['reviewer'] ?? json['user'];
    final authorName = reviewer != null && reviewer['name'] != null
        ? reviewer['name'].toString()
        : 'Verified Customer';

    return ReviewItem(
      id: json['id']?.toString() ?? '',
      author: authorName,
      rating: (json['rating'] is num) ? (json['rating'] as num).toDouble() : 5.0,
      title: json['title']?.toString() ?? '',
      comment: json['body']?.toString() ?? json['comment']?.toString() ?? '',
      date: json['created_at'] != null ? json['created_at'].toString().split('T').first : '',
    );
  }

  String get avatarInitial => author.isNotEmpty ? author[0].toUpperCase() : 'C';
}

class Product {
  const Product({
    required this.id,
    required this.title,
    required this.imageUrl,
    required this.price,
    required this.categoryId,
    required this.sellerId,
    this.seller,
    this.slug = '',
    this.brandName = '',
    this.sku = '',
    this.shortDescription = '',
    this.compareAtPrice,
    this.grade,
    this.isNew = false,
    this.isVerified = false,
    this.isWarehoused = false,
    this.rating = 0,
    this.reviewCount = 0,
    this.stock = 10,
    this.gallery = const [],
    this.reviews = const [],
    this.description = '',
    this.specs = const {},
  });

  final String id;
  final String title;
  final String slug;
  final String imageUrl;
  final double price;
  final double? compareAtPrice;
  final String categoryId;
  final String sellerId;
  final Seller? seller;
  final String brandName;
  final String sku;
  final String shortDescription;
  final ProductGrade? grade;
  final bool isNew;
  final bool isVerified;
  final bool isWarehoused;
  final double rating;
  final int reviewCount;
  final int stock;
  final List<String> gallery;
  final List<ReviewItem> reviews;
  final String description;
  final Map<String, String> specs;

  int? get discountPercent {
    if (compareAtPrice == null || compareAtPrice! <= price) return null;
    return (((compareAtPrice! - price) / compareAtPrice!) * 100).round();
  }

  String get cleanDescription {
    if (description.isEmpty) return 'No description provided.';

    String text = description
        .replaceAll(RegExp(r'</(p|h1|h2|h3|h4|h5|h6|div|section|li)>', caseSensitive: false), '\n\n')
        .replaceAll(RegExp(r'<br\s*/?>', caseSensitive: false), '\n');

    text = text.replaceAll(RegExp(r'<[^>]*>'), '');

    text = text
        .replaceAll('&nbsp;', ' ')
        .replaceAll('&amp;', '&')
        .replaceAll('&quot;', '"')
        .replaceAll('&apos;', "'")
        .replaceAll('&#39;', "'")
        .replaceAll('&#039;', "'")
        .replaceAll('&lt;', '<')
        .replaceAll('&gt;', '>');

    text = text.replaceAll(RegExp(r'\n{3,}'), '\n\n').trim();

    if (text.toLowerCase().startsWith('description')) {
      text = text.substring('description'.length).trim();
    }

    return text.isEmpty ? 'No description provided.' : text;
  }

  String get cleanShortDescription {
    final sourceText = shortDescription.trim();
    if (sourceText.isEmpty) return '';

    String text = sourceText
        .replaceAll(RegExp(r'</(p|h1|h2|h3|h4|h5|h6|div|section|ul|ol)>', caseSensitive: false), '\n')
        .replaceAll(RegExp(r'<li[^>]*>', caseSensitive: false), '• ')
        .replaceAll(RegExp(r'</li>', caseSensitive: false), '\n')
        .replaceAll(RegExp(r'<br\s*/?>', caseSensitive: false), '\n');

    text = text.replaceAll(RegExp(r'<[^>]*>'), '');

    text = text
        .replaceAll('&nbsp;', ' ')
        .replaceAll('&amp;', '&')
        .replaceAll('&quot;', '"')
        .replaceAll('&apos;', "'")
        .replaceAll('&#39;', "'")
        .replaceAll('&#039;', "'")
        .replaceAll('&lt;', '<')
        .replaceAll('&gt;', '>');

    return text.replaceAll(RegExp(r'\n{3,}'), '\n\n').trim();
  }

  static String formatImageUrl(String? rawUrl) {
    if (rawUrl == null || rawUrl.isEmpty || rawUrl.contains('via.placeholder.com')) {
      return '';
    }
    String trimmed = rawUrl.trim();
    if (trimmed.startsWith('{') || trimmed.startsWith('[') || trimmed.contains('Instance of')) {
      return '';
    }

    final backendHost = ApiConfig.backendHost;
    trimmed = ApiConfig.sanitizeUrl(trimmed);

    if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) {
      return trimmed.contains(' ') ? trimmed.replaceAll(' ', '%20') : trimmed;
    }

    String result;
    if (trimmed.startsWith('products/')) {
      result = '$backendHost/storage/$trimmed';
    } else if (trimmed.startsWith('/products/')) {
      result = '$backendHost/storage$trimmed';
    } else if (trimmed.startsWith('storage/')) {
      result = '$backendHost/$trimmed';
    } else if (trimmed.startsWith('/storage/')) {
      result = '$backendHost$trimmed';
    } else {
      final cleanPath = trimmed.startsWith('/') ? trimmed : '/$trimmed';
      result = '$backendHost$cleanPath';
    }

    return result.contains(' ') ? result.replaceAll(' ', '%20') : result;
  }

  static String extractSingleImageUrl(dynamic raw) {
    if (raw == null) return '';
    if (raw is String) {
      final trimmed = raw.trim();
      if (trimmed.isEmpty) return '';
      if (trimmed.startsWith('{') && trimmed.endsWith('}')) {
        try {
          final decoded = jsonDecode(trimmed);
          if (decoded is Map) {
            return extractSingleImageUrl(decoded);
          }
        } catch (_) {}
      }
      return formatImageUrl(trimmed);
    } else if (raw is Map) {
      final path = raw['url']?.toString() ??
          raw['path']?.toString() ??
          raw['original_url']?.toString() ??
          raw['full_url']?.toString() ??
          raw['image']?.toString() ??
          raw['src']?.toString();
      if (path != null) return formatImageUrl(path);
    } else if (raw is List && raw.isNotEmpty) {
      final primaryItem = raw.firstWhere(
        (it) => it is Map && (it['is_primary'] == true || it['is_primary'] == 1 || it['primary'] == true),
        orElse: () => raw.first,
      );
      return extractSingleImageUrl(primaryItem);
    }
    return '';
  }

  factory Product.fromJson(Map<String, dynamic> json) {
    final cat = json['category'];
    final seller = json['seller'];
    final brand = json['brand'];

    final brandNameStr = brand != null && brand['name'] != null
        ? brand['name'].toString()
        : json['brand_name']?.toString() ?? '';

    final skuStr = json['sku']?.toString() ?? json['product_code']?.toString() ?? '';
    final shortDescStr = json['short_description']?.toString() ??
        json['short_desc']?.toString() ??
        json['shortDescription']?.toString() ??
        json['meta_description']?.toString() ??
        json['brief_description']?.toString() ??
        json['summary']?.toString() ??
        json['excerpt']?.toString() ??
        json['subtitle']?.toString() ??
        json['overview']?.toString() ??
        '';

    final descStr = json['description']?.toString() ??
        json['body']?.toString() ??
        json['details']?.toString() ??
        json['content']?.toString() ??
        '';

    final mainImageCandidates = [
      json['image'],
      json['image_url'],
      json['primary_image_url'],
      json['primary_image'],
      json['featured_image'],
      json['thumbnail'],
      json['cover_image'],
      json['main_image'],
      json['photo'],
      json['images'],
      json['gallery'],
    ];

    String formattedMainImage = '';
    for (final cand in mainImageCandidates) {
      final extracted = extractSingleImageUrl(cand);
      if (extracted.isNotEmpty) {
        formattedMainImage = extracted;
        break;
      }
    }

    final pid = json['id']?.toString() ?? 'prod';
    if (formattedMainImage.isEmpty) {
      formattedMainImage = 'https://picsum.photos/seed/$pid/600/600';
    }

    final List<String> galleryList = [];

    void extractGalleryItem(dynamic item) {
      if (item == null) return;
      if (item is String) {
        final trimmed = item.trim();
        if (trimmed.isEmpty) return;
        if (trimmed.startsWith('[') && trimmed.endsWith(']')) {
          try {
            final decoded = jsonDecode(trimmed);
            if (decoded is List) {
              for (final subItem in decoded) {
                extractGalleryItem(subItem);
              }
              return;
            }
          } catch (_) {}
        }
        if (trimmed.contains(',') && !trimmed.startsWith('http')) {
          final parts = trimmed.split(',');
          for (final p in parts) {
            extractGalleryItem(p);
          }
          return;
        }
        final formatted = formatImageUrl(trimmed);
        if (formatted.isNotEmpty && !galleryList.contains(formatted)) {
          galleryList.add(formatted);
        }
      } else if (item is Map) {
        final path = item['path']?.toString() ??
            item['url']?.toString() ??
            item['original_url']?.toString() ??
            item['full_url']?.toString() ??
            item['image']?.toString() ??
            item['src']?.toString();
        if (path != null) {
          final formatted = formatImageUrl(path);
          if (formatted.isNotEmpty && !galleryList.contains(formatted)) {
            galleryList.add(formatted);
          }
        } else if (item.containsKey('data')) {
          extractGalleryItem(item['data']);
        }
      } else if (item is List) {
        for (final subItem in item) {
          extractGalleryItem(subItem);
        }
      }
    }

    final galleryCandidates = [
      json['gallery'],
      json['images'],
      json['gallery_images'],
      json['product_images'],
      json['media'],
    ];

    for (final cand in galleryCandidates) {
      extractGalleryItem(cand);
    }

    if (galleryList.isEmpty && formattedMainImage.isNotEmpty) {
      galleryList.add(formattedMainImage);
    } else if (formattedMainImage.isNotEmpty && !galleryList.contains(formattedMainImage)) {
      galleryList.insert(0, formattedMainImage);
    }

    final rawReviews = json['reviews'] ?? json['approved_reviews'] ?? json['approvedReviews'];
    final List<ReviewItem> reviewsList = [];
    if (rawReviews is List) {
      for (final item in rawReviews) {
        if (item is Map<String, dynamic>) {
          reviewsList.add(ReviewItem.fromJson(item));
        }
      }
    }

    final Map<String, String> specsMap = {};
    final rawSpecs = json['specs'] ?? json['specifications'] ?? json['attributes'] ?? json['features'] ?? json['spec'];
    if (rawSpecs is Map) {
      rawSpecs.forEach((key, value) {
        if (key != null && value != null) {
          specsMap[key.toString()] = value.toString();
        }
      });
    } else if (rawSpecs is List) {
      for (final item in rawSpecs) {
        if (item is Map) {
          final k = item['name']?.toString() ?? item['key']?.toString() ?? item['label']?.toString() ?? item['attribute']?.toString();
          final v = item['value']?.toString() ?? item['content']?.toString() ?? item['val']?.toString();
          if (k != null && k.isNotEmpty && v != null && v.isNotEmpty) {
            specsMap[k] = v;
          }
        }
      }
    }

    final sellerRaw = json['seller'] ?? json['store'] ?? json['vendor'] ?? json['user'] ?? json['created_by'];
    Seller? parsedSeller;
    if (sellerRaw is Map<String, dynamic>) {
      parsedSeller = Seller.fromJson(sellerRaw);
    } else if (sellerRaw is Map) {
      parsedSeller = Seller.fromJson(Map<String, dynamic>.from(sellerRaw));
    }

    final sellerIdStr = parsedSeller?.id.isNotEmpty == true
        ? parsedSeller!.id
        : (seller != null && seller['id'] != null ? seller['id'].toString() : '');

    return Product(
      id: json['id']?.toString() ?? '',
      title: json['title'] ?? '',
      slug: json['slug'] ?? '',
      brandName: brandNameStr,
      sku: skuStr,
      shortDescription: shortDescStr,
      imageUrl: formattedMainImage,
      price: (json['price'] is num) ? (json['price'] as num).toDouble() : 0.0,
      compareAtPrice: json['compare_price'] != null ? (json['compare_price'] as num).toDouble() : null,
      categoryId: cat != null && cat['id'] != null ? cat['id'].toString() : '',
      sellerId: sellerIdStr,
      seller: parsedSeller,
      rating: (json['rating'] is num) ? (json['rating'] as num).toDouble() : 0.0,
      reviewCount: (json['reviews_count'] is num) ? (json['reviews_count'] as num).toInt() : reviewsList.length,
      stock: (json['stock'] is num) ? (json['stock'] as num).toInt() : 0,
      gallery: galleryList,
      reviews: reviewsList,
      description: descStr,
      specs: specsMap,
      isVerified: true,
    );
  }
}
