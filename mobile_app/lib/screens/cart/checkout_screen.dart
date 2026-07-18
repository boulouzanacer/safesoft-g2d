import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:dropdown_search/dropdown_search.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../core/network/api_response.dart';
import '../../core/network/dio_client.dart';
import '../../models/commune_model.dart';
import '../../models/wilaya_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/cart_provider.dart';
import '../../providers/commande_provider.dart';

class CheckoutScreen extends ConsumerStatefulWidget {
  const CheckoutScreen({super.key});

  @override
  ConsumerState<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends ConsumerState<CheckoutScreen> {
  final _adresseCtrl = TextEditingController();
  final _teleShippingCtrl = TextEditingController();
  final _notesCtrl = TextEditingController();

  List<WilayaModel> _wilayas = [];
  List<CommuneModel> _communes = [];
  WilayaModel? _selectedWilaya;
  CommuneModel? _selectedCommune;

  String _price(double value) {
    final f = NumberFormat('#,##0.00', 'fr_FR');
    return '${f.format(value)} DA';
  }

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final client = ref.read(authProvider).client;
      if (client?.adresse != null && client!.adresse!.trim().isNotEmpty) {
        _adresseCtrl.text = client.adresse!.trim();
      }
      if ((client?.telephone ?? '').trim().isNotEmpty) {
        _teleShippingCtrl.text = client!.telephone!.trim();
      }
      _loadWilayas();
    });
  }

  @override
  void dispose() {
    _adresseCtrl.dispose();
    _teleShippingCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadWilayas() async {
    try {
      final dio = ref.read(dioProvider);
      final res = await dio.get('/wilayas');
      final api = ApiResponse.fromJson(res.data as Map<String, dynamic>);
      final rows = (api.data as List)
          .whereType<Map<String, dynamic>>()
          .map(WilayaModel.fromJson)
          .toList();
      if (!mounted) return;
      setState(() => _wilayas = rows);
    } catch (_) {}
  }

  Future<void> _loadCommunes(int wilayaId) async {
    try {
      final dio = ref.read(dioProvider);
      final res = await dio.get('/communes/$wilayaId');
      final api = ApiResponse.fromJson(res.data as Map<String, dynamic>);
      final rows = (api.data as List)
          .whereType<Map<String, dynamic>>()
          .map(CommuneModel.fromJson)
          .toList();
      if (!mounted) return;
      setState(() => _communes = rows);
    } catch (_) {}
  }

  Future<void> _showStockError(String message, List<CartItem> items) async {
    final m = RegExp(r'produit\s+(\d+)', caseSensitive: false).firstMatch(message);
    final id = (m != null) ? int.tryParse(m.group(1)!) : null;
    final name = (id == null)
        ? null
        : items
            .where((e) => e.produit.id == id)
            .map((e) => e.produit.designation)
            .cast<String?>()
            .firstWhere((_) => true, orElse: () => null);

    await showDialog<void>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Stock insuffisant'),
        content: Text(name != null ? '$name\n\n$message' : message),
        actions: [
          TextButton(onPressed: () => Navigator.of(ctx).pop(), child: const Text('OK')),
        ],
      ),
    );
  }

  Future<void> _confirm() async {
    final items = ref.read(cartProvider);
    final cart = ref.read(cartProvider.notifier);
    final auth = ref.read(authProvider);

    if (!auth.isAuthenticated) {
      if (!mounted) return;
      context.go('/login');
      return;
    }

    if (items.isEmpty) return;
    if (_selectedWilaya == null || _selectedCommune == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Veuillez sélectionner wilaya et commune')),
      );
      return;
    }

    final adresse = _adresseCtrl.text.trim();
    if (adresse.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Veuillez saisir une adresse complète')),
      );
      return;
    }

    final teleShipping = _teleShippingCtrl.text.trim();
    if (teleShipping.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Veuillez saisir le téléphone de livraison')),
      );
      return;
    }

    final frsId = cart.frsId;
    if (frsId == null) return;

    final body = {
      'id_frs': frsId,
      'adresse_livraison': adresse,
      'tele_shipping': teleShipping,
      'id_wilaya': _selectedWilaya!.idWilaya,
      'id_commune': _selectedCommune!.idCommune,
      'notes': _notesCtrl.text.trim().isEmpty ? null : _notesCtrl.text.trim(),
      'panier': items
          .map((e) => {'id_produit': e.produit.id, 'quantite': e.quantite})
          .toList(),
    }..removeWhere((key, value) => value == null);

    final cmd = await ref.read(commandeProvider.notifier).createCommande(body);
    if (!mounted) return;
    if (cmd != null) {
      ref.read(cartProvider.notifier).clearCart();
      context.go('/order-confirm/${cmd.id}');
      return;
    }

    final err = ref.read(commandeProvider).error ?? 'Erreur';
    if (err.toLowerCase().contains('stock insuffisant')) {
      await _showStockError(err, items);
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(err)));
  }

  @override
  Widget build(BuildContext context) {
    final items = ref.watch(cartProvider);
    final cart = ref.read(cartProvider.notifier);
    final cmdState = ref.watch(commandeProvider);
    final auth = ref.watch(authProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Checkout')),
      body: items.isEmpty
          ? const Center(child: Text('Panier vide'))
          : Builder(
              builder: (context) {
                final children = <Widget>[
                const Text('Livraison',
                    style:
                        TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
                const SizedBox(height: 10),
                DropdownSearch<WilayaModel>(
                  items: _wilayas,
                  selectedItem: _selectedWilaya,
                  itemAsString: (w) => '${w.idWilaya} - ${w.wilaya}',
                  popupProps: const PopupProps.menu(showSearchBox: true),
                  dropdownDecoratorProps: const DropDownDecoratorProps(
                    dropdownSearchDecoration: InputDecoration(labelText: 'Wilaya'),
                  ),
                  onChanged: (w) async {
                    setState(() {
                      _selectedWilaya = w;
                      _selectedCommune = null;
                      _communes = [];
                    });
                    if (w != null) await _loadCommunes(w.idWilaya);
                  },
                ),
                const SizedBox(height: 12),
                DropdownSearch<CommuneModel>(
                  items: _communes,
                  selectedItem: _selectedCommune,
                  itemAsString: (c) => c.commune,
                  popupProps: const PopupProps.menu(showSearchBox: true),
                  dropdownDecoratorProps: const DropDownDecoratorProps(
                    dropdownSearchDecoration: InputDecoration(labelText: 'Commune'),
                  ),
                  onChanged: (c) => setState(() => _selectedCommune = c),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _adresseCtrl,
                  minLines: 3,
                  maxLines: 5,
                  decoration: const InputDecoration(labelText: 'Adresse complète'),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _teleShippingCtrl,
                  keyboardType: TextInputType.phone,
                  decoration: const InputDecoration(
                    labelText: 'Téléphone de livraison',
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _notesCtrl,
                  minLines: 2,
                  maxLines: 4,
                  decoration: const InputDecoration(
                      labelText: 'Notes pour le fournisseur (optionnel)'),
                ),
                const SizedBox(height: 18),
                const Text('Résumé commande',
                    style:
                        TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
                const SizedBox(height: 10),
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(12),
                    child: Column(
                      children: [
                        ...items.map(
                          (e) => Padding(
                            padding: const EdgeInsets.only(bottom: 10),
                            child: Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    e.produit.designation,
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                                Text('x${e.quantite}'),
                                const SizedBox(width: 12),
                                Text(_price(e.sousTotal),
                                    style: const TextStyle(
                                        fontWeight: FontWeight.w700)),
                              ],
                            ),
                          ),
                        ),
                        const Divider(),
                        Row(
                          children: [
                            const Text('Total',
                                style: TextStyle(fontWeight: FontWeight.w900)),
                            const Spacer(),
                            Text(_price(cart.montantTotal),
                                style: const TextStyle(
                                    fontWeight: FontWeight.w900)),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                if (!auth.isAuthenticated)
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () => context.go('/login'),
                      child: const Text('Se connecter'),
                    ),
                  )
                else
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: cmdState.isLoading ? null : _confirm,
                      child: cmdState.isLoading
                          ? const SizedBox(
                              height: 18,
                              width: 18,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Text('Confirmer la commande'),
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
