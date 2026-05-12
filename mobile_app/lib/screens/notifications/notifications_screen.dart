import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../models/notification_model.dart';
import '../../providers/notification_provider.dart';
import '../../widgets/common/error_state.dart';
import '../../widgets/skeletons/notification_skeleton.dart';

class NotificationsScreen extends ConsumerWidget {
  const NotificationsScreen({super.key});

  DateTime? _parseDate(String? raw) {
    if (raw == null || raw.trim().isEmpty) return null;
    return DateTime.tryParse(raw)?.toLocal();
  }

  String _groupTitle(DateTime dt) {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final day = DateTime(dt.year, dt.month, dt.day);
    final diff = today.difference(day).inDays;
    if (diff == 0) return "Aujourd'hui";
    if (diff == 1) return 'Hier';
    return 'Plus ancien';
  }

  String _timeAgo(DateTime dt) {
    final diff = DateTime.now().difference(dt);
    if (diff.inMinutes < 1) return "À l'instant";
    if (diff.inMinutes < 60) return 'Il y a ${diff.inMinutes} min';
    if (diff.inHours < 24) return 'Il y a ${diff.inHours} h';
    return DateFormat('dd/MM/yyyy').format(dt);
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(notificationProvider);
    final notifier = ref.read(notificationProvider.notifier);

    Widget content;
    if (state.isLoading) {
      content = ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: 6,
        itemBuilder: (_, __) => const Padding(
          padding: EdgeInsets.only(bottom: 12),
          child: NotificationSkeleton(),
        ),
      );
    } else if (state.error != null) {
      content = ErrorState(
        message: state.error ?? 'Erreur',
        onRetry: () => notifier.fetchNotifications(),
      );
    } else if (state.notifications.isEmpty) {
      content = ListView.builder(
        itemCount: 1,
        itemBuilder: (_, __) => const Padding(
          padding: EdgeInsets.only(top: 120),
          child: Column(
            children: [
              Icon(Icons.notifications_none, size: 60),
              SizedBox(height: 12),
              Text('Aucune notification'),
              SizedBox(height: 6),
              Text(
                'Vous recevrez ici les mises à jour de vos commandes.',
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      );
    } else {
      content = _buildNotificationsList(context, state.notifications, notifier);
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        actions: [
          TextButton(
            onPressed:
                state.nonLues <= 0 ? null : () => notifier.toutMarquerLu(),
            child: const Text('Tout lire'),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => notifier.fetchNotifications(),
        child: content,
      ),
    );
  }

  Widget _buildNotificationsList(
    BuildContext context,
    List<NotificationModel> notifications,
    NotificationNotifier notifier,
  ) {
    final listItems = <Widget>[];
    for (final group in const ["Aujourd'hui", 'Hier', 'Plus ancien']) {
      listItems.addAll(_buildGroup(
        context,
        groupTitle: group,
        items: notifications.where((n) {
          final dt = _parseDate(n.createdAt) ?? DateTime(1970);
          return _groupTitle(dt) == group;
        }).toList(),
        onTap: (n) async {
          await notifier.marquerLue(n.id);
          final commandeId = n.data['commande_id']?.toString();
          if (commandeId != null) {
            final id = int.tryParse(commandeId);
            if (id != null && context.mounted) {
              context.push('/commandes/$id');
            }
          }
        },
        onDelete: (id) => notifier.supprimer(id),
        timeAgo: _timeAgo,
        parseDate: _parseDate,
      ));
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: listItems.length,
      itemBuilder: (_, i) => listItems[i],
    );
  }

  List<Widget> _buildGroup(
    BuildContext context, {
    required String groupTitle,
    required List<NotificationModel> items,
    required Future<void> Function(NotificationModel n) onTap,
    required Future<void> Function(String id) onDelete,
    required String Function(DateTime dt) timeAgo,
    required DateTime? Function(String? raw) parseDate,
  }) {
    if (items.isEmpty) return const [];
    return [
      Padding(
        padding: const EdgeInsets.only(bottom: 10, top: 6),
        child: Text(
          groupTitle,
          style: const TextStyle(fontWeight: FontWeight.w900),
        ),
      ),
      ...items.map((n) {
        final msg = (n.data['message']?.toString() ?? 'Notification').trim();
        final isUnread = (n.readAt == null || n.readAt.toString().isEmpty);
        final dt = parseDate(n.createdAt) ?? DateTime(1970);
        return Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: Dismissible(
            key: ValueKey(n.id),
            direction: DismissDirection.endToStart,
            confirmDismiss: (_) async {
              final res = await showDialog<bool>(
                context: context,
                builder: (ctx) => AlertDialog(
                  title: const Text('Supprimer ?'),
                  content: const Text('Supprimer cette notification ?'),
                  actions: [
                    TextButton(
                      onPressed: () => Navigator.of(ctx).pop(false),
                      child: const Text('Annuler'),
                    ),
                    ElevatedButton(
                      onPressed: () => Navigator.of(ctx).pop(true),
                      child: const Text('Supprimer'),
                    ),
                  ],
                ),
              );
              return res ?? false;
            },
            onDismissed: (_) => onDelete(n.id),
            background: Container(
              alignment: Alignment.centerRight,
              padding: const EdgeInsets.only(right: 16),
              decoration: BoxDecoration(
                color: Colors.red.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(Icons.delete_outline, color: Colors.red),
            ),
            child: InkWell(
              borderRadius: BorderRadius.circular(12),
              onTap: () => onTap(n),
              child: Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: Theme.of(context).colorScheme.surface,
                  borderRadius: BorderRadius.circular(12),
                  border:
                      Border.all(color: Colors.black.withValues(alpha: 0.06)),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: 10,
                      height: 10,
                      margin: const EdgeInsets.only(top: 4),
                      decoration: BoxDecoration(
                        color: isUnread ? Colors.blue : Colors.transparent,
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            msg,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(fontWeight: FontWeight.w800),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            timeAgo(dt),
                            style: TextStyle(
                              fontSize: 12,
                              color: Theme.of(context)
                                  .textTheme
                                  .bodySmall
                                  ?.color
                                  ?.withValues(alpha: 0.7),
                            ),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      onPressed: () => onDelete(n.id),
                      icon: const Icon(Icons.close),
                    )
                  ],
                ),
              ),
            ),
          ),
        );
      }),
    ];
  }
}
