import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:intl/intl.dart';
import 'package:photo_view/photo_view.dart';

import '../../core/constants/api_constants.dart';
import '../../models/produit_model.dart';
import '../../providers/cart_provider.dart';
import '../../providers/produit_provider.dart';
import '../../widgets/common/error_state.dart';

class ProductDetailScreen extends ConsumerStatefulWidget {
  final int id;

  const ProductDetailScreen({super.key, required this.id});

  @override
  ConsumerState<ProductDetailScreen> createState() =>
      _ProductDetailScreenState();
}

class _ProductDetailScreenState extends ConsumerState<ProductDetailScreen> {
  int _qty = 1;
  int _imageIndex = 0;
  bool _descExpanded = false;

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

  List<String> _images(ProduitModel p) {
    final urls = <String>[
      ...p.images
          .map((e) => _resolveUrl(e.urlPrincipale))
          .where((e) => e.isNotEmpty),
    ];
    final main = _resolveUrl(p.imagePrincipale ?? '');
    if (urls.isEmpty && main.isNotEmpty) urls.add(main);
    return urls;
  }

  void _openFullscreen(String url) {
    showDialog(
      context: context,
      builder: (_) => Dialog(
        insetPadding: EdgeInsets.zero,
        backgroundColor: Colors.black,
        child: Stack(
          children: [
            Positioned.fill(
              child: PhotoView(
                imageProvider: CachedNetworkImageProvider(url),
                backgroundDecoration: const BoxDecoration(color: Colors.black),
              ),
            ),
            Positioned(
              top: 20,
              right: 12,
              child: IconButton(
                onPressed: () => Navigator.of(context).pop(),
                icon: const Icon(Icons.close, color: Colors.white),
              ),
            )
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final async = ref.watch(produitDetailProvider(widget.id));

    return Scaffold(
      appBar: AppBar(title: const Text('Détail produit')),
      body: async.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (_, __) => ErrorState(
          message: 'Impossible de charger le produit',
          onRetry: () => ref.invalidate(produitDetailProvider(widget.id)),
        ),
        data: (p) {
          final images = _images(p);
          final hasStock = p.stock > 0;
          final desc = p.description.trim();
          final longDesc = desc.length > 140;
          final unit = p.unitPriceForQty(_qty);
          final total = unit * _qty;

          return Column(
            children: [
              Expanded(
                child: Builder(
                  builder: (context) {
                    final children = <Widget>[
                      if (images.isNotEmpty) ...[
                        AspectRatio(
                          aspectRatio: 1.2,
                          child: Stack(
                            children: [
                              PageView.builder(
                                itemCount: images.length,
                                onPageChanged: (i) =>
                                    setState(() => _imageIndex = i),
                                itemBuilder: (_, i) {
                                  final url = images[i];
                                  final img = CachedNetworkImage(
                                    imageUrl: url,
                                    fit: BoxFit.cover,
                                    placeholder: (_, __) => Container(
                                      color:
                                          Colors.black.withValues(alpha: 0.04),
                                      child: const Center(
                                          child: CircularProgressIndicator()),
                                    ),
                                    errorWidget: (_, __, ___) => Container(
                                      color:
                                          Colors.black.withValues(alpha: 0.04),
                                      child: const Icon(
                                          Icons.broken_image_outlined),
                                    ),
                                  );

                                  return GestureDetector(
                                    onTap: () => _openFullscreen(url),
                                    child: i == 0
                                        ? Hero(
                                            tag: 'product-image-${p.id}',
                                            child: img,
                                          )
                                        : img,
                                  );
                                },
                              ),
                              Positioned(
                                bottom: 10,
                                left: 0,
                                right: 0,
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: List.generate(
                                    images.length,
                                    (i) => AnimatedContainer(
                                      duration:
                                          const Duration(milliseconds: 200),
                                      height: 8,
                                      width: _imageIndex == i ? 22 : 8,
                                      margin: const EdgeInsets.symmetric(
                                          horizontal: 4),
                                      decoration: BoxDecoration(
                                        color: _imageIndex == i
                                            ? Theme.of(context)
                                                .colorScheme
                                                .primary
                                            : Colors.white
                                                .withValues(alpha: 0.5),
                                        borderRadius: BorderRadius.circular(10),
                                      ),
                                    ),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),
                      ],
                      Text(
                        p.designation,
                        style: const TextStyle(
                            fontSize: 18, fontWeight: FontWeight.w900),
                      ),
                      const SizedBox(height: 6),
                      Text('Référence : ${p.reference}'),
                      const SizedBox(height: 2),
                      Text('Catégorie : ${p.categorie}'),
                      const SizedBox(height: 14),
                      Text(
                        _price(unit),
                        style: const TextStyle(
                            fontSize: 20, fontWeight: FontWeight.w900),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Total: ${_price(total)}',
                        style: TextStyle(
                          fontWeight: FontWeight.w700,
                          color: Theme.of(context)
                              .textTheme
                              .bodyMedium
                              ?.color
                              ?.withValues(alpha: 0.8),
                        ),
                      ),
                      const SizedBox(height: 10),
                      CheckboxListTile(
                        value: p.enableTierPricing,
                        onChanged: null,
                        dense: true,
                        contentPadding: EdgeInsets.zero,
                        controlAffinity: ListTileControlAffinity.leading,
                        title: const Text('Prix par palier'),
                        subtitle: p.quantityPrices.isEmpty
                            ? const Text('Aucun palier')
                            : Text('${p.quantityPrices.length} palier(s)'),
                      ),
                      if (p.hasTierPricing) ...[
                        Card(
                          margin: const EdgeInsets.only(top: 6, bottom: 10),
                          child: Padding(
                            padding: const EdgeInsets.all(12),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text('Tarifs par quantité',
                                    style:
                                        TextStyle(fontWeight: FontWeight.w900)),
                                const SizedBox(height: 10),
                                ...p.quantityPrices.map((t) {
                                  final range = t.quantityMax == null
                                      ? '${t.quantityMin}+'
                                      : '${t.quantityMin}-${t.quantityMax}';
                                  return Padding(
                                    padding:
                                        const EdgeInsets.only(bottom: 8.0),
                                    child: Row(
                                      children: [
                                        Expanded(child: Text('$range pièces')),
                                        Text(_price(t.price),
                                            style: const TextStyle(
                                                fontWeight: FontWeight.w800)),
                                      ],
                                    ),
                                  );
                                }),
                              ],
                            ),
                          ),
                        ),
                      ],
                      Row(
                        children: [
                          Icon(
                            hasStock
                                ? Icons.check_circle_outline
                                : Icons.cancel_outlined,
                            color: hasStock ? Colors.green : Colors.red,
                          ),
                          const SizedBox(width: 8),
                          Text(hasStock
                              ? 'Stock : Disponible'
                              : 'Stock : Rupture'),
                        ],
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'Description',
                        style: TextStyle(
                          fontWeight: FontWeight.w800,
                          color: Theme.of(context).textTheme.bodyLarge?.color,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        desc.isEmpty ? '—' : desc,
                        maxLines: longDesc && !_descExpanded ? 3 : null,
                        overflow: longDesc && !_descExpanded
                            ? TextOverflow.ellipsis
                            : null,
                      ),
                      if (longDesc)
                        Align(
                          alignment: Alignment.centerLeft,
                          child: TextButton(
                            onPressed: () =>
                                setState(() => _descExpanded = !_descExpanded),
                            child: Text(_descExpanded ? 'Réduire' : 'Voir plus'),
                          ),
                        ),
                      const SizedBox(height: 12),
                      Text(
                        'Vendeur : ${p.nomFrs ?? ''}',
                        style: const TextStyle(fontWeight: FontWeight.w700),
                      ),
                    ];

                    return ListView.builder(
                      padding: const EdgeInsets.all(16),
                      itemCount: children.length,
                      itemBuilder: (_, i) => children[i],
                    );
                  },
                ),
              ),
              SafeArea(
                top: false,
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          const Text('Quantité :',
                              style: TextStyle(fontWeight: FontWeight.w700)),
                          const Spacer(),
                          IconButton(
                            onPressed: _qty <= 1
                                ? null
                                : () => setState(() => _qty -= 1),
                            icon: const Icon(Icons.remove_circle_outline),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 14, vertical: 8),
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(
                                  color: Colors.black.withValues(alpha: 0.12)),
                            ),
                            child: Text('$_qty',
                                style: const TextStyle(
                                    fontWeight: FontWeight.w800)),
                          ),
                          IconButton(
                            onPressed: !hasStock || _qty >= p.stock
                                ? null
                                : () => setState(() => _qty += 1),
                            icon: const Icon(Icons.add_circle_outline),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton(
                          onPressed: hasStock
                              ? () {
                                  ref
                                      .read(cartProvider.notifier)
                                      .addItem(context, p, quantite: _qty)
                                      .then((ok) {
                                    if (!ok || !context.mounted) return;
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      const SnackBar(
                                          content: Text('Ajouté au panier')),
                                    );
                                  });
                                }
                              : null,
                          child: const Text('Ajouter au panier'),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}
