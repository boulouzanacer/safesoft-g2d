import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:shimmer/shimmer.dart';

import '../../core/constants/api_constants.dart';
import '../../models/produit_model.dart';
import '../../providers/cart_provider.dart';

class ProductCard extends ConsumerWidget {
  final ProduitModel produit;
  final VoidCallback? onTap;

  const ProductCard({super.key, required this.produit, this.onTap});

  String _price(double value) {
    final f = NumberFormat('#,##0.00', 'fr_FR');
    return '${f.format(value)} DA';
  }

  String _bestImageUrl() {
    final main = _resolveUrl(produit.imagePrincipale ?? '');
    if (main.isNotEmpty) return main;
    if (produit.images.isNotEmpty) {
      final thumb = _resolveUrl(produit.images.first.urlThumbnail);
      if (thumb.isNotEmpty) return thumb;
      final full = _resolveUrl(produit.images.first.urlPrincipale);
      if (full.isNotEmpty) return full;
    }
    return '';
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

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final imageUrl = _bestImageUrl();

    return InkWell(
      borderRadius: BorderRadius.circular(12),
      onTap: onTap ?? () => context.push('/produits/${produit.id}'),
      child: Card(
        elevation: 0.5,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            AspectRatio(
              aspectRatio: 1.25,
              child: imageUrl.isEmpty
                  ? Container(
                      color: Colors.black.withValues(alpha: 0.04),
                      child: const Icon(Icons.image_not_supported_outlined),
                    )
                  : Hero(
                      tag: 'product-image-${produit.id}',
                      child: CachedNetworkImage(
                        imageUrl: imageUrl,
                        fit: BoxFit.cover,
                        placeholder: (_, __) => Shimmer.fromColors(
                          baseColor: Colors.black.withValues(alpha: 0.06),
                          highlightColor: Colors.black.withValues(alpha: 0.02),
                          child: Container(color: Colors.white),
                        ),
                        errorWidget: (_, __, ___) => Container(
                          color: Colors.black.withValues(alpha: 0.04),
                          child: const Icon(Icons.broken_image_outlined),
                        ),
                      ),
                    ),
            ),
            Padding(
              padding: const EdgeInsets.all(6),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    produit.designation,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontWeight: FontWeight.w700),
                  ),
                  const SizedBox(height: 1),
                  Text(
                    'Ref: ${produit.reference}',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontSize: 12,
                      color: Theme.of(context)
                          .textTheme
                          .bodySmall
                          ?.color
                          ?.withValues(alpha: 0.7),
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    _price(produit.prix),
                    style: const TextStyle(fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 5),
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton.icon(
                      style: OutlinedButton.styleFrom(
                        minimumSize: const Size(0, 32),
                        padding: const EdgeInsets.symmetric(
                            horizontal: 10, vertical: 6),
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                        visualDensity:
                            const VisualDensity(horizontal: -2, vertical: -2),
                      ),
                      onPressed: () async {
                        final ok = await ref
                            .read(cartProvider.notifier)
                            .addItem(context, produit);
                        if (!context.mounted || !ok) return;
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Ajouté au panier')),
                        );
                      },
                      icon: const Icon(Icons.add_shopping_cart_outlined,
                          size: 18),
                      label: const Text('+ Panier', maxLines: 1),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
