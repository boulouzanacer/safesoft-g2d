import 'package:dropdown_search/dropdown_search.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/network/api_response.dart';
import '../../core/network/dio_client.dart';
import '../../models/commune_model.dart';
import '../../models/wilaya_model.dart';
import '../../providers/auth_provider.dart';

class EditProfileScreen extends ConsumerStatefulWidget {
  const EditProfileScreen({super.key});

  @override
  ConsumerState<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends ConsumerState<EditProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  final _prenomCtrl = TextEditingController();
  final _nomCtrl = TextEditingController();
  final _telCtrl = TextEditingController();
  final _adresseCtrl = TextEditingController();

  List<WilayaModel> _wilayas = [];
  List<CommuneModel> _communes = [];
  WilayaModel? _selectedWilaya;
  CommuneModel? _selectedCommune;

  bool _saving = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      final client = ref.read(authProvider).client;
      if (client != null) {
        _prenomCtrl.text = client.prenom;
        _nomCtrl.text = client.nom;
        _telCtrl.text = client.telephone ?? '';
        _adresseCtrl.text = client.adresse ?? '';
      }
      await _loadWilayas();
      if (!mounted) return;
      final c = client;
      if (c == null) return;
      if (c.idWilaya != null) {
        _selectedWilaya =
            _wilayas.where((w) => w.idWilaya == c.idWilaya).firstOrNull;
        if (_selectedWilaya != null) {
          await _loadCommunes(_selectedWilaya!.idWilaya);
          if (!mounted) return;
          if (c.idCommune != null) {
            _selectedCommune = _communes
                .where((cm) => cm.idCommune == c.idCommune)
                .firstOrNull;
          }
        }
        setState(() {});
      }
    });
  }

  @override
  void dispose() {
    _prenomCtrl.dispose();
    _nomCtrl.dispose();
    _telCtrl.dispose();
    _adresseCtrl.dispose();
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

  Future<void> _save() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;
    if (_selectedWilaya == null || _selectedCommune == null) {
      setState(() => _error = 'Veuillez sélectionner wilaya et commune');
      return;
    }

    setState(() {
      _saving = true;
      _error = null;
    });

    try {
      final dio = ref.read(dioProvider);
      await dio.put('/auth/profil', data: {
        'prenom': _prenomCtrl.text.trim(),
        'nom': _nomCtrl.text.trim(),
        'telephone': _telCtrl.text.trim().isEmpty ? null : _telCtrl.text.trim(),
        'adresse': _adresseCtrl.text.trim(),
        'id_wilaya': _selectedWilaya!.idWilaya,
        'id_commune': _selectedCommune!.idCommune,
      });

      await ref.read(authProvider.notifier).loadCurrentUser();
      if (!mounted) return;
      context.pop();
    } catch (e) {
      setState(() => _error = 'Erreur lors de la mise à jour');
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final client = ref.watch(authProvider).client;
    if (client == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Mes informations')),
        body: Center(
          child: ElevatedButton(
            onPressed: () => context.go('/login'),
            child: const Text('Se connecter'),
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Mes informations')),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Form(
            key: _formKey,
            child: Builder(
              builder: (context) {
                final children = <Widget>[
                  if (_error != null) ...[
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.red.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(_error!,
                          style: const TextStyle(color: Colors.red)),
                    ),
                    const SizedBox(height: 12),
                  ],
                  TextFormField(
                    controller: _prenomCtrl,
                    decoration: const InputDecoration(labelText: 'Prénom'),
                    validator: (v) =>
                        (v == null || v.trim().isEmpty) ? 'Champ requis' : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _nomCtrl,
                    decoration: const InputDecoration(labelText: 'Nom'),
                    validator: (v) =>
                        (v == null || v.trim().isEmpty) ? 'Champ requis' : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _telCtrl,
                    decoration: const InputDecoration(labelText: 'Téléphone'),
                    keyboardType: TextInputType.phone,
                  ),
                  const SizedBox(height: 12),
                  DropdownSearch<WilayaModel>(
                    items: _wilayas,
                    selectedItem: _selectedWilaya,
                    itemAsString: (w) => '${w.idWilaya} - ${w.wilaya}',
                    popupProps: const PopupProps.menu(showSearchBox: true),
                    dropdownDecoratorProps: const DropDownDecoratorProps(
                      dropdownSearchDecoration:
                          InputDecoration(labelText: 'Wilaya'),
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
                      dropdownSearchDecoration:
                          InputDecoration(labelText: 'Commune'),
                    ),
                    onChanged: (c) => setState(() => _selectedCommune = c),
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _adresseCtrl,
                    decoration: const InputDecoration(labelText: 'Adresse'),
                    minLines: 2,
                    maxLines: 4,
                    validator: (v) =>
                        (v == null || v.trim().isEmpty) ? 'Champ requis' : null,
                  ),
                  const SizedBox(height: 16),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: _saving ? null : _save,
                      child: _saving
                          ? const SizedBox(
                              height: 18,
                              width: 18,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Text('Enregistrer'),
                    ),
                  ),
                ];

                return ListView.builder(
                  itemCount: children.length,
                  itemBuilder: (_, i) => children[i],
                );
              },
            ),
          ),
        ),
      ),
    );
  }
}

extension _FirstOrNull<T> on Iterable<T> {
  T? get firstOrNull => isEmpty ? null : first;
}
