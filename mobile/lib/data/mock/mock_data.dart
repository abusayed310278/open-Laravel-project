import 'package:flutter/material.dart';

import '../models/address.dart';
import '../models/app_notification.dart';
import '../models/category.dart';
import '../models/chat_conversation.dart';
import '../models/order.dart';
import '../models/product.dart';
import '../models/review.dart';
import '../models/seller.dart';

/// Static sample data standing in for real API responses so every screen
/// can be designed and previewed without a backend integration.
abstract final class MockData {
  static const categories = <ProductCategory>[
    ProductCategory(id: 'phones', name: 'Phones', icon: Icons.smartphone_outlined),
    ProductCategory(id: 'laptops', name: 'Laptops', icon: Icons.laptop_mac_outlined),
    ProductCategory(id: 'tablets', name: 'Tablets', icon: Icons.tablet_mac_outlined),
    ProductCategory(id: 'watches', name: 'Watches', icon: Icons.watch_outlined),
    ProductCategory(id: 'audio', name: 'Audio', icon: Icons.headphones_outlined),
    ProductCategory(id: 'gaming', name: 'Gaming', icon: Icons.sports_esports_outlined),
    ProductCategory(id: 'cameras', name: 'Cameras', icon: Icons.camera_alt_outlined),
    ProductCategory(id: 'dateup', name: 'DateUp', icon: Icons.data_usage_outlined),
    ProductCategory(id: 'more', name: 'More', icon: Icons.grid_view_rounded),
  ];

  static const sellers = <Seller>[
    Seller(
      id: 'openbox',
      name: 'Openbox Certified',
      type: SellerType.openbox,
      avatarInitial: 'OB',
      isVerified: true,
      rating: 4.9,
      reviewCount: 1240,
      city: 'Doha',
      productCount: 320,
      memberSince: '2022',
    ),
    Seller(
      id: 'techhub',
      name: 'TechHub Electronics',
      type: SellerType.business,
      avatarInitial: 'TH',
      isVerified: true,
      rating: 4.7,
      reviewCount: 512,
      city: 'Doha',
      productCount: 148,
      memberSince: '2023',
    ),
    Seller(
      id: 'ahmed',
      name: 'Ahmed K.',
      type: SellerType.saler,
      avatarInitial: 'AK',
      isVerified: false,
      rating: 4.4,
      reviewCount: 37,
      city: 'Al Rayyan',
      productCount: 9,
      memberSince: '2024',
    ),
  ];

