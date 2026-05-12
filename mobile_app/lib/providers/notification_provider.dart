import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../core/constants/api_constants.dart';
import '../core/network/api_response.dart';
import '../core/network/dio_client.dart';
import '../models/notification_model.dart';
import 'auth_provider.dart';

class NotificationState {
  final List<NotificationModel> notifications;
  final int nonLues;
  final bool isLoading;
  final String? error;

  const NotificationState({
    required this.notifications,
    required this.nonLues,
    required this.isLoading,
    required this.error,
  });

  const NotificationState.initial()
      : notifications = const [],
        nonLues = 0,
        isLoading = false,
        error = null;

  NotificationState copyWith({
    List<NotificationModel>? notifications,
    int? nonLues,
    bool? isLoading,
    String? error,
    bool clearError = false,
  }) {
    return NotificationState(
      notifications: notifications ?? this.notifications,
      nonLues: nonLues ?? this.nonLues,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : (error ?? this.error),
    );
  }
}

class NotificationNotifier extends StateNotifier<NotificationState> {
  NotificationNotifier(this._ref) : super(const NotificationState.initial()) {
    _ref.listen<AuthState>(authProvider, (prev, next) {
      final prevAuth = prev?.isAuthenticated ?? false;
      if (prevAuth != next.isAuthenticated) {
        if (next.isAuthenticated) {
          fetchNotifications();
        } else {
          state = const NotificationState.initial();
        }
      }
    });

    if (_ref.read(authProvider).isAuthenticated) {
      fetchNotifications();
    }
  }

  final Ref _ref;
  Dio get _dio => _ref.read(dioProvider);

  Future<void> fetchNotifications() async {
    if (!_ref.read(authProvider).isAuthenticated) {
      state = const NotificationState.initial();
      return;
    }

    state = state.copyWith(isLoading: true, clearError: true);
    try {
      final res = await _dio.get(ApiConstants.notifications);
      final api = ApiResponse.fromJson(res.data as Map<String, dynamic>);
      final data = api.data as Map<String, dynamic>;
      final nonLues = (data['non_lues'] ?? 0) as int;
      final items = (data['notifications'] as List)
          .whereType<Map<String, dynamic>>()
          .map(NotificationModel.fromJson)
          .toList();
      state = state.copyWith(
        isLoading: false,
        notifications: items,
        nonLues: nonLues,
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

  Future<void> marquerLue(String id) async {
    try {
      await _dio.put(ApiConstants.notificationMarkRead(id));
      await fetchNotifications();
    } catch (_) {}
  }

  Future<void> toutMarquerLu() async {
    try {
      await _dio.put(ApiConstants.notificationsMarkAllRead);
      await fetchNotifications();
    } catch (_) {}
  }

  Future<void> supprimer(String id) async {
    try {
      await _dio.delete(ApiConstants.notificationDelete(id));
      await fetchNotifications();
    } catch (_) {}
  }
}

final notificationProvider =
    StateNotifierProvider<NotificationNotifier, NotificationState>(
        (ref) => NotificationNotifier(ref));
