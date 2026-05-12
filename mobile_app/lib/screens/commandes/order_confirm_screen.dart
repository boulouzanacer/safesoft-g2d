import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:lottie/lottie.dart';

class OrderConfirmScreen extends StatelessWidget {
  final int id;

  const OrderConfirmScreen({super.key, required this.id});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            SizedBox(
              height: 160,
              child: Lottie.network(
                'https://assets10.lottiefiles.com/packages/lf20_jbrw3hcz.json',
                repeat: false,
                errorBuilder: (_, __, ___) => const Icon(
                  Icons.check_circle_outline,
                  size: 96,
                  color: Colors.green,
                ),
              ),
            ),
            const SizedBox(height: 12),
            const Text(
              'Commande confirmée !',
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900),
            ),
            const SizedBox(height: 8),
            Text(
              'N° commande : #$id',
              style: const TextStyle(fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 10),
            Text(
              'Votre fournisseur a été notifié de votre commande',
              textAlign: TextAlign.center,
              style: TextStyle(
                color: Theme.of(context)
                    .textTheme
                    .bodySmall
                    ?.color
                    ?.withValues(alpha: 0.7),
              ),
            ),
            const SizedBox(height: 18),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () => context.go('/home/commandes'),
                child: const Text('Voir mes commandes'),
              ),
            ),
            const SizedBox(height: 10),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton(
                onPressed: () => context.go('/home/produits'),
                child: const Text('Continuer mes achats'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
