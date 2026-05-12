import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../core/theme/app_colors.dart';
import '../../core/utils/storage_service.dart';
import '../../widgets/common/gradient_button.dart';

class OnboardingScreen extends StatelessWidget {
  const OnboardingScreen({super.key});

  static final StorageService _storage = StorageService();

  @override
  Widget build(BuildContext context) {
    return _OnboardingBody(storage: _storage);
  }
}

class _OnboardingBody extends StatefulWidget {
  final StorageService storage;

  const _OnboardingBody({required this.storage});

  @override
  State<_OnboardingBody> createState() => _OnboardingBodyState();
}

class _OnboardingBodyState extends State<_OnboardingBody> {
  final PageController _controller = PageController();
  int _index = 0;
  String? _typeClient;

  Future<void> _next() async {
    if (_index == 1 && _typeClient != 'simple') return;
    if (_index >= 2) return;
    await _controller.nextPage(
        duration: const Duration(milliseconds: 300), curve: Curves.easeInOut);
  }

  Widget _dot(bool active) {
    return AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      height: 8,
      width: active ? 22 : 8,
      margin: const EdgeInsets.symmetric(horizontal: 4),
      decoration: BoxDecoration(
        color: active ? AppColors.primary : Colors.white24,
        borderRadius: BorderRadius.circular(10),
      ),
    );
  }

  Future<void> _goLogin() async {
    await widget.storage.setOnboardingSeen(true);
    if (!mounted) return;
    context.go('/login');
  }

  Future<void> _goRegister() async {
    await widget.storage.setOnboardingSeen(true);
    if (!mounted) return;
    context.go('/register');
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [AppColors.bgDark, AppColors.primary],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              children: [
                Expanded(
                  child: PageView(
                    controller: _controller,
                    onPageChanged: (i) => setState(() => _index = i),
                    children: [
                      const _Page1(),
                      _Page2(
                        typeClient: _typeClient,
                        onSelectSimple: () {
                          setState(() => _typeClient = 'simple');
                          _next();
                        },
                        onLoginAbonne: _goLogin,
                      ),
                      _Page3(
                        visible: _typeClient == 'simple',
                        onRegister: _goRegister,
                        onLogin: _goLogin,
                      ),
                    ],
                  ),
                ),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    _dot(_index == 0),
                    _dot(_index == 1),
                    _dot(_index == 2),
                  ],
                ),
                const SizedBox(height: 14),
                SizedBox(
                  width: double.infinity,
                  child: GradientButton(
                    onPressed: _index == 2 ? null : _next,
                    child: const Text('Suivant'),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _Page1 extends StatelessWidget {
  const _Page1();

  @override
  Widget build(BuildContext context) {
    return const Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Icon(Icons.storefront, size: 86, color: Colors.white),
        SizedBox(height: 16),
        Text('Bienvenue sur SafeSoft G2D',
            textAlign: TextAlign.center,
            style: TextStyle(
                color: Colors.white,
                fontSize: 24,
                fontWeight: FontWeight.w800)),
        SizedBox(height: 10),
        Padding(
          padding: EdgeInsets.symmetric(horizontal: 20),
          child: Text(
            'Découvrez des centaines de produits de fournisseurs locaux',
            textAlign: TextAlign.center,
            style: TextStyle(color: Colors.white70, fontSize: 14),
          ),
        ),
      ],
    );
  }
}

class _Page2 extends StatelessWidget {
  final String? typeClient;
  final VoidCallback onSelectSimple;
  final VoidCallback onLoginAbonne;

  const _Page2({
    required this.typeClient,
    required this.onSelectSimple,
    required this.onLoginAbonne,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        const Text('Choisir type client',
            style: TextStyle(
                color: Colors.white,
                fontSize: 20,
                fontWeight: FontWeight.w800)),
        const SizedBox(height: 16),
        Row(
          children: [
            Expanded(
              child: _ChoiceCard(
                title: 'Client Simple',
                icon: Icons.shopping_bag_outlined,
                description: 'Parcourez toutes les boutiques librement',
                buttonText: 'Continuer',
                onPressed: onSelectSimple,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: _ChoiceCard(
                title: 'Abonné',
                icon: Icons.key_outlined,
                description: 'Vous avez un compte chez un fournisseur',
                buttonText: 'Se connecter',
                onPressed: onLoginAbonne,
                accent: AppColors.primary,
              ),
            ),
          ],
        ),
      ],
    );
  }
}

class _ChoiceCard extends StatelessWidget {
  final String title;
  final IconData icon;
  final String description;
  final String buttonText;
  final VoidCallback onPressed;
  final Color? accent;

  const _ChoiceCard({
    required this.title,
    required this.icon,
    required this.description,
    required this.buttonText,
    required this.onPressed,
    this.accent,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.10),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.white12),
      ),
      child: Column(
        children: [
          Icon(icon, size: 44, color: Colors.white),
          const SizedBox(height: 10),
          Text(title,
              style: const TextStyle(
                  color: Colors.white, fontWeight: FontWeight.w800)),
          const SizedBox(height: 8),
          Text(description,
              textAlign: TextAlign.center,
              style: const TextStyle(color: Colors.white70, fontSize: 12)),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: onPressed,
              style: ElevatedButton.styleFrom(
                backgroundColor: accent ?? Colors.white.withValues(alpha: 0.15),
                foregroundColor: Colors.white,
              ),
              child: Text(buttonText),
            ),
          ),
        ],
      ),
    );
  }
}

class _Page3 extends StatelessWidget {
  final bool visible;
  final VoidCallback onRegister;
  final VoidCallback onLogin;

  const _Page3({
    required this.visible,
    required this.onRegister,
    required this.onLogin,
  });

  @override
  Widget build(BuildContext context) {
    if (!visible) {
      return const Center(
        child: Text('Sélectionnez "Client Simple" pour continuer.',
            style: TextStyle(color: Colors.white70)),
      );
    }

    return Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        const Icon(Icons.person_add_alt_1_outlined,
            size: 86, color: Colors.white),
        const SizedBox(height: 16),
        const Text(
          'Créez votre compte pour passer des commandes',
          textAlign: TextAlign.center,
          style: TextStyle(
              color: Colors.white, fontSize: 20, fontWeight: FontWeight.w800),
        ),
        const SizedBox(height: 18),
        SizedBox(
            width: double.infinity,
            child: GradientButton(
                onPressed: onRegister, child: const Text('Créer un compte'))),
        const SizedBox(height: 10),
        TextButton(
          onPressed: onLogin,
          child: const Text("J'ai déjà un compte",
              style: TextStyle(color: Colors.white70)),
        ),
      ],
    );
  }
}
