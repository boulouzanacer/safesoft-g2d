import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:badges/badges.dart' as badges;

import '../../l10n/app_i18n.dart';
import '../../providers/cart_provider.dart';
import '../../providers/notification_provider.dart';

class HomeShell extends ConsumerWidget {
  final Widget child;

  const HomeShell({super.key, required this.child});

  int _locationToIndex(String location) {
    if (location.startsWith('/home/accueil')) return 0;
    if (location.startsWith('/home/commandes')) return 0;
    if (location.startsWith('/home/produits')) return 1;
    if (location.startsWith('/home/panier')) return 2;
    if (location.startsWith('/home/profil')) return 3;
    return 0;
  }

  void _onTap(BuildContext context, int index) {
    switch (index) {
      case 0:
        context.go('/home/accueil');
        return;
      case 1:
        context.go('/home/produits');
        return;
      case 2:
        context.go('/home/panier');
        return;
      case 3:
        context.go('/home/profil');
        return;
    }
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final location = GoRouterState.of(context).uri.toString();
    final index = _locationToIndex(location);
    final cartCount =
        ref.watch(cartProvider).fold<int>(0, (sum, e) => sum + e.quantite);
    final notifCount = ref.watch(notificationProvider).nonLues;

    return Scaffold(
      body: child,
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: index,
        onTap: (i) => _onTap(context, i),
        type: BottomNavigationBarType.fixed,
        items: [
          BottomNavigationBarItem(
            icon: const Icon(Icons.home_outlined),
            label: context.tr('Accueil'),
          ),
          BottomNavigationBarItem(
            icon: const Icon(Icons.storefront_outlined),
            label: context.tr('Produits'),
          ),
          BottomNavigationBarItem(
            icon: TweenAnimationBuilder<double>(
              key: ValueKey(cartCount),
              tween: Tween(begin: 1.25, end: 1),
              duration: const Duration(milliseconds: 220),
              curve: Curves.easeOutBack,
              builder: (_, scale, child) =>
                  Transform.scale(scale: scale, child: child),
              child: cartCount <= 0
                  ? const Icon(Icons.shopping_cart_outlined)
                  : badges.Badge(
                      badgeContent: Text(
                        '$cartCount',
                        style:
                            const TextStyle(color: Colors.white, fontSize: 10),
                      ),
                      child: const Icon(Icons.shopping_cart_outlined),
                    ),
            ),
            label: context.tr('Panier'),
          ),
          BottomNavigationBarItem(
            icon: notifCount <= 0
                ? const Icon(Icons.person_outline)
                : badges.Badge(
                    badgeContent: Text(
                      '$notifCount',
                      style: const TextStyle(color: Colors.white, fontSize: 10),
                    ),
                    child: const Icon(Icons.person_outline),
                  ),
            label: context.tr('Profil'),
          ),
        ],
      ),
    );
  }
}
