import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../providers/auth_provider.dart';
import '../../providers/produit_provider.dart';
import '../../widgets/common/error_state.dart';
import '../../widgets/product/product_card.dart';
import '../../widgets/skeletons/product_card_skeleton.dart';

class ProductListScreen extends ConsumerStatefulWidget {
  const ProductListScreen({super.key});

  @override
  ConsumerState<ProductListScreen> createState() => _ProductListScreenState();
}

class _ProductListScreenState extends ConsumerState<ProductListScreen> {
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
      final auth = ref.read(authProvider);
      final frsId =
          (auth.client?.typeClient == 'abonne') ? auth.client?.idFrs : null;
      ref
          .read(produitProvider(ProduitListQuery(frsId: frsId)).notifier)
          .loadMore();
    }
  }

  void _onSearchChanged(String v, {required int? frsId}) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 350), () {
      ref
          .read(produitProvider(ProduitListQuery(frsId: frsId)).notifier)
          .refresh(search: v, categorie: _selectedCategorie);
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
    final auth = ref.watch(authProvider);
    final frsId =
        (auth.client?.typeClient == 'abonne') ? auth.client?.idFrs : null;

    final state = ref.watch(produitProvider(ProduitListQuery(frsId: frsId)));
    final categoriesAsync = ref.watch(categoriesProvider(frsId));

    return Scaffold(
      appBar: AppBar(
        title: const Text('Produits'),
        actions: [
          IconButton(
            onPressed: () {},
            icon: const Icon(Icons.filter_alt_outlined),
          ),
          IconButton(
            onPressed: () => context.push('/notifications'),
            icon: const Icon(Icons.notifications_none),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          await ref
              .read(produitProvider(ProduitListQuery(frsId: frsId)).notifier)
              .refresh(search: _searchCtrl.text, categorie: _selectedCategorie);
        },
        child: Builder(
          builder: (_) {
            final children = <Widget>[
              TextField(
                controller: _searchCtrl,
                onChanged: (v) {
                  setState(() {});
                  _onSearchChanged(v, frsId: frsId);
                },
                decoration: InputDecoration(
                  hintText: 'Rechercher un produit',
                  prefixIcon: const Icon(Icons.search),
                  suffixIcon: _searchCtrl.text.isEmpty
                      ? null
                      : IconButton(
                          onPressed: () {
                            _searchCtrl.clear();
                            ref
                                .read(produitProvider(ProduitListQuery(frsId: frsId)).notifier)
                                .refresh(search: '', categorie: _selectedCategorie);
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
                                  .read(produitProvider(ProduitListQuery(frsId: frsId)).notifier)
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
                                .read(produitProvider(ProduitListQuery(frsId: frsId)).notifier)
                                .refresh(categorie: c);
                          },
                        ),
                      );
                    },
                  ),
                ),
              ),
              const SizedBox(height: 14),
              if (state.isLoading && state.produits.isEmpty)
                _gridSkeleton()
              else if (state.error != null && state.produits.isEmpty)
                ErrorState(
                  message: state.error ?? 'Erreur',
                  onRetry: () => ref
                      .read(produitProvider(ProduitListQuery(frsId: frsId)).notifier)
                      .fetchProduits(page: 1),
                )
              else if (state.produits.isEmpty)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 40),
                  child: Column(
                    children: [
                      Icon(Icons.inventory_2_outlined, size: 52),
                      SizedBox(height: 10),
                      Text('Aucun produit'),
                      SizedBox(height: 6),
                      Text(
                        'Essayez de modifier votre recherche ou vos filtres.',
                        textAlign: TextAlign.center,
                      ),
                    ],
                  ),
                )
              else
                GridView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: state.produits.length,
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    childAspectRatio: 0.62,
                    crossAxisSpacing: 12,
                    mainAxisSpacing: 12,
                  ),
                  itemBuilder: (_, i) => ProductCard(produit: state.produits[i]),
                ),
              if (state.isLoading && state.produits.isNotEmpty)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 16),
                  child: Center(child: CircularProgressIndicator()),
                ),
              if (!state.hasMore && state.produits.isNotEmpty)
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 20),
                  child: Center(
                    child: Text(
                      'Fin de liste',
                      style: TextStyle(
                        color: Theme.of(context)
                            .textTheme
                            .bodySmall
                            ?.color
                            ?.withValues(alpha: 0.7),
                      ),
                    ),
                  ),
                ),
              const SizedBox(height: 20),
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
