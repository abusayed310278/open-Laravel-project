import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/theme/app_colors.dart';
import '../../core/widgets/app_logo.dart';
import '../../navigation/app_shell.dart';
import '../../providers/auth_provider.dart';
import '../onboarding/onboarding_screen.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _checkAuthAndNavigate();
    });
  }

  Future<void> _checkAuthAndNavigate() async {
    final startTime = DateTime.now();
    final authProvider = context.read<AuthProvider>();

    if (authProvider.state == AuthState.loading || authProvider.state == AuthState.uninitialized) {
      await authProvider.checkAuthStatus();
    }

    final elapsed = DateTime.now().difference(startTime).inMilliseconds;
    if (elapsed < 1200) {
      await Future.delayed(Duration(milliseconds: 1200 - elapsed));
    }

    if (!mounted) return;

    if (authProvider.isAuthenticated) {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const AppShell()),
      );
    } else {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const OnboardingScreen()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.brand500,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const AppLogo(size: 84, borderRadius: 20),
            const SizedBox(height: 20),
            const Text(
              'Openbox',
              style: TextStyle(color: Colors.white, fontSize: 28, fontWeight: FontWeight.w800, letterSpacing: 0.2),
            ),
            const SizedBox(height: 6),
            const Text(
              'Verified. Graded. Trusted.',
              style: TextStyle(color: Colors.white70, fontSize: 13, fontWeight: FontWeight.w500),
            ),
          ],
        ),
      ),
    );
  }
}