  static const products = <Product>[
    Product(
      id: 'p1',
      title: 'iPhone 14 Pro 128GB — Deep Purple',
      imageUrl: 'https://picsum.photos/seed/p1/600/600',
      price: 2699,
      compareAtPrice: 3299,
      categoryId: 'phones',
      sellerId: 'openbox',
      grade: ProductGrade.a,
      isVerified: true,
      isWarehoused: true,
      rating: 4.8,
      reviewCount: 96,
      gallery: [
        'https://picsum.photos/seed/p1/600/600',
        'https://picsum.photos/seed/p1b/600/600',
        'https://picsum.photos/seed/p1c/600/600',
      ],
      shortDescription: '• 48MP Main Camera with Quad-Pixel Sensor\n• Dynamic Island & Always-On Super Retina XDR Display\n• A16 Bionic Chip with 6-core CPU\n• All-day battery life with certified 90%+ health',
      description:
          'Certified refurbished iPhone 14 Pro, fully tested across 40+ checkpoints by an Openbox verifier. Battery health above 90%.',
      specs: {
        'Storage': '128GB',
        'Color': 'Deep Purple',
        'Battery Health': '92%',
        'Condition': 'Grade A · Like New',
      },
    ),
    Product(
      id: 'p2',
      title: 'MacBook Air M2 13" 256GB',
      imageUrl: 'https://picsum.photos/seed/p2/600/600',
      price: 3899,
      compareAtPrice: 4499,
      categoryId: 'laptops',
      sellerId: 'openbox',
      grade: ProductGrade.a,
      isVerified: true,
      isWarehoused: true,
      rating: 4.9,
      reviewCount: 64,
      gallery: ['https://picsum.photos/seed/p2/600/600', 'https://picsum.photos/seed/p2b/600/600'],
      shortDescription: '• Apple M2 chip with 8-core CPU & 8-core GPU\n• 13.6-inch Liquid Retina display with True Tone\n• 8GB unified memory, 256GB SSD storage\n• 1080p FaceTime HD camera & 4-speaker sound',
      description: 'Space Gray MacBook Air M2, verified and grade-A certified. Includes original charger.',
      specs: {'Chip': 'Apple M2', 'RAM': '8GB', 'Storage': '256GB SSD', 'Condition': 'Grade A · Like New'},
    ),
    Product(
      id: 'p3',
      title: 'Samsung Galaxy Watch 6 44mm',
      imageUrl: 'https://picsum.photos/seed/p3/600/600',
      price: 899,
      categoryId: 'watches',
      sellerId: 'techhub',
      isNew: true,
      rating: 4.6,
      reviewCount: 28,
      gallery: ['https://picsum.photos/seed/p3/600/600'],
      shortDescription: '• Advanced sleep tracking & personalized HR zones\n• Sapphire crystal glass with 20% larger display\n• Body Composition Analysis & BIA sensor',
      description: 'Brand new, sealed box. 1-year manufacturer warranty via TechHub Electronics.',
      specs: {'Case size': '44mm', 'Color': 'Graphite', 'Connectivity': 'Bluetooth'},
    ),
    Product(
      id: 'p4',
      title: 'Sony WH-1000XM5 Wireless Headphones',
      imageUrl: 'https://picsum.photos/seed/p4/600/600',
      price: 1099,
      compareAtPrice: 1399,
      categoryId: 'audio',
      sellerId: 'techhub',
      isNew: true,
      rating: 4.8,
      reviewCount: 152,
      gallery: ['https://picsum.photos/seed/p4/600/600'],
      shortDescription: '• Auto NC Optimizer with 8 microphones & 2 processors\n• Ultra-clear hands-free calling with Precise Voice Pickup\n• Up to 30-hour battery life with quick charging',
      description: 'Industry-leading noise cancellation. Brand new, sealed.',
      specs: {'Type': 'Over-ear', 'Battery life': '30 hrs', 'Color': 'Black'},
    ),
    Product(
      id: 'p5',
      title: 'iPad Air 5th Gen 64GB Wi-Fi',
      imageUrl: 'https://picsum.photos/seed/p5/600/600',
      price: 2199,
      compareAtPrice: 2599,
      categoryId: 'tablets',
      sellerId: 'openbox',
      grade: ProductGrade.b,
      isVerified: true,
      isWarehoused: true,
      rating: 4.5,
      reviewCount: 41,
      gallery: ['https://picsum.photos/seed/p5/600/600'],
      shortDescription: '• Apple M1 chip with Neural Engine\n• 10.9-inch Liquid Retina display with True Tone\n• 12MP Ultra Wide front camera with Center Stage',
      description: 'Light cosmetic marks, fully functional and battery-tested above 85%.',
      specs: {'Storage': '64GB', 'Color': 'Space Gray', 'Condition': 'Grade B · Good'},
    ),
    Product(
      id: 'p6',
      title: 'PlayStation 5 Slim Console',
      imageUrl: 'https://picsum.photos/seed/p6/600/600',
      price: 2299,
      categoryId: 'gaming',
      sellerId: 'ahmed',
      rating: 4.7,
      reviewCount: 19,
      gallery: ['https://picsum.photos/seed/p6/600/600'],
      shortDescription: '• Ultra-high speed 1TB SSD storage\n• 4K-TV gaming with up to 120fps output\n• Tempest 3D AudioTech & DualSense haptic feedback',
      description: 'Used for 3 months, excellent condition, all original accessories included.',
      specs: {'Storage': '1TB', 'Edition': 'Disc'},
    ),
    Product(
      id: 'p7',
      title: 'Canon EOS R50 Mirrorless Camera',
      imageUrl: 'https://picsum.photos/seed/p7/600/600',
      price: 3299,
      compareAtPrice: 3699,
      categoryId: 'cameras',
      sellerId: 'techhub',
      isNew: true,
      rating: 4.6,
      reviewCount: 12,
      gallery: ['https://picsum.photos/seed/p7/600/600'],
      shortDescription: '• 24.2 Megapixel CMOS (APS-C) Sensor\n• Uncropped 4K video at up to 30 fps oversampled from 6K\n• Dual Pixel CMOS AF II with Subject Detection',
      description: 'Brand new with 18-45mm kit lens. Full manufacturer warranty.',
      specs: {'Sensor': 'APS-C 24.2MP', 'Video': '4K 30fps'},
    ),
    Product(
      id: 'p8',
      title: 'Samsung Galaxy S23 256GB',
      imageUrl: 'https://picsum.photos/seed/p8/600/600',
      price: 2399,
      compareAtPrice: 2899,
      categoryId: 'phones',
      sellerId: 'openbox',
      grade: ProductGrade.a,
      isVerified: true,
      isWarehoused: true,
      rating: 4.7,
      reviewCount: 58,
      gallery: ['https://picsum.photos/seed/p8/600/600'],
      description: 'Certified refurbished, like-new condition, 92% battery health.',
      specs: {'Storage': '256GB', 'Color': 'Phantom Black', 'Condition': 'Grade A · Like New'},
    ),
    Product(
      id: 'd1',
      title: 'DateUp 50GB Monthly Data Pass',
      imageUrl: 'https://picsum.photos/seed/dateup1/600/600',
      price: 149,
      compareAtPrice: 199,
      categoryId: 'dateup',
      sellerId: 'openbox',
      isVerified: true,
      rating: 4.9,
      reviewCount: 42,
      gallery: ['https://picsum.photos/seed/dateup1/600/600'],
      shortDescription: '• 50GB High-Speed 5G Data\n• Valid for 30 Days from activation\n• Instant Digital Activation & eSIM ready',
      description: 'DateUp 50GB monthly data package. Active validity starting from activation date with full speed 5G coverage.',
      specs: {
        'Data Allowance': '50 GB',
        'Validity': '30 Days',
        'Activation Date': '28 Sep 2026',
        'Expiry Date': '28 Oct 2026',
        'Network': '5G / LTE',
      },
    ),
    Product(
      id: 'd2',
      title: 'DateUp Unlimited 5G Pass (7 Days)',
      imageUrl: 'https://picsum.photos/seed/dateup2/600/600',
      price: 79,
      compareAtPrice: 99,
      categoryId: 'dateup',
      sellerId: 'techhub',
      isNew: true,
      rating: 4.8,
      reviewCount: 35,
      gallery: ['https://picsum.photos/seed/dateup2/600/600'],
      shortDescription: '• Unlimited 5G Data for 7 days\n• No speed throttling\n• Hotspot sharing enabled',
      description: '7-day unlimited DateUp data package with unthrottled high-speed 5G network access.',
      specs: {
        'Data Allowance': 'Unlimited',
        'Validity': '7 Days',
        'Activation Date': '28 Sep 2026',
        'Expiry Date': '05 Oct 2026',
        'Network': '5G Ultra',
      },
    ),
    Product(
      id: 'd3',
      title: 'DateUp Global eSIM Roaming 15GB',
      imageUrl: 'https://picsum.photos/seed/dateup3/600/600',
      price: 199,
      categoryId: 'dateup',
      sellerId: 'openbox',
      isVerified: true,
      rating: 4.7,
      reviewCount: 19,
      gallery: ['https://picsum.photos/seed/dateup3/600/600'],
      shortDescription: '• 15GB Global Data in 120+ countries\n• Valid for 15 Days\n• QR Code instant installation',
      description: 'Travel hassle-free with DateUp Global eSIM. Instant QR delivery and auto-activation upon arrival.',
      specs: {
        'Data Allowance': '15 GB',
        'Coverage': '120+ Countries',
        'Validity': '15 Days',
        'Activation Date': '28 Sep 2026',
        'Expiry Date': '13 Oct 2026',
      },
    ),
    Product(
      id: 'd4',
      title: 'DateUp Annual Starter Pass 100GB',
      imageUrl: 'https://picsum.photos/seed/dateup4/600/600',
      price: 399,
      compareAtPrice: 499,
      categoryId: 'dateup',
      sellerId: 'techhub',
      isVerified: true,
      rating: 4.9,
      reviewCount: 68,
      gallery: ['https://picsum.photos/seed/dateup4/600/600'],
      shortDescription: '• 100GB rollover data valid for 365 days\n• Auto date extension upon top-up\n• Dual SIM & Tablet compatible',
      description: 'Long validity DateUp annual package. Use data at your own pace for a full year with automated date extensions.',
      specs: {
        'Data Allowance': '100 GB',
        'Validity': '365 Days',
        'Activation Date': '28 Sep 2026',
        'Expiry Date': '28 Sep 2027',
      },
    ),
  ];

