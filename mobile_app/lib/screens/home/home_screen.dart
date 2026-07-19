import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:shimmer/shimmer.dart';

import '../../l10n/app_i18n.dart';
import '../../models/boutique_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/produit_provider.dart';
import '../../widgets/common/error_state.dart';
import '../../widgets/common/horizontal_category_selector.dart';
import '../../widgets/product/product_card.dart';
import '../../widgets/skeletons/product_card_skeleton.dart';

class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({super.key});

  @override
  ConsumerState<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen> {
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
          .refresh(search: v);
    });
  }

  Widget _searchBar({required int? frsId}) {
    return TextField(
      controller: _searchCtrl,
      onChanged: (v) {
        setState(() {});
        _onSearchChanged(v, frsId: frsId);
      },
      decoration: InputDecoration(
        hintText: context.tr('Rechercher un produit'),
        prefixIcon: const Icon(Icons.search),
        suffixIcon: _searchCtrl.text.isEmpty
            ? null
            : IconButton(
                onPressed: () {
                  _searchCtrl.clear();
                  ref
                      .read(produitProvider(ProduitListQuery(frsId: frsId))
                          .notifier)
                      .refresh(search: '');
                  setState(() {});
                },
                icon: const Icon(Icons.close),
              ),
      ),
    );
  }

  Widget _sectionTitle(String title, {VoidCallback? onTap}) {
    return Row(
      children: [
        Expanded(
          child: Text(
            title,
            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
          ),
        ),
        if (onTap != null)
          TextButton(onPressed: onTap, child: Text(context.tr('Voir tout'))),
      ],
    );
  }

  Widget _boutiqueCard(BoutiqueModel b) {
    final arrowIcon =
        context.isRtlLanguage ? Icons.arrow_back_ios_new : Icons.arrow_forward_ios;

    return InkWell(
      borderRadius: BorderRadius.circular(12),
      onTap: () => context.push('/boutiques/${b.id}'),
      child: Container(
        width: 220,
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Theme.of(context).colorScheme.surface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.black.withValues(alpha: 0.05)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    b.nomFrs,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontWeight: FontWeight.w800),
                  ),
                ),
                Icon(arrowIcon, size: 14),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              b.wilaya ?? '-',
              style: TextStyle(
                fontSize: 12,
                color: Theme.of(context)
                    .textTheme
                    .bodySmall
                    ?.color
                    ?.withValues(alpha: 0.7),
              ),
            ),
            const Spacer(),
            Text(
              '${b.nbProduits} ${context.tr('produits')}',
              style: const TextStyle(fontWeight: FontWeight.w700),
            ),
          ],
        ),
      ),
    );
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
    final client = auth.client;
    final isAbonne = client?.typeClient == 'abonne';
    final frsId = isAbonne ? client?.idFrs : null;

    final produitsState =
        ref.watch(produitProvider(ProduitListQuery(frsId: frsId)));
    final boutiquesAsync = ref.watch(boutiquesProvider);
    final categoriesAsync =
        isAbonne ? ref.watch(categoriesProvider(frsId)) : null;

    return Scaffold(
      appBar: AppBar(
        title: Text(context.tr('Accueil')),
        actions: [
          IconButton(
            onPressed: () => context.push('/notifications'),
            icon: const Icon(Icons.notifications_none),
          )
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          await ref
              .read(produitProvider(ProduitListQuery(frsId: frsId)).notifier)
              .refresh(search: _searchCtrl.text, categorie: _selectedCategorie);
        },
        child: Builder(
          builder: (context) {
            final children = <Widget>[
              if (isAbonne) ...[
                Text(
                  '${context.tr('Bienvenue, ')}${client?.firstName ?? ''}',
                  style: const TextStyle(
                      fontSize: 18, fontWeight: FontWeight.w900),
                ),
                const SizedBox(height: 4),
                Text(
                  client?.fournisseur?.nomFrs ?? '—',
                  style: TextStyle(
                    color: Theme.of(context)
                        .textTheme
                        .bodySmall
                        ?.color
                        ?.withValues(alpha: 0.7),
                  ),
                ),
                const SizedBox(height: 12),
              ],
              _searchBar(frsId: frsId),
              const SizedBox(height: 16),
              if (!isAbonne) ...[
                _sectionTitle(context.tr('Nos Boutiques')),
                const SizedBox(height: 10),
                boutiquesAsync.when(
                  loading: () => SizedBox(
                    height: 110,
                    child: ListView.separated(
                      scrollDirection: Axis.horizontal,
                      itemCount: 3,
                      separatorBuilder: (_, __) => const SizedBox(width: 12),
                      itemBuilder: (_, __) => Shimmer.fromColors(
                        baseColor: Colors.black.withValues(alpha: 0.06),
                        highlightColor: Colors.black.withValues(alpha: 0.02),
                        child: Container(
                          width: 220,
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                      ),
                    ),
                  ),
                  error: (_, __) => TextButton(
                    onPressed: () => ref.invalidate(boutiquesProvider),
                    child: Text(context.tr('Réessayer')),
                  ),
                  data: (boutiques) => SizedBox(
                    height: 110,
                    child: ListView.separated(
                      scrollDirection: Axis.horizontal,
                      itemCount: boutiques.length,
                      separatorBuilder: (_, __) => const SizedBox(width: 12),
                      itemBuilder: (_, i) => _boutiqueCard(boutiques[i]),
                    ),
                  ),
                ),
                const SizedBox(height: 18),
                _sectionTitle(context.tr('Tous les produits'),
                    onTap: () => context.go('/home/produits')),
                const SizedBox(height: 10),
              ] else ...[
                Text(
                  context.tr('Catégories'),
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 10),
                categoriesAsync!.when(
                  loading: () => const LinearProgressIndicator(minHeight: 2),
                  error: (_, __) => TextButton(
                    onPressed: () => ref.invalidate(categoriesProvider(frsId)),
                    child: Text(context.tr('Réessayer')),
                  ),
                  data: (cats) => HorizontalCategorySelector(
                    categories: cats,
                    allLabel: context.tr('Tous'),
                    selectedValue: _selectedCategorie,
                    onChanged: (value) async {
                      setState(() => _selectedCategorie = value);
                      final notifier = ref
                          .read(produitProvider(ProduitListQuery(frsId: frsId)).notifier);

                      if (value == null) {
                        await notifier.refresh(clearCategorie: true);
                        return;
                      }

                      await notifier.refresh(categorie: value);
                    },
                  ),
                ),
                const SizedBox(height: 18),
              ],
              if (produitsState.isLoading && produitsState.produits.isEmpty)
                _gridSkeleton()
              else if (produitsState.error != null &&
                  produitsState.produits.isEmpty)
                ErrorState(
                  message: produitsState.error ?? context.tr('Erreur'),
                  onRetry: () => ref
                      .read(produitProvider(ProduitListQuery(frsId: frsId))
                          .notifier)
                      .fetchProduits(page: 1),
                )
              else if (produitsState.produits.isEmpty)
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 40),
                  child: Column(
                    children: [
                      Icon(Icons.inventory_2_outlined, size: 52),
                      SizedBox(height: 10),
                      Text(context.tr('Aucun produit')),
                      SizedBox(height: 6),
                      Text(
                        context.tr('Essayez de modifier votre recherche.'),
                        textAlign: TextAlign.center,
                      ),
                    ],
                  ),
                )
              else
                GridView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: produitsState.produits.length,
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    childAspectRatio: 0.62,
                    crossAxisSpacing: 12,
                    mainAxisSpacing: 12,
                  ),
                  itemBuilder: (_, i) =>
                      ProductCard(produit: produitsState.produits[i]),
                ),
              if (produitsState.isLoading && produitsState.produits.isNotEmpty)
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
