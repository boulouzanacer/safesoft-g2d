import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/produit_provider.dart';
import '../../widgets/common/error_state.dart';
import '../../widgets/product/product_card.dart';
import '../../widgets/skeletons/product_card_skeleton.dart';

class BoutiqueDetailScreen extends ConsumerStatefulWidget {
  final int id;

  const BoutiqueDetailScreen({super.key, required this.id});

  @override
  ConsumerState<BoutiqueDetailScreen> createState() =>
      _BoutiqueDetailScreenState();
}

class _BoutiqueDetailScreenState extends ConsumerState<BoutiqueDetailScreen> {
  final _scrollCtrl = ScrollController();
  final _searchCtrl = TextEditingController();
  Timer? _debounce;
  String? _selectedCategorie;

  @override
  void initState() {
    super.initState();
    _scrollCtrl.addListener(_onScroll);
  }

  @override
  void dispose() {
    _debounce?.cancel();
    _scrollCtrl.dispose();
    _searchCtrl.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (!_scrollCtrl.hasClients) return;
    final max = _scrollCtrl.position.maxScrollExtent;
    final current = _scrollCtrl.position.pixels;
    if (max <= 0) return;
    if (current >= max - 320) {
      ref
          .read(produitProvider(ProduitListQuery(frsId: widget.id)).notifier)
          .loadMore();
    }
  }

  void _onSearchChanged(String v) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 350), () {
      ref
          .read(produitProvider(ProduitListQuery(frsId: widget.id)).notifier)
          .refresh(search: v);
    });
  }

  Widget _gridSkeleton() {
    return GridView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: 6,
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 2,
        childAspectRatio: 0.62,
        crossAxisSpacing: 12,
        mainAxisSpacing: 12,
      ),
      itemBuilder: (_, __) => const ProductCardSkeleton(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final boutiqueAsync = ref.watch(boutiquesProvider);
    final productsState =
        ref.watch(produitProvider(ProduitListQuery(frsId: widget.id)));
    final categoriesAsync = ref.watch(categoriesProvider(widget.id));

    final boutique = boutiqueAsync.maybeWhen(
      data: (items) {
        for (final b in items) {
          if (b.id == widget.id) return b;
        }
        return null;
      },
      orElse: () => null,
    );

    return Scaffold(
      appBar: AppBar(
        title: Text(boutique?.nomFrs ?? 'Boutique'),
        actions: [
          IconButton(
            onPressed: () => context.push('/notifications'),
            icon: const Icon(Icons.notifications_none),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          await ref
              .read(
                  produitProvider(ProduitListQuery(frsId: widget.id)).notifier)
              .refresh(search: _searchCtrl.text, categorie: _selectedCategorie);
        },
        child: Builder(
          builder: (context) {
            final children = <Widget>[
              if (boutique != null)
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.surface,
                    borderRadius: BorderRadius.circular(12),
                    border:
                        Border.all(color: Colors.black.withValues(alpha: 0.06)),
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              boutique.nomFrs,
                              style:
                                  const TextStyle(fontWeight: FontWeight.w900),
                            ),
                            const SizedBox(height: 4),
                            Text(boutique.wilaya ?? '-'),
                          ],
                        ),
                      ),
                      Text(
                        '${boutique.nbProduits} produits',
                        style: const TextStyle(fontWeight: FontWeight.w800),
                      ),
                    ],
                  ),
                ),
              const SizedBox(height: 12),
              TextField(
                controller: _searchCtrl,
                onChanged: (v) {
                  setState(() {});
                  _onSearchChanged(v);
                },
                decoration: InputDecoration(
                  hintText: 'Rechercher dans cette boutique',
                  prefixIcon: const Icon(Icons.search),
                  suffixIcon: _searchCtrl.text.isEmpty
                      ? null
                      : IconButton(
                          onPressed: () {
                            _searchCtrl.clear();
                            ref
                                .read(produitProvider(
                                        ProduitListQuery(frsId: widget.id))
                                    .notifier)
                                .refresh(search: '');
                            setState(() {});
                          },
                          icon: const Icon(Icons.close),
                        ),
                ),
              ),
              const SizedBox(height: 12),
              categoriesAsync.when(
                loading: () => const LinearProgressIndicator(minHeight: 2),
                error: (_, __) => const SizedBox.shrink(),
                data: (cats) => SizedBox(
                  height: 40,
                  child: ListView.builder(
                    scrollDirection: Axis.horizontal,
                    itemCount: cats.length + 1,
                    itemBuilder: (_, i) {
                      if (i == 0) {
                        return Padding(
                          padding: const EdgeInsets.only(right: 8),
                          child: ChoiceChip(
                            label: const Text('Tous'),
                            selected: _selectedCategorie == null,
                            onSelected: (_) async {
                              setState(() => _selectedCategorie = null);
                              await ref
                                  .read(produitProvider(
                                          ProduitListQuery(frsId: widget.id))
                                      .notifier)
                                  .refresh(clearCategorie: true);
                            },
                          ),
                        );
                      }
                      final c = cats[i - 1];
                      return Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: ChoiceChip(
                          label: Text(c),
                          selected: _selectedCategorie == c,
                          onSelected: (_) async {
                            setState(() => _selectedCategorie = c);
                            await ref
                                .read(produitProvider(
                                        ProduitListQuery(frsId: widget.id))
                                    .notifier)
                                .refresh(categorie: c);
                          },
                        ),
                      );
                    },
                  ),
                ),
              ),
              const SizedBox(height: 14),
              if (productsState.isLoading && productsState.produits.isEmpty)
                _gridSkeleton()
              else if (productsState.error != null &&
                  productsState.produits.isEmpty)
                ErrorState(
                  message: productsState.error ?? 'Erreur',
                  onRetry: () => ref
                      .read(produitProvider(ProduitListQuery(frsId: widget.id))
                          .notifier)
                      .fetchProduits(page: 1),
                )
              else if (productsState.produits.isEmpty)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 40),
                  child: Column(
                    children: [
                      Icon(Icons.inventory_2_outlined, size: 52),
                      SizedBox(height: 10),
                      Text('Aucun produit'),
                      SizedBox(height: 6),
                      Text(
                        'Essayez une autre recherche ou catégorie.',
                        textAlign: TextAlign.center,
                      ),
                    ],
                  ),
                )
              else
                GridView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: productsState.produits.length,
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    childAspectRatio: 0.62,
                    crossAxisSpacing: 12,
                    mainAxisSpacing: 12,
                  ),
                  itemBuilder: (_, i) =>
                      ProductCard(produit: productsState.produits[i]),
                ),
              if (productsState.isLoading && productsState.produits.isNotEmpty)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 16),
                  child: Center(child: CircularProgressIndicator()),
                ),
            ];

            return ListView.builder(
              controller: _scrollCtrl,
              padding: const EdgeInsets.all(16),
              itemCount: children.length,
              itemBuilder: (_, i) => children[i],
            );
          },
        ),
      ),
    );
  }
}
