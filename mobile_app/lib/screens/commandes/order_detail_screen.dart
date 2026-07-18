import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:intl/intl.dart';

import '../../core/constants/api_constants.dart';
import '../../providers/commande_provider.dart';

class OrderDetailScreen extends ConsumerStatefulWidget {
  final int id;

  const OrderDetailScreen({super.key, required this.id});

  @override
  ConsumerState<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends ConsumerState<OrderDetailScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(commandeProvider.notifier).fetchCommande(widget.id);
    });
  }

  String _formatDate(String raw) {
    final dt = DateTime.tryParse(raw);
    if (dt == null) return raw;
    return DateFormat('dd/MM/yyyy HH:mm').format(dt.toLocal());
  }

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

  List<String> _steps(String statut) {
    if (statut == 'annulee') {
      return const ['en_attente', 'annulee'];
    }
    return const ['en_attente', 'confirmee', 'expediee', 'livree'];
  }

  int _stepIndex(String statut, List<String> steps) {
    final idx = steps.indexOf(statut);
    if (idx != -1) return idx;
    if (statut == 'annulee' && steps.contains('annulee')) {
      return steps.indexOf('annulee');
    }
    return 0;
  }

  Widget _timeline(String statut) {
    final steps = _steps(statut);
    final current = _stepIndex(statut, steps);
    return Row(
      children: List.generate(steps.length, (i) {
        final done = i <= current;
        final color = done ? Colors.blue : Colors.grey;
        final label = steps[i].replaceAll('_', ' ');
        return Expanded(
          child: Column(
            children: [
              Row(
                children: [
                  Container(
                    width: 14,
                    height: 14,
                    decoration: BoxDecoration(
                      color: done ? color : Colors.transparent,
                      border: Border.all(color: color),
                      shape: BoxShape.circle,
                    ),
                  ),
                  if (i < steps.length - 1)
                    Expanded(
                      child: Container(
                        height: 2,
                        color:
                            done ? color : Colors.grey.withValues(alpha: 0.3),
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 6),
              Text(
                label,
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 11, color: color),
              ),
            ],
          ),
        );
      }),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(commandeProvider);
    final cmd =
        (state.currentCommande?.id == widget.id) ? state.currentCommande : null;

    return Scaffold(
      appBar: AppBar(title: Text('Commande #${widget.id}')),
      body: state.isLoading && cmd == null
          ? const Center(child: CircularProgressIndicator())
          : state.error != null && cmd == null
              ? Center(
                  child: TextButton(
                    onPressed: () => ref
                        .read(commandeProvider.notifier)
                        .fetchCommande(widget.id),
                    child: const Text('Réessayer'),
                  ),
                )
              : (cmd == null
                  ? const Center(child: Text('Commande introuvable'))
                  : Builder(
                      builder: (context) {
                        final children = <Widget>[
                          Container(
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: Theme.of(context).colorScheme.surface,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                  color: Colors.black.withValues(alpha: 0.06)),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text('N° commande : #${cmd.id}',
                                    style: const TextStyle(
                                        fontWeight: FontWeight.w900)),
                                const SizedBox(height: 6),
                                Text('Date : ${_formatDate(cmd.dateCmd)}'),
                                const SizedBox(height: 4),
                                Text('Fournisseur : ${cmd.nomFrs ?? ''}'),
                                const SizedBox(height: 12),
                                _timeline(cmd.statut),
                              ],
                            ),
                          ),
                          const SizedBox(height: 14),
                          const Text('Adresse de livraison',
                              style: TextStyle(
                                  fontSize: 16, fontWeight: FontWeight.w800)),
                          const SizedBox(height: 8),
                          Container(
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: Theme.of(context).colorScheme.surface,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                  color: Colors.black.withValues(alpha: 0.06)),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Téléphone : ${cmd.teleLivraison?.trim().isNotEmpty == true ? cmd.teleLivraison : '—'}',
                                ),
                                const SizedBox(height: 8),
                                Text(cmd.adresseLivraison ?? '—'),
                              ],
                            ),
                          ),
                          const SizedBox(height: 14),
                          const Text('Produits',
                              style: TextStyle(
                                  fontSize: 16, fontWeight: FontWeight.w800)),
                          const SizedBox(height: 8),
                          ...cmd.lignes.map(
                            (l) => Padding(
                              padding: const EdgeInsets.only(bottom: 10),
                              child: Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: Theme.of(context).colorScheme.surface,
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(
                                    color: Colors.black.withValues(alpha: 0.06),
                                  ),
                                ),
                                child: Row(
                                  children: [
                                    ClipRRect(
                                      borderRadius: BorderRadius.circular(10),
                                      child: SizedBox(
                                        width: 56,
                                        height: 56,
                                        child: _resolveUrl(l.produitImage ?? '')
                                                .isEmpty
                                            ? Container(
                                                color: Colors.black
                                                    .withValues(alpha: 0.04),
                                                child: const Icon(Icons.image),
                                              )
                                            : CachedNetworkImage(
                                                imageUrl: _resolveUrl(
                                                    l.produitImage ?? ''),
                                                fit: BoxFit.cover,
                                                errorWidget: (_, __, ___) =>
                                                    Container(
                                                  color: Colors.black
                                                      .withValues(alpha: 0.04),
                                                  child: const Icon(Icons
                                                      .broken_image_outlined),
                                                ),
                                              ),
                                      ),
                                    ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            l.produitDesignation ??
                                                'Produit #${l.idProduit}',
                                            maxLines: 2,
                                            overflow: TextOverflow.ellipsis,
                                            style: const TextStyle(
                                                fontWeight: FontWeight.w800),
                                          ),
                                          const SizedBox(height: 4),
                                          Text(
                                              '${l.quantite} × ${_price(l.prixUnitaire)}'),
                                        ],
                                      ),
                                    ),
                                    const SizedBox(width: 8),
                                    Text(
                                      _price(l.sousTotal),
                                      style: const TextStyle(
                                          fontWeight: FontWeight.w900),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                          const Divider(height: 32),
                          Row(
                            children: [
                              const Text('Total',
                                  style:
                                      TextStyle(fontWeight: FontWeight.w900)),
                              const Spacer(),
                              Text(_price(cmd.montantTotal),
                                  style: const TextStyle(
                                      fontWeight: FontWeight.w900)),
                            ],
                          ),
                          if ((cmd.notes ?? '').trim().isNotEmpty) ...[
                            const SizedBox(height: 14),
                            const Text('Notes',
                                style: TextStyle(
                                    fontSize: 16, fontWeight: FontWeight.w800)),
                            const SizedBox(height: 8),
                            Container(
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: Theme.of(context).colorScheme.surface,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                    color:
                                        Colors.black.withValues(alpha: 0.06)),
                              ),
                              child: Text(cmd.notes!.trim()),
                            ),
                          ],
                        ];

                        return ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: children.length,
                          itemBuilder: (_, i) => children[i],
                        );
                      },
                    )),
    );
  }
}
