import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:intl/intl.dart';

import '../../core/constants/api_constants.dart';
import '../../l10n/app_i18n.dart';
import '../../providers/auth_provider.dart';
import '../../providers/cart_provider.dart';
import '../../widgets/common/ltr_value.dart';

class CartScreen extends ConsumerWidget {
  const CartScreen({super.key});

  String _price(double value) {
    final f = NumberFormat('#,##0.00', 'fr_FR');
    return '${f.format(value)} DA';
  }

  String _resolveUrl(String raw) {
    final v = raw.trim();
    if (v.isEmpty) return '';
    final lower = v.toLowerCase();
    if (lower.startsWith('http://') || lower.startsWith('https://')) return v;

    final base = Uri.parse(ApiConstants.baseUrl);
    if (v.startsWith('//')) return '${base.scheme}:$v';
    if (v.startsWith('/')) return '${base.origin}$v';
    return '${base.origin}/$v';
  }

  String _thumb(CartItem item) {
    final main = _resolveUrl(item.produit.imagePrincipale ?? '');
    if (main.isNotEmpty) return main;
    if (item.produit.images.isNotEmpty) {
      final t = _resolveUrl(item.produit.images.first.urlThumbnail);
      if (t.isNotEmpty) return t;
      final u = _resolveUrl(item.produit.images.first.urlPrincipale);
      if (u.isNotEmpty) return u;
    }
    return '';
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final items = ref.watch(cartProvider);
    final cart = ref.read(cartProvider.notifier);
    final auth = ref.watch(authProvider);

    return Scaffold(
      appBar: AppBar(title: Text(context.tr('Panier'))),
      body: items.isEmpty
          ? Padding(
              padding: const EdgeInsets.all(20),
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.shopping_cart_outlined, size: 64),
                    const SizedBox(height: 10),
                    Text(
                      context.tr('Votre panier est vide'),
                      style:
                          TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: () => context.go('/home/produits'),
                        child: Text(context.tr('Parcourir les produits')),
                      ),
                    ),
                  ],
                ),
              ),
            )
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: items.length + 1,
              itemBuilder: (_, i) {
                if (i == items.length) {
                  return const SizedBox(height: 120);
                }

                final it = items[i];
                final url = _thumb(it);

                return Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Dismissible(
                    key: ValueKey(it.produit.id),
                    direction: DismissDirection.endToStart,
                    background: Container(
                      alignment: Alignment.centerRight,
                      padding: const EdgeInsets.only(right: 16),
                      decoration: BoxDecoration(
                        color: Colors.red.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child:
                          const Icon(Icons.delete_outline, color: Colors.red),
                    ),
                    onDismissed: (_) => ref
                        .read(cartProvider.notifier)
                        .removeItem(it.produit.id),
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Theme.of(context).colorScheme.surface,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                            color: Colors.black.withValues(alpha: 0.06)),
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          ClipRRect(
                            borderRadius: BorderRadius.circular(10),
                            child: SizedBox(
                              width: 64,
                              height: 64,
                              child: url.isEmpty
                                  ? Container(
                                      color:
                                          Colors.black.withValues(alpha: 0.04),
                                      child: const Icon(
                                          Icons.image_not_supported_outlined),
                                    )
                                  : CachedNetworkImage(
                                      imageUrl: url,
                                      fit: BoxFit.cover,
                                      errorWidget: (_, __, ___) => Container(
                                        color: Colors.black
                                            .withValues(alpha: 0.04),
                                        child: const Icon(
                                            Icons.broken_image_outlined),
                                      ),
                                    ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  it.produit.designation,
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(
                                      fontWeight: FontWeight.w800),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  '${_price(it.prixUnitaire)} / ${context.tr('unité')}',
                                  style: const TextStyle(
                                      fontWeight: FontWeight.w700),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  '${context.tr('Sous-total:')} ${_price(it.sousTotal)}',
                                  style: TextStyle(
                                    color: Theme.of(context)
                                        .textTheme
                                        .bodySmall
                                        ?.color
                                        ?.withValues(alpha: 0.8),
                                  ),
                                ),
                                const SizedBox(height: 8),
                                Row(
                                  children: [
                                    IconButton(
                                      onPressed: it.quantite <= 1
                                          ? null
                                          : () => ref
                                              .read(cartProvider.notifier)
                                              .updateQuantite(it.produit.id,
                                                  it.quantite - 1),
                                      icon: const Icon(
                                          Icons.remove_circle_outline),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                          horizontal: 12, vertical: 6),
                                      decoration: BoxDecoration(
                                        borderRadius: BorderRadius.circular(10),
                                        border: Border.all(
                                          color: Colors.black
                                              .withValues(alpha: 0.12),
                                        ),
                                      ),
                                      child: LtrText(
                                        '${it.quantite}',
                                        style: const TextStyle(fontWeight: FontWeight.w800),
                                      ),
                                    ),
                                    IconButton(
                                      onPressed: () => ref
                                          .read(cartProvider.notifier)
                                          .updateQuantite(
                                              it.produit.id, it.quantite + 1),
                                      icon:
                                          const Icon(Icons.add_circle_outline),
                                    ),
                                    const Spacer(),
                                    IconButton(
                                      onPressed: () => ref
                                          .read(cartProvider.notifier)
                                          .removeItem(it.produit.id),
                                      icon: const Icon(Icons.delete_outline),
                                    )
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              },
            ),
      bottomNavigationBar: items.isEmpty
          ? null
          : SafeArea(
              top: false,
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Theme.of(context).colorScheme.surface,
                  border: Border(
                    top:
                        BorderSide(color: Colors.black.withValues(alpha: 0.06)),
                  ),
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Row(
                      children: [
                        Text(context.tr('Sous-total :')),
                        const Spacer(),
                        LtrText(_price(cart.montantTotal),
                            style:
                                const TextStyle(fontWeight: FontWeight.w700)),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        Text(context.tr('Livraison :')),
                        const Spacer(),
                        Text(context.tr('À calculer')),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        Text(context.tr('TOTAL :'),
                            style: TextStyle(fontWeight: FontWeight.w800)),
                        const Spacer(),
                        LtrText(_price(cart.montantTotal),
                            style:
                                const TextStyle(fontWeight: FontWeight.w900)),
                      ],
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        onPressed: () => auth.isAuthenticated
                            ? context.push('/checkout')
                            : context.go('/login'),
                        icon: Icon(
                          context.isRtlLanguage ? Icons.arrow_back : Icons.arrow_forward,
                        ),
                        label: Text(auth.isAuthenticated
                            ? context.tr('Passer la commande')
                            : context.tr('Se connecter pour commander')),
                      ),
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}
