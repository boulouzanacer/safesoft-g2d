import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'dart:convert';

import '../core/constants/api_constants.dart';
import '../core/network/api_response.dart';
import '../core/network/dio_client.dart';
import '../core/utils/storage_service.dart';
import '../models/client_model.dart';

class AuthState {
  final ClientModel? client;
  final String? token;
  final bool isLoading;
  final String? error;

  const AuthState({
    required this.client,
    required this.token,
    required this.isLoading,
    required this.error,
  });

  const AuthState.initial()
      : client = null,
        token = null,
        isLoading = false,
        error = null;

  AuthState copyWith({
    ClientModel? client,
    String? token,
    bool? isLoading,
    String? error,
    bool clearClient = false,
    bool clearToken = false,
    bool clearError = false,
  }) {
    return AuthState(
      client: clearClient ? null : (client ?? this.client),
      token: clearToken ? null : (token ?? this.token),
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : (error ?? this.error),
    );
  }

  bool get isAuthenticated => token != null && token!.isNotEmpty;
  bool get isAbonne => client?.typeClient == 'abonne';
}

class AuthNotifier extends StateNotifier<AuthState> {
  AuthNotifier(this._ref) : super(const AuthState.initial()) {
    loadCurrentUser();
  }

  final Ref _ref;
  final StorageService _storage = StorageService();

  Dio get _dio => _ref.read(dioProvider);

  String _extractErrorMessage(DioException e) {
    final data = e.response?.data;
    if (data is Map<String, dynamic>) {
      final msg = data['message']?.toString();
      final errors = data['errors'];
      if (errors is Map<String, dynamic>) {
        for (final entry in errors.entries) {
          final value = entry.value;
          if (value is List && value.isNotEmpty) {
            final first = value.first?.toString();
            if (first != null && first.isNotEmpty) return first;
          }
          if (value is String && value.isNotEmpty) return value;
        }
      }
      if (msg != null && msg.isNotEmpty) return msg;
    }
    return 'Erreur';
  }

  Future<void> loadCurrentUser() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final token = await _storage.getToken();
    if (token == null || token.isEmpty) {
      state = state.copyWith(
        isLoading: false,
        clearToken: true,
        clearClient: true,
      );
      return;
    }

    try {
      final res = await _dio.get(ApiConstants.authMe);
      final api = ApiResponse.fromJson(res.data as Map<String, dynamic>);
      final client = ClientModel.fromJson(api.data as Map<String, dynamic>);
      await _storage.setClientJson(jsonEncode(client.toJson()));
      state = state.copyWith(isLoading: false, token: token, client: client);
    } on DioException catch (e) {
      await _storage.clearToken();
      await _storage.clearClient();
      state = state.copyWith(
        isLoading: false,
        clearToken: true,
        clearClient: true,
        error: (e.response?.data is Map<String, dynamic>)
            ? ((e.response?.data as Map<String, dynamic>)['message']
                    ?.toString() ??
                'Session expirée')
            : 'Session expirée',
      );
    } catch (_) {
      await _storage.clearToken();
      await _storage.clearClient();
      state = state.copyWith(
        isLoading: false,
        clearToken: true,
        clearClient: true,
        error: 'Session expirée',
      );
    }
  }

  Future<bool> login({required String email, required String password}) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final res = await _dio.post(ApiConstants.authLogin,
          data: {'email': email, 'password': password});
      final api = ApiResponse.fromJson(res.data as Map<String, dynamic>);
      final token = (api.data as Map<String, dynamic>)['token']?.toString();
      final clientJson =
          (api.data as Map<String, dynamic>)['client'] as Map<String, dynamic>;
      final client = ClientModel.fromJson(clientJson);
      if (token != null && token.isNotEmpty) {
        await _storage.setToken(token);
        await _storage.setClientJson(jsonEncode(client.toJson()));
      }
      state = state.copyWith(isLoading: false, token: token, client: client);
      return true;
    } on DioException catch (e) {
      state = state.copyWith(isLoading: false, error: _extractErrorMessage(e));
      return false;
    } catch (_) {
      state = state.copyWith(isLoading: false, error: 'Erreur');
      return false;
    }
  }

  Future<bool> register({
    required String nom,
    required String email,
    required String password,
    String? telephone,
    int? idWilaya,
    int? idCommune,
    String? adresse,
  }) async {
    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final res = await _dio.post(ApiConstants.authRegister, data: {
        'nom': nom,
        'email': email,
        'password': password,
        'telephone': telephone,
        'type_client': 'simple',
        if (idWilaya != null) 'id_wilaya': idWilaya,
        if (idCommune != null) 'id_commune': idCommune,
        if (adresse != null) 'adresse': adresse,
      });
      final api = ApiResponse.fromJson(res.data as Map<String, dynamic>);
      final token = (api.data as Map<String, dynamic>)['token']?.toString();
      final clientJson =
          (api.data as Map<String, dynamic>)['client'] as Map<String, dynamic>;
      final client = ClientModel.fromJson(clientJson);
      if (token != null && token.isNotEmpty) {
        await _storage.setToken(token);
        await _storage.setClientJson(jsonEncode(client.toJson()));
      }
      state = state.copyWith(isLoading: false, token: token, client: client);
      return true;
    } on DioException catch (e) {
      state = state.copyWith(isLoading: false, error: _extractErrorMessage(e));
      return false;
    } catch (_) {
      state = state.copyWith(isLoading: false, error: 'Erreur');
      return false;
    }
  }

  Future<void> logout() async {
    try {
      await _dio.post(ApiConstants.authLogout);
    } catch (_) {}
    await _storage.clearToken();
    await _storage.clearClient();
    state = const AuthState.initial();
  }
}

final authProvider =
    StateNotifierProvider<AuthNotifier, AuthState>((ref) => AuthNotifier(ref));