  static List<Product> get featured => products.where((p) => p.isWarehoused).toList();
  static List<Product> get dealsOfTheDay => products.where((p) => p.discountPercent != null).toList();

  static List<Product> getProductsForCategory(String categoryId, String categoryName) {
    final cleanId = categoryId.toLowerCase().trim();
    return products.where((p) => p.categoryId.toLowerCase() == cleanId).toList();
  }

  static Seller sellerFor(String id) => sellers.firstWhere((s) => s.id == id, orElse: () => sellers.first);

  static const reviews = <Review>[
    Review(author: 'Fatima R.', rating: 5, comment: 'Exactly as described, arrived fast and well packaged.', time: '2 weeks ago', avatarInitial: 'F'),
    Review(author: 'Omar S.', rating: 4, comment: 'Great condition for a refurbished unit, battery lasts all day.', time: '1 month ago', avatarInitial: 'O'),
    Review(author: 'Layla M.', rating: 5, comment: 'Seller was responsive and the grading was accurate.', time: '1 month ago', avatarInitial: 'L'),
  ];

  static final addresses = <Address>[
    const Address(id: 'a1', label: 'Home', recipient: 'Sarah Ahmed', line1: 'Zone 45, Street 12, Villa 7', city: 'Doha, Qatar', phone: '+974 5555 1234', isDefault: true),
    const Address(id: 'a2', label: 'Office', recipient: 'Sarah Ahmed', line1: 'West Bay Tower, Floor 14', city: 'Doha, Qatar', phone: '+974 5555 1234'),
  ];

