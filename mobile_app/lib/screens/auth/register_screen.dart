import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:dropdown_search/dropdown_search.dart';

import '../../core/network/api_response.dart';
import '../../core/network/dio_client.dart';
import '../../core/utils/storage_service.dart';
import '../../core/utils/validators.dart';
import '../../l10n/app_i18n.dart';
import '../../models/commune_model.dart';
import '../../models/wilaya_model.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/common/gradient_button.dart';

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nomCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _confirmCtrl = TextEditingController();
  final _telCtrl = TextEditingController();
  final _adresseCtrl = TextEditingController();

  bool _showPassword = false;
  bool _showConfirm = false;
  bool _cgu = false;

  List<WilayaModel> _wilayas = [];
  List<CommuneModel> _communes = [];
  WilayaModel? _selectedWilaya;
  CommuneModel? _selectedCommune;

  final StorageService _storage = StorageService();

  @override
  void dispose() {
    _nomCtrl.dispose();
    _emailCtrl.dispose();
    _passCtrl.dispose();
    _confirmCtrl.dispose();
    _telCtrl.dispose();
    _adresseCtrl.dispose();
    super.dispose();
  }

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _loadWilayas());
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
      setState(() {
        _wilayas = rows;
      });
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
      setState(() {
        _communes = rows;
      });
    } catch (_) {}
  }

  Future<void> _submit() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;
    if (_selectedWilaya == null || _selectedCommune == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(context.tr('Veuillez sélectionner wilaya et commune'))),
      );
      return;
    }
    if (!_cgu) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(context.tr('Veuillez accepter les CGU'))),
      );
      return;
    }

    final ok = await ref.read(authProvider.notifier).register(
          nom: _nomCtrl.text.trim(),
          email: _emailCtrl.text.trim(),
          password: _passCtrl.text,
          telephone: _telCtrl.text.trim().isEmpty ? null : _telCtrl.text.trim(),
          idWilaya: _selectedWilaya!.idWilaya,
          idCommune: _selectedCommune!.idCommune,
          adresse: _adresseCtrl.text.trim().isEmpty
              ? null
              : _adresseCtrl.text.trim(),
        );

    if (!mounted) return;
    if (ok) {
      await _storage.setOnboardingSeen(true);
      if (!mounted) return;
      context.go('/home');
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authProvider);
    return Scaffold(
      appBar: AppBar(title: Text(context.tr('Inscription'))),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Form(
            key: _formKey,
            child: Builder(
              builder: (context) {
                final children = <Widget>[
                if (auth.error != null) ...[
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.red.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(auth.error!,
                        style: const TextStyle(color: Colors.red)),
                  ),
                  const SizedBox(height: 12),
                ],
                TextFormField(
                  controller: _nomCtrl,
                  decoration: InputDecoration(labelText: context.tr('Nom client')),
                  validator: (value) => Validators.requiredField(context, value),
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _emailCtrl,
                  keyboardType: TextInputType.emailAddress,
                  decoration: InputDecoration(labelText: context.tr('Email')),
                  validator: (value) => Validators.email(context, value),
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _telCtrl,
                  keyboardType: TextInputType.phone,
                  decoration: InputDecoration(labelText: context.tr('Téléphone')),
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _passCtrl,
                  obscureText: !_showPassword,
                  decoration: InputDecoration(
                    labelText: context.tr('Mot de passe'),
                    suffixIcon: IconButton(
                      onPressed: () =>
                          setState(() => _showPassword = !_showPassword),
                      icon: Icon(_showPassword
                          ? Icons.visibility_off
                          : Icons.visibility),
                    ),
                  ),
                  validator: (v) => Validators.password(context, v),
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _confirmCtrl,
                  obscureText: !_showConfirm,
                  decoration: InputDecoration(
                    labelText: context.tr('Confirmer mot de passe'),
                    suffixIcon: IconButton(
                      onPressed: () =>
                          setState(() => _showConfirm = !_showConfirm),
                      icon: Icon(_showConfirm
                          ? Icons.visibility_off
                          : Icons.visibility),
                    ),
                  ),
                  validator: (v) {
                    final err = Validators.password(context, v);
                    if (err != null) return err;
                    if (v != _passCtrl.text) {
                      return context.tr('Les mots de passe ne correspondent pas');
                    }
                    return null;
                  },
                ),
                const SizedBox(height: 12),
                DropdownSearch<WilayaModel>(
                  items: _wilayas,
                  selectedItem: _selectedWilaya,
                  itemAsString: (w) => '${w.idWilaya} - ${w.wilaya}',
                  popupProps: const PopupProps.menu(showSearchBox: true),
                  dropdownDecoratorProps: DropDownDecoratorProps(
                    dropdownSearchDecoration:
                        InputDecoration(labelText: context.tr('Wilaya')),
                  ),
                  onChanged: (w) async {
                    setState(() {
                      _selectedWilaya = w;
                      _selectedCommune = null;
                      _communes = [];
                    });
                    if (w != null) {
                      await _loadCommunes(w.idWilaya);
                    }
                  },
                ),
                const SizedBox(height: 12),
                DropdownSearch<CommuneModel>(
                  items: _communes,
                  selectedItem: _selectedCommune,
                  itemAsString: (c) => c.commune,
                  popupProps: const PopupProps.menu(showSearchBox: true),
                  dropdownDecoratorProps: DropDownDecoratorProps(
                    dropdownSearchDecoration:
                        InputDecoration(labelText: context.tr('Commune')),
                  ),
                  onChanged: (c) => setState(() => _selectedCommune = c),
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _adresseCtrl,
                  decoration: InputDecoration(
                    labelText: context.tr('Adresse (optionnel)'),
                  ),
                  minLines: 2,
                  maxLines: 4,
                ),
                const SizedBox(height: 16),
                CheckboxListTile(
                  value: _cgu,
                  onChanged: (v) => setState(() => _cgu = v ?? false),
                  contentPadding: EdgeInsets.zero,
                  title: Text(context.tr("J'accepte les CGU")),
                ),
                const SizedBox(height: 12),
                GradientButton(
                  onPressed: auth.isLoading ? null : _submit,
                  child: auth.isLoading
                      ? const SizedBox(
                          height: 18,
                          width: 18,
                          child: CircularProgressIndicator(strokeWidth: 2))
                      : Text(context.tr("S'inscrire")),
                ),
                const SizedBox(height: 8),
                TextButton(
                  onPressed: () => context.go('/login'),
                  child: Text(context.tr('J’ai déjà un compte')),
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
