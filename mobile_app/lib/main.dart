import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import 'core/network/dio_client.dart';
import 'core/theme/app_theme.dart';
import 'providers/theme_provider.dart';
import 'services/notification_service.dart';
import 'widgets/common/offline_banner.dart';
import 'screens/auth/login_screen.dart';
import 'screens/auth/register_screen.dart';
import 'screens/cart/cart_screen.dart';
import 'screens/cart/checkout_screen.dart';
import 'screens/commandes/order_confirm_screen.dart';
import 'screens/commandes/order_detail_screen.dart';
import 'screens/commandes/order_list_screen.dart';
import 'screens/home/boutique_detail_screen.dart';
import 'screens/home/home_screen.dart';
import 'screens/home/home_shell.dart';
import 'screens/notifications/notifications_screen.dart';
import 'screens/onboarding/onboarding_screen.dart';
import 'screens/produits/product_detail_screen.dart';
import 'screens/produits/product_list_screen.dart';
import 'screens/profil/change_password_screen.dart';
import 'screens/profil/edit_profile_screen.dart';
import 'screens/profil/profile_screen.dart';
import 'screens/splash/splash_screen.dart';

final GlobalKey<NavigatorState> rootNavigatorKey = GlobalKey<NavigatorState>();

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  var firebaseReady = false;
  try {
    await Firebase.initializeApp();
    firebaseReady = true;
  } catch (_) {
    firebaseReady = false;
  }
  DioClient.setNavigatorKey(rootNavigatorKey);
  NotificationService.setNavigatorKey(rootNavigatorKey);
  if (firebaseReady) {
    await NotificationService.instance.init();
  }
  runApp(const ProviderScope(child: GrosLinkApp()));
}

class GrosLinkApp extends ConsumerWidget {
  const GrosLinkApp({super.key});

  Page<dynamic> _fadePage(GoRouterState state, Widget child) {
    return CustomTransitionPage(
      key: state.pageKey,
      child: child,
      transitionsBuilder: (_, animation, __, pageChild) {
        return FadeTransition(opacity: animation, child: pageChild);
      },
    );
  }

  GoRouter _router() {
    return GoRouter(
      navigatorKey: rootNavigatorKey,
      initialLocation: '/',
      routes: [
        GoRoute(
          path: '/',
          pageBuilder: (_, state) => _fadePage(state, const SplashScreen()),
        ),
        GoRoute(
          path: '/onboarding',
          pageBuilder: (_, state) => _fadePage(state, const OnboardingScreen()),
        ),
        GoRoute(
          path: '/login',
          pageBuilder: (_, state) => _fadePage(state, const LoginScreen()),
        ),
        GoRoute(
          path: '/register',
          pageBuilder: (_, state) => _fadePage(state, const RegisterScreen()),
        ),
        GoRoute(
          path: '/home',
          redirect: (_, __) => '/home/accueil',
        ),
        ShellRoute(
          builder: (_, __, child) => HomeShell(child: child),
          routes: [
            GoRoute(
              path: '/home/accueil',
              pageBuilder: (_, state) => _fadePage(state, const HomeScreen()),
            ),
            GoRoute(
              path: '/home/produits',
              pageBuilder: (_, state) =>
                  _fadePage(state, const ProductListScreen()),
            ),
            GoRoute(
              path: '/home/panier',
              pageBuilder: (_, state) => _fadePage(state, const CartScreen()),
            ),
            GoRoute(
              path: '/home/commandes',
              pageBuilder: (_, state) =>
                  _fadePage(state, const OrderListScreen()),
            ),
            GoRoute(
              path: '/home/commandes/:id',
              pageBuilder: (_, state) => _fadePage(
                state,
                OrderDetailScreen(id: int.parse(state.pathParameters['id']!)),
              ),
            ),
            GoRoute(
              path: '/home/profil',
              pageBuilder: (_, state) =>
                  _fadePage(state, const ProfileScreen()),
            ),
          ],
        ),
        GoRoute(
          path: '/boutiques/:id',
          pageBuilder: (_, state) => _fadePage(
            state,
            BoutiqueDetailScreen(id: int.parse(state.pathParameters['id']!)),
          ),
        ),
        GoRoute(
          path: '/produits/:id',
          pageBuilder: (_, state) => _fadePage(
            state,
            ProductDetailScreen(id: int.parse(state.pathParameters['id']!)),
          ),
        ),
        GoRoute(path: '/commandes', redirect: (_, __) => '/home/commandes'),
        GoRoute(
          path: '/commandes/:id',
          redirect: (_, state) =>
              '/home/commandes/${state.pathParameters['id']}',
        ),
        GoRoute(
          path: '/checkout',
          pageBuilder: (_, state) => _fadePage(state, const CheckoutScreen()),
        ),
        GoRoute(
          path: '/order-confirm/:id',
          pageBuilder: (_, state) => _fadePage(
            state,
            OrderConfirmScreen(id: int.parse(state.pathParameters['id']!)),
          ),
        ),
        GoRoute(
          path: '/profile/edit',
          pageBuilder: (_, state) =>
              _fadePage(state, const EditProfileScreen()),
        ),
        GoRoute(
          path: '/profile/password',
          pageBuilder: (_, state) =>
              _fadePage(state, const ChangePasswordScreen()),
        ),
        GoRoute(
          path: '/notifications',
          pageBuilder: (_, state) =>
              _fadePage(state, const NotificationsScreen()),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isDark = ref.watch(themeProvider);
    final router = _router();

    return MaterialApp.router(
      title: 'GrosLink',
      theme: AppTheme.light(),
      darkTheme: AppTheme.dark(),
      themeMode: isDark ? ThemeMode.dark : ThemeMode.light,
      routerConfig: router,
      builder: (context, child) {
        return OfflineBanner(child: child ?? const SizedBox.shrink());
      },
    );
  }
}
