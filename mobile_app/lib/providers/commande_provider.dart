import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/constants/api_constants.dart';
import '../core/network/api_response.dart';
import '../core/network/dio_client.dart';
import '../models/commande_model.dart';

class CommandeState {
  final List<CommandeModel> commandes;
  final CommandeModel? currentCommande;
  final bool isLoading;
  final String? error;

  const CommandeState({
    required this.commandes,
    required this.currentCommande,
    required this.isLoading,
    required this.error,
  });

  const CommandeState.initial()
      : commandes = const [],
        currentCommande = null,
        isLoading = false,
        error = null;

  CommandeState copyWith({
    List<CommandeModel>? commandes,
    CommandeModel? currentCommande,
    bool? isLoading,
    String? error,
    bool clearError = false,
  }) {
    return CommandeState(
      commandes: commandes ?? this.commandes,
      currentCommande: currentCommande ?? this.currentCommande,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : (error ?? this.error),
    );
  }
}

class CommandeNotifier extends StateNotifier<CommandeState> {
  CommandeNotifier(this._ref) : super(const CommandeState.initial()) {
    fetchMesCommandes();
  }

  final Ref _ref;
  Dio get _dio => _ref.read(dioProvider);

  Future<void> fetchMesCommandes() async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final res = await _dio.get(ApiConstants.commandes);
      final api = ApiResponse.fromJson(res.data as Map<String, dynamic>);
      final items = (api.data as Map<String, dynamic>)['items'] as List;
      final commandes = items
          .whereType<Map<String, dynamic>>()
          .map(CommandeModel.fromJson)
          .toList();
      state = state.copyWith(isLoading: false, commandes: commandes);
    } on DioException catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: (e.response?.data is Map<String, dynamic>)
            ? ((e.response?.data as Map<String, dynamic>)['message']
                    ?.toString() ??
                'Erreur')
            : 'Erreur',
      );
    } catch (_) {
      state = state.copyWith(isLoading: false, error: 'Erreur');
    }
  }

  Future<CommandeModel?> fetchCommande(int id) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final res = await _dio.get(ApiConstants.commandeDetail(id));
      final api = ApiResponse.fromJson(res.data as Map<String, dynamic>);
      final data = api.data as Map<String, dynamic>;
      final cmd = (data['commande'] as Map<String, dynamic>);
      final lignes = (data['lignes'] as List?) ?? const [];
      final merged = <String, dynamic>{
        ...cmd,
        'nom_frs': cmd['nom_frs'],
        'lignes': lignes,
      };
      final model = CommandeModel.fromJson(merged);
      state = state.copyWith(isLoading: false, currentCommande: model);
      return model;
    } on DioException catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: (e.response?.data is Map<String, dynamic>)
            ? ((e.response?.data as Map<String, dynamic>)['message']
                    ?.toString() ??
                'Erreur')
            : 'Erreur',
      );
      return null;
    } catch (_) {
      state = state.copyWith(isLoading: false, error: 'Erreur');
      return null;
    }
  }

  Future<CommandeModel?> createCommande(Map<String, dynamic> body) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final res = await _dio.post(ApiConstants.commandes, data: body);
      final api = ApiResponse.fromJson(res.data as Map<String, dynamic>);
      final data = api.data as Map<String, dynamic>;
      final cmd = (data['commande'] as Map<String, dynamic>);
      final lignes = (data['lignes'] as List?) ?? const [];
      final merged = <String, dynamic>{
        ...cmd,
        'lignes': lignes,
      };
      final model = CommandeModel.fromJson(merged);
      state = state.copyWith(
        isLoading: false,
        currentCommande: model,
        commandes: [model, ...state.commandes],
      );
      return model;
    } on DioException catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: (e.response?.data is Map<String, dynamic>)
            ? ((e.response?.data as Map<String, dynamic>)['message']
                    ?.toString() ??
                'Erreur')
            : 'Erreur',
      );
      return null;
    } catch (_) {
      state = state.copyWith(isLoading: false, error: 'Erreur');
      return null;
    }
  }
}

final commandeProvider = StateNotifierProvider<CommandeNotifier, CommandeState>(
    (ref) => CommandeNotifier(ref));