  static final orders = <Order>[
    Order(
      id: 'OB-10234',
      placedAt: DateTime(2026, 9, 20),
      status: OrderStatus.outForDelivery,
      total: 2699,
      trackingNumber: 'OB-TRK-88213',
      items: const [
        OrderItem(productTitle: 'iPhone 14 Pro 128GB — Deep Purple', imageUrl: 'https://picsum.photos/seed/p1/200/200', price: 2699, quantity: 1, sellerName: 'Openbox Certified'),
      ],
    ),
    Order(
      id: 'OB-10198',
      placedAt: DateTime(2026, 9, 10),
      status: OrderStatus.delivered,
      total: 1099,
      items: const [
        OrderItem(productTitle: 'Sony WH-1000XM5 Wireless Headphones', imageUrl: 'https://picsum.photos/seed/p4/200/200', price: 1099, quantity: 1, sellerName: 'TechHub Electronics'),
      ],
    ),
    Order(
      id: 'OB-10052',
      placedAt: DateTime(2026, 8, 22),
      status: OrderStatus.cancelled,
      total: 899,
      items: const [
        OrderItem(productTitle: 'Samsung Galaxy Watch 6 44mm', imageUrl: 'https://picsum.photos/seed/p3/200/200', price: 899, quantity: 1, sellerName: 'TechHub Electronics'),
      ],
    ),
  ];

  static const conversations = <ChatConversation>[];

  static const notifications = <AppNotification>[
    AppNotification(id: 'n1', title: 'Order shipped', body: 'Your order OB-10234 is out for delivery.', time: '2h ago', category: NotificationCategory.order),
    AppNotification(id: 'n2', title: 'Payment verified', body: 'Your bank transfer for OB-10198 was confirmed.', time: '1d ago', category: NotificationCategory.payment, isRead: true),
    AppNotification(id: 'n3', title: 'Price drop', body: 'iPad Air 5th Gen you wishlisted dropped by 15%.', time: '2d ago', category: NotificationCategory.product),
    AppNotification(id: 'n4', title: 'Support reply', body: 'Our team replied to your ticket #4521.', time: '3d ago', category: NotificationCategory.support, isRead: true),
    AppNotification(id: 'n5', title: 'Weekend deals', body: 'Up to 30% off certified refurbished phones.', time: '5d ago', category: NotificationCategory.promo, isRead: true),
  ];
}
