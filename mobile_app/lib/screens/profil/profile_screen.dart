import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../l10n/app_i18n.dart';
import '../../providers/auth_provider.dart';
import '../../providers/locale_provider.dart';
import '../../providers/notification_provider.dart';
import '../../providers/theme_provider.dart';

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  String _initials(String fullName) {
    final parts =
        fullName.trim().split(RegExp(r'\s+')).where((part) => part.isNotEmpty);
    final letters = parts.take(2).map((part) => part.characters.first.toUpperCase()).join();
    return letters.isEmpty ? '?' : letters;
  }

  Widget _row({
    required BuildContext context,
    required IconData icon,
    required String title,
    VoidCallback? onTap,
    Widget? trailing,
    Color? color,
  }) {
    return ListTile(
      leading: Icon(icon, color: color),
      title: Text(title, style: TextStyle(color: color)),
      trailing: trailing ??
          Icon(
            Directionality.of(context) == TextDirection.rtl
                ? Icons.chevron_left
                : Icons.chevron_right,
          ),
      onTap: onTap,
    );
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final auth = ref.watch(authProvider);
    final client = auth.client;
    final notif = ref.watch(notificationProvider);
    final isDark = ref.watch(themeProvider);
    final locale = ref.watch(localeProvider);

    return Scaffold(
      appBar: AppBar(title: Text(context.tr('Profil'))),
      body: Builder(
        builder: (context) {
          final children = <Widget>[
          if (client == null)
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Theme.of(context).colorScheme.surface,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.black.withValues(alpha: 0.06)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Text(context.tr('Non connecté'),
                      style:
                          const TextStyle(fontSize: 16, fontWeight: FontWeight.w900)),
                  const SizedBox(height: 10),
                  ElevatedButton(
                    onPressed: () => context.go('/login'),
                    child: Text(context.tr('Se connecter')),
                  ),
                ],
              ),
            )
          else
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Theme.of(context).colorScheme.surface,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.black.withValues(alpha: 0.06)),
              ),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 28,
                    backgroundColor:
                        Theme.of(context).colorScheme.primary.withValues(alpha: 0.15),
                    child: Text(
                      _initials(client.nom),
                      style: TextStyle(
                        fontWeight: FontWeight.w900,
                        color: Theme.of(context).colorScheme.primary,
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          client.nom,
                          style: const TextStyle(
                              fontSize: 16, fontWeight: FontWeight.w900),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          client.email,
                          style: TextStyle(
                            color: Theme.of(context)
                                .textTheme
                                .bodySmall
                                ?.color
                                ?.withValues(alpha: 0.7),
                          ),
                        ),
                        const SizedBox(height: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 10, vertical: 6),
                          decoration: BoxDecoration(
                            color: (client.typeClient == 'abonne'
                                    ? Colors.blue
                                    : Colors.green)
                                .withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(999),
                          ),
                          child: Text(
                            client.typeClient == 'abonne'
                                ? context.tr('Abonné')
                                : context.tr('Client Simple'),
                            style: TextStyle(
                              color: client.typeClient == 'abonne'
                                  ? Colors.blue
                                  : Colors.green,
                              fontWeight: FontWeight.w800,
                              fontSize: 12,
                            ),
                          ),
                        )
                      ],
                    ),
                  ),
                ],
              ),
            ),
          const SizedBox(height: 12),
          Container(
            decoration: BoxDecoration(
              color: Theme.of(context).colorScheme.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.black.withValues(alpha: 0.06)),
            ),
            child: Column(
              children: [
                _row(
                  context: context,
                  icon: Icons.person_outline,
                  title: context.tr('Mes informations'),
                  onTap: client == null ? null : () => context.push('/profile/edit'),
                ),
                _row(
                  context: context,
                  icon: Icons.receipt_long_outlined,
                  title: context.tr('Mes commandes'),
                  onTap: () => context.go('/home/commandes'),
                ),
                _row(
                  context: context,
                  icon: Icons.notifications_none,
                  title: context.tr('Notifications'),
                  trailing: notif.nonLues > 0
                      ? Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: Colors.red.withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(999),
                              ),
                              child: Text(
                                '${notif.nonLues}',
                                style: const TextStyle(
                                    color: Colors.red,
                                    fontWeight: FontWeight.w900,
                                    fontSize: 12),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Icon(
                              context.isRtlLanguage
                                  ? Icons.chevron_left
                                  : Icons.chevron_right,
                            ),
                          ],
                        )
                      : Icon(
                          context.isRtlLanguage
                              ? Icons.chevron_left
                              : Icons.chevron_right,
                        ),
                  onTap: () => context.push('/notifications'),
                ),
                ListTile(
                  leading: const Text('🌐', style: TextStyle(fontSize: 20)),
                  title: Text(context.tr('Langue')),
                  subtitle: Text(context.tr('Choisir la langue')),
                  trailing: Wrap(
                    spacing: 8,
                    children: [
                      for (final option in AppI18n.localeOptions)
                        ChoiceChip(
                          label: Text('${option['flag']} ${option['label']}'),
                          selected: locale.languageCode == option['code'],
                          onSelected: (_) => ref
                              .read(localeProvider.notifier)
                              .setLocale(option['code']!),
                        ),
                    ],
                  ),
                ),
                SwitchListTile(
                  value: isDark,
                  onChanged: (_) => ref.read(themeProvider.notifier).toggle(),
                  secondary: const Icon(Icons.dark_mode_outlined),
                  title: Text(context.tr('Mode sombre')),
                ),
                _row(
                  context: context,
                  icon: Icons.key_outlined,
                  title: context.tr('Changer mot de passe'),
                  onTap: client == null ? null : () => context.push('/profile/password'),
                ),
                _row(
                  context: context,
                  icon: Icons.support_agent_outlined,
                  title: context.tr('Support'),
                  onTap: () {
                    showDialog<void>(
                      context: context,
                      builder: (ctx) => AlertDialog(
                        title: Text(context.tr('Support')),
                        content: Text(
                          context.tr('Contactez-nous : support@safesoft.dz'),
                        ),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.of(ctx).pop(),
                            child: Text(context.tr('OK')),
                          ),
                        ],
                      ),
                    );
                  },
                ),
                _row(
                  context: context,
                  icon: Icons.logout,
                  title: context.tr('Déconnexion'),
                  color: Colors.red,
                  trailing: const Icon(Icons.logout, color: Colors.red),
                  onTap: client == null
                      ? null
                      : () async {
                          await ref.read(authProvider.notifier).logout();
                          if (context.mounted) context.go('/login');
                        },
                ),
              ],
            ),
          ),
          ];

          return ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: children.length,
            itemBuilder: (_, i) => children[i],
          );
        },
      ),
    );
  }
}
