import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/constants/api_constants.dart';
import '../core/network/api_response.dart';
import '../core/network/dio_client.dart';
import '../models/boutique_model.dart';
import '../models/produit_model.dart';

class ProduitApi {
  final Dio _dio;

  const ProduitApi(this._dio);

  Future<ProduitModel> fetchProduit(int id) async {
    final res = await _dio.get(ApiConstants.produitDetail(id));
    final api = ApiResponse.fromJson(res.data as Map<String, dynamic>);
    return ProduitModel.fromJson(api.data as Map<String, dynamic>);
  }

  Future<List<String>> fetchCategories({int? frsId}) async {
    final res = await _dio.get(
      ApiConstants.produitCategories,
      queryParameters: {if (frsId != null) 'frs_id': frsId},
    );
    final api = ApiResponse.fromJson(res.data as Map<String, dynamic>);
    return (api.data as List)
        .map((e) => e.toString())
        .where((e) => e.trim().isNotEmpty)
        .toList();
  }

  Future<List<BoutiqueModel>> fetchBoutiques() async {
    final res = await _dio.get(ApiConstants.boutiques);
    final api = ApiResponse.fromJson(res.data as Map<String, dynamic>);
    return (api.data as List)
        .whereType<Map<String, dynamic>>()
        .map(BoutiqueModel.fromJson)
        .toList();
  }
}

final produitApiProvider =
    Provider<ProduitApi>((ref) => ProduitApi(ref.read(dioProvider)));

class ProduitListQuery {
  final int? frsId;

  const ProduitListQuery({this.frsId});

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is ProduitListQuery &&
          runtimeType == other.runtimeType &&
          frsId == other.frsId;

  @override
  int get hashCode => frsId.hashCode;
}

class ProduitListState {
  final List<ProduitModel> produits;
  final int page;
  final bool hasMore;
  final bool isLoading;
  final String? error;
  final String search;
  final String? categorie;

  const ProduitListState({
    required this.produits,
    required this.page,
    required this.hasMore,
    required this.isLoading,
    required this.error,
    required this.search,
    required this.categorie,
  });

  const ProduitListState.initial()
      : produits = const [],
        page = 1,
        hasMore = true,
        isLoading = false,
        error = null,
        search = '',
        categorie = null;

  ProduitListState copyWith({
    List<ProduitModel>? produits,
    int? page,
    bool? hasMore,
    bool? isLoading,
    String? error,
    bool clearError = false,
    String? search,
    String? categorie,
    bool clearCategorie = false,
  }) {
    return ProduitListState(
      produits: produits ?? this.produits,
      page: page ?? this.page,
      hasMore: hasMore ?? this.hasMore,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : (error ?? this.error),
      search: search ?? this.search,
      categorie: clearCategorie ? null : (categorie ?? this.categorie),
    );
  }
}

class ProduitListNotifier extends StateNotifier<ProduitListState> {
  ProduitListNotifier(this._ref, this._query)
      : super(const ProduitListState.initial()) {
    fetchProduits(page: 1);
  }

  final Ref _ref;
  final ProduitListQuery _query;

  Dio get _dio => _ref.read(dioProvider);

  Future<void> fetchProduits({
    int? frsId,
    String? categorie,
    String? search,
    int page = 1,
  }) async {
    final nextFrsId = frsId ?? _query.frsId;
    final nextCategorie = categorie;
    final nextSearch = search;

    final isFirstPage = page <= 1;
    state = state.copyWith(
      isLoading: true,
      clearError: true,
      page: page,
      produits: isFirstPage ? const [] : state.produits,
      search: nextSearch ?? state.search,
      categorie: nextCategorie ?? state.categorie,
    );

    try {
      final res = await _dio.get(
        ApiConstants.produits,
        queryParameters: {
          if (nextFrsId != null) 'frs_id': nextFrsId,
          if ((state.categorie ?? '').isNotEmpty) 'categorie': state.categorie,
          if (state.search.trim().isNotEmpty) 'search': state.search.trim(),
          'page': page,
        },
      );

      final api = ApiResponse.fromJson(res.data as Map<String, dynamic>);
      final data = api.data as Map<String, dynamic>;
      final items = (data['items'] as List?) ?? const [];
      final pagination = (data['pagination'] as Map?) ?? const {};
      final currentPage = (pagination['current_page'] as int?) ?? page;
      final lastPage = (pagination['last_page'] as int?) ?? currentPage;

      final produits = items
          .whereType<Map<String, dynamic>>()
          .map(ProduitModel.fromJson)
          .toList();

      state = state.copyWith(
        isLoading: false,
        produits: isFirstPage ? produits : [...state.produits, ...produits],
        page: currentPage,
        hasMore: currentPage < lastPage,
      );
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

  Future<void> refresh(
      {String? search, String? categorie, bool clearCategorie = false}) async {
    state = state.copyWith(
      search: search ?? state.search,
      categorie: categorie ?? state.categorie,
      clearCategorie: clearCategorie,
    );
    await fetchProduits(page: 1);
  }

  Future<void> loadMore() async {
    if (state.isLoading || !state.hasMore) return;
    await fetchProduits(page: state.page + 1);
  }

  Future<ProduitModel> fetchProduit(int id) async {
    final res = await _dio.get(ApiConstants.produitDetail(id));
    final api = ApiResponse.fromJson(res.data as Map<String, dynamic>);
    return ProduitModel.fromJson(api.data as Map<String, dynamic>);
  }

  Future<List<String>> fetchCategories({int? frsId}) async {
    final res = await _dio.get(
      ApiConstants.produitCategories,
      queryParameters: {if (frsId != null) 'frs_id': frsId},
    );
    final api = ApiResponse.fromJson(res.data as Map<String, dynamic>);
    return (api.data as List)
        .map((e) => e.toString())
        .where((e) => e.trim().isNotEmpty)
        .toList();
  }

  Future<List<BoutiqueModel>> fetchBoutiques() async {
    final res = await _dio.get(ApiConstants.boutiques);
    final api = ApiResponse.fromJson(res.data as Map<String, dynamic>);
    return (api.data as List)
        .whereType<Map<String, dynamic>>()
        .map(BoutiqueModel.fromJson)
        .toList();
  }
}

final produitProvider = StateNotifierProvider.family<
    ProduitListNotifier,
    ProduitListState,
    ProduitListQuery>((ref, query) => ProduitListNotifier(ref, query));

final produitDetailProvider =
    FutureProvider.family<ProduitModel, int>((ref, id) async {
  return ref.read(produitApiProvider).fetchProduit(id);
});

final categoriesProvider =
    FutureProvider.family<List<String>, int?>((ref, frsId) async {
  return ref.read(produitApiProvider).fetchCategories(frsId: frsId);
});

final boutiquesProvider = FutureProvider<List<BoutiqueModel>>((ref) async {
  return ref.read(produitApiProvider).fetchBoutiques();
});
