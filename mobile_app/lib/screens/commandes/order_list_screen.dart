import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../providers/auth_provider.dart';
import '../../providers/commande_provider.dart';
import '../../widgets/common/error_state.dart';
import '../../widgets/skeletons/order_card_skeleton.dart';

class OrderListScreen extends ConsumerWidget {
  const OrderListScreen({super.key});

  String _formatDate(String raw) {
    final dt = DateTime.tryParse(raw);
    if (dt == null) return raw;
    return DateFormat('dd/MM/yyyy HH:mm').format(dt.toLocal());
  }

  Color _statusColor(String statut) {
    switch (statut) {
      case 'en_attente':
        return Colors.orange;
      case 'confirmee':
        return Colors.blue;
      case 'expediee':
        return Colors.purple;
      case 'livree':
        return Colors.green;
      case 'annulee':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(commandeProvider);
    final auth = ref.watch(authProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Mes commandes')),
      body: !auth.isAuthenticated
          ? Center(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.lock_outline, size: 52),
                    const SizedBox(height: 10),
                    const Text('Connectez-vous pour voir vos commandes'),
                    const SizedBox(height: 12),
                    ElevatedButton(
                      onPressed: () => context.go('/login'),
                      child: const Text('Se connecter'),
                    ),
                  ],
                ),
              ),
            )
          : state.isLoading
              ? ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: 6,
                  itemBuilder: (_, __) => const Padding(
                    padding: EdgeInsets.only(bottom: 12),
                    child: OrderCardSkeleton(),
                  ),
                )
              : state.error != null
                  ? ErrorState(
                      message: state.error ?? 'Erreur',
                      onRetry: () => ref
                          .read(commandeProvider.notifier)
                          .fetchMesCommandes(),
                    )
                  : (state.commandes.isEmpty
                      ? const Center(
                          child: Padding(
                            padding: EdgeInsets.all(20),
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.receipt_long_outlined, size: 60),
                                SizedBox(height: 12),
                                Text(
                                  'Aucune commande',
                                  style: TextStyle(fontWeight: FontWeight.w900),
                                ),
                                SizedBox(height: 6),
                                Text(
                                  'Vos commandes apparaîtront ici après validation.',
                                  textAlign: TextAlign.center,
                                ),
                              ],
                            ),
                          ),
                        )
                      : ListView.separated(
                          padding: const EdgeInsets.all(16),
                          itemCount: state.commandes.length,
                          separatorBuilder: (_, __) =>
                              const SizedBox(height: 12),
                          itemBuilder: (_, i) {
                            final c = state.commandes[i];
                            final color = _statusColor(c.statut);
                            return InkWell(
                              borderRadius: BorderRadius.circular(12),
                              onTap: () =>
                                  context.push('/home/commandes/${c.id}'),
                              child: Container(
                                padding: const EdgeInsets.all(14),
                                decoration: BoxDecoration(
                                  color: Theme.of(context).colorScheme.surface,
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(
                                    color: Colors.black.withValues(alpha: 0.06),
                                  ),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        Expanded(
                                          child: Text(
                                            'Commande #${c.id}',
                                            style: const TextStyle(
                                                fontWeight: FontWeight.w900),
                                          ),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(
                                              horizontal: 10, vertical: 6),
                                          decoration: BoxDecoration(
                                            color:
                                                color.withValues(alpha: 0.12),
                                            borderRadius:
                                                BorderRadius.circular(999),
                                          ),
                                          child: Text(
                                            c.statut.replaceAll('_', ' '),
                                            style: TextStyle(
                                              color: color,
                                              fontWeight: FontWeight.w800,
                                              fontSize: 12,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 6),
                                    Text(_formatDate(c.dateCmd),
                                        style: TextStyle(
                                          fontSize: 12,
                                          color: Theme.of(context)
                                              .textTheme
                                              .bodySmall
                                              ?.color
                                              ?.withValues(alpha: 0.7),
                                        )),
                                    const SizedBox(height: 8),
                                    Text('Fournisseur : ${c.nomFrs ?? ''}'),
                                    const SizedBox(height: 6),
                                    Text(
                                      '${NumberFormat('#,##0.00', 'fr_FR').format(c.montantTotal)} DA',
                                      style: const TextStyle(
                                          fontWeight: FontWeight.w900),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        )),
    );
  }
}
