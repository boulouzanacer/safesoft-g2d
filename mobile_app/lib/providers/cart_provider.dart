import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../models/produit_model.dart';
import 'auth_provider.dart';

class CartItem {
  final ProduitModel produit;
  final int quantite;

  const CartItem({required this.produit, required this.quantite});

  double get prixUnitaire => produit.unitPriceForQty(quantite);
  double get sousTotal => prixUnitaire * quantite;

  CartItem copyWith({ProduitModel? produit, int? quantite}) {
    return CartItem(
      produit: produit ?? this.produit,
      quantite: quantite ?? this.quantite,
    );
  }
}

class CartNotifier extends StateNotifier<List<CartItem>> {
  CartNotifier(this._ref) : super(const []);

  final Ref _ref;

  int get totalArticles => state.fold<int>(0, (sum, e) => sum + e.quantite);
  double get montantTotal =>
      state.fold<double>(0, (sum, e) => sum + e.sousTotal);
  int? get frsId => state.isEmpty ? null : state.first.produit.idFrs;

  Future<bool> addItem(
    BuildContext context,
    ProduitModel produit, {
    int quantite = 1,
  }) async {
    final auth = _ref.read(authProvider);
    if (!auth.isAuthenticated) {
      final goLogin = await _askLogin(context);
      if (goLogin && context.mounted) {
        context.go('/login');
      }
      return false;
    }

    final currentFrsId = frsId;
    if (currentFrsId != null && currentFrsId != produit.idFrs) {
      final ok = await _confirmChangeFournisseur(
        context,
        currentNomFrs: state.first.produit.nomFrs ?? 'ce fournisseur',
        nextNomFrs: produit.nomFrs ?? 'ce fournisseur',
      );
      if (!ok) return false;
      clearCart();
    }

    final idx = state.indexWhere((e) => e.produit.id == produit.id);
    if (idx == -1) {
      state = [...state, CartItem(produit: produit, quantite: quantite)];
      return true;
    }

    final updated = [...state];
    updated[idx] =
        updated[idx].copyWith(quantite: updated[idx].quantite + quantite);
    state = updated;
    return true;
  }

  void removeItem(int produitId) {
    state = state.where((e) => e.produit.id != produitId).toList();
  }

  void updateQuantite(int produitId, int quantite) {
    if (quantite <= 0) {
      removeItem(produitId);
      return;
    }
    final idx = state.indexWhere((e) => e.produit.id == produitId);
    if (idx == -1) return;
    final updated = [...state];
    updated[idx] = updated[idx].copyWith(quantite: quantite);
    state = updated;
  }

  void clearCart() => state = const [];

  Future<bool> _askLogin(BuildContext context) async {
    final res = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Connectez-vous pour commander'),
        content: const Text(
            'Vous devez vous connecter pour ajouter des articles au panier.'),
        actions: [
          TextButton(
              onPressed: () => Navigator.of(ctx).pop(false),
              child: const Text('Annuler')),
          ElevatedButton(
              onPressed: () => Navigator.of(ctx).pop(true),
              child: const Text('Se connecter')),
        ],
      ),
    );
    return res ?? false;
  }

  Future<bool> _confirmChangeFournisseur(
    BuildContext context, {
    required String currentNomFrs,
    required String nextNomFrs,
  }) async {
    final res = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Changer de fournisseur ?'),
        content: Text(
          'Votre panier appartient à $currentNomFrs.\n\nVider le panier et ajouter des produits de $nextNomFrs ?',
        ),
        actions: [
          TextButton(
              onPressed: () => Navigator.of(ctx).pop(false),
              child: const Text('Annuler')),
          ElevatedButton(
              onPressed: () => Navigator.of(ctx).pop(true),
              child: const Text('Vider et ajouter')),
        ],
      ),
    );
    return res ?? false;
  }
}

final cartProvider = StateNotifierProvider<CartNotifier, List<CartItem>>(
    (ref) => CartNotifier(ref));
