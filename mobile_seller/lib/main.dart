import 'dart:io';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'core/network/app_http_overrides.dart';
import 'core/theme/app_theme.dart';
import 'providers/seller_auth_provider.dart';
import 'providers/seller_dashboard_provider.dart';
import 'providers/seller_order_provider.dart';
import 'providers/seller_product_provider.dart';
import 'providers/seller_wallet_provider.dart';
import 'screens/splash/splash_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  HttpOverrides.global = AppHttpOverrides();
  runApp(const OpenboxSellerApp());
}

class OpenboxSellerApp extends StatelessWidget {
  static final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

  const OpenboxSellerApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => SellerAuthProvider()),
        ChangeNotifierProvider(create: (_) => SellerDashboardProvider()),
        ChangeNotifierProvider(create: (_) => SellerOrderProvider()),
        ChangeNotifierProvider(create: (_) => SellerProductProvider()),
        ChangeNotifierProvider(create: (_) => SellerWalletProvider()),
      ],
      child: MaterialApp(
        navigatorKey: navigatorKey,
        title: 'Openbox Seller',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light(),
        home: const SplashScreen(),
      ),
    );
  }
}
