import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../core/network/api_config.dart';
import '../../core/widgets/app_logo.dart';
import '../../core/theme/app_colors.dart';
import '../../core/theme/app_spacing.dart';
import '../navigation/main_navigation_screen.dart';

class _OnboardPage {
  const _OnboardPage({
    required this.imageUrl,
    required this.fallbackIcon,
    required this.title,
    required this.body,
  });

  final String imageUrl;
  final IconData fallbackIcon;
  final String title;
  final String body;
}

final _pages = [
  _OnboardPage(
    imageUrl: '${ApiConfig.backendHost}/storage/branding/5chHVsfI4FhME3cNIsVRtl2KbCOciMOr4tzAK8ln.png',
    fallbackIcon: Icons.verified_rounded,
    title: 'Every item, physically verified',
    body: 'Openbox inspectors check every refurbished product across 40+ checkpoints before it reaches you.',
  ),
  _OnboardPage(
    imageUrl: '${ApiConfig.backendHost}/storage/branding/GMHl4m8eVhHCvszz6mc7yFHpsg7iwaqzbY0X5UjS.png',
    fallbackIcon: Icons.grade_rounded,
    title: 'Transparent condition grading',
    body: 'Grade A, B, or C - know exactly what you\'re buying, with battery health and cosmetic condition disclosed upfront.',
  ),
  _OnboardPage(
    imageUrl: '${ApiConfig.backendHost}/buy-and-sale.png',
    fallbackIcon: Icons.storefront_rounded,
    title: 'Shop new & refurbished, one place',
    body: 'Browse trusted businesses, individual sellers, and Openbox-warehoused certified deals side by side.',
  ),
];

class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  final _controller = PageController();
  int _page = 0;

  Future<void> _finish() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setBool('has_seen_onboarding', true);
    } catch (_) {}
    if (!mounted) return;
    // After onboarding screen, go straight to MainNavigationScreen (guest mode)
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isLast = _page == _pages.length - 1;
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: AppSpacing.lg, vertical: AppSpacing.xs),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      const AppLogo(size: 34, borderRadius: 10),
                      const SizedBox(width: 8),
                      const Text(
                        'Openbox',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: AppColors.ink, letterSpacing: -0.2),
                      ),
                    ],
                  ),
                  TextButton(
                    onPressed: _finish,
                    child: const Text('Skip', style: TextStyle(color: AppColors.slate500, fontWeight: FontWeight.w600)),
                  ),
                ],
              ),
            ),
            Expanded(
              child: PageView.builder(
                controller: _controller,
                itemCount: _pages.length,
                onPageChanged: (i) => setState(() => _page = i),
                itemBuilder: (context, i) {
                  final page = _pages[i];
                  return Padding(
                    padding: const EdgeInsets.symmetric(horizontal: AppSpacing.xxxl),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Container(
                          width: 160,
                          height: 160,
                          decoration: const BoxDecoration(color: AppColors.brand50, shape: BoxShape.circle),
                          clipBehavior: Clip.antiAlias,
                          child: Padding(
                            padding: const EdgeInsets.all(AppSpacing.md),
                            child: Image.network(
                              page.imageUrl,
                              fit: BoxFit.contain,
                              errorBuilder: (context, error, stackTrace) => const Center(
                                child: Icon(Icons.storefront_rounded, size: 60, color: AppColors.brand600),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: AppSpacing.xxxl),
                        Text(
                          page.title,
                          textAlign: TextAlign.center,
                          style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppColors.ink),
                        ),
                        const SizedBox(height: AppSpacing.md),
                        Text(
                          page.body,
                          textAlign: TextAlign.center,
                          style: const TextStyle(fontSize: 14, color: AppColors.slate500, height: 1.5),
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(
                _pages.length,
                (i) => AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  margin: const EdgeInsets.symmetric(horizontal: 4),
                  width: i == _page ? 22 : 8,
                  height: 8,
                  decoration: BoxDecoration(
                    color: i == _page ? AppColors.brand500 : AppColors.slate200,
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(AppSpacing.xxl),
              child: SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.brand500,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
                  ),
                  onPressed: () {
                    if (isLast) {
                      _finish();
                    } else {
                      _controller.nextPage(duration: const Duration(milliseconds: 250), curve: Curves.easeOut);
                    }
                  },
                  child: Text(isLast ? 'Get started' : 'Next', style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
