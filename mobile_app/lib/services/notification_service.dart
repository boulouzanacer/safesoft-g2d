import 'dart:async';
import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:go_router/go_router.dart';

import '../core/constants/api_constants.dart';
import '../core/utils/storage_service.dart';

@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
}

class NotificationService {
  NotificationService._();

  static final NotificationService instance = NotificationService._();

  static GlobalKey<NavigatorState>? _navigatorKey;

  static void setNavigatorKey(GlobalKey<NavigatorState> key) {
    _navigatorKey = key;
  }

  final StorageService _storage = StorageService();
  final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();

  static const String _channelId = 'safesoft_g2d_channel';

  Future<void> init() async {
    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);

    const androidInit = AndroidInitializationSettings('@mipmap/ic_launcher');
    const initSettings = InitializationSettings(android: androidInit);
    await _localNotifications.initialize(
      settings: initSettings,
      onDidReceiveNotificationResponse: (resp) {
        final payload = resp.payload;
        if (payload == null || payload.isEmpty) return;
        try {
          final data = jsonDecode(payload) as Map<String, dynamic>;
          handleNotificationTap(data);
        } catch (_) {}
      },
    );

    await _createAndroidChannel();

    await FirebaseMessaging.instance.requestPermission();

    await _syncToken();

    FirebaseMessaging.instance.onTokenRefresh.listen((_) {
      unawaited(_syncToken());
    });

    FirebaseMessaging.onMessage.listen((message) {
      final title = message.notification?.title ?? 'Notification';
      final body = message.notification?.body ?? '';
      showLocalNotification(title, body, message.data);
    });

    FirebaseMessaging.onMessageOpenedApp.listen((message) {
      handleNotificationTap(message.data);
    });

    final initial = await FirebaseMessaging.instance.getInitialMessage();
    if (initial != null) {
      handleNotificationTap(initial.data);
    }
  }

  void handleNotificationTap(Map<String, dynamic> data) {
    final type = data['type']?.toString();
    final commandeId = data['commande_id']?.toString();

    if (type == 'commande' || commandeId != null) {
      final id = int.tryParse(commandeId ?? '');
      if (id != null) {
        final ctx = _navigatorKey?.currentContext;
        if (ctx != null) {
          GoRouter.of(ctx).go('/commandes/$id');
        }
      }
    }
  }

  Future<void> showLocalNotification(
    String title,
    String body,
    Map<String, dynamic> payload,
  ) async {
    const androidDetails = AndroidNotificationDetails(
      _channelId,
      'SafeSoft G2D',
      importance: Importance.high,
      priority: Priority.high,
      playSound: true,
    );

    await _localNotifications.show(
      id: DateTime.now().millisecondsSinceEpoch ~/ 1000,
      title: title,
      body: body,
      notificationDetails: const NotificationDetails(android: androidDetails),
      payload: jsonEncode(payload),
    );
  }

  Future<void> _createAndroidChannel() async {
    final android = _localNotifications.resolvePlatformSpecificImplementation<
        AndroidFlutterLocalNotificationsPlugin>();
    if (android == null) return;

    const channel = AndroidNotificationChannel(
      _channelId,
      'SafeSoft G2D',
      description: 'Notifications SafeSoft G2D',
      importance: Importance.high,
      playSound: true,
    );

    await android.createNotificationChannel(channel);
  }

  Future<void> _syncToken() async {
    final token = await FirebaseMessaging.instance.getToken();
    if (token == null || token.isEmpty) return;

    final authToken = await _storage.getToken();
    if (authToken == null || authToken.isEmpty) return;

    final dio = Dio(
      BaseOptions(
        baseUrl: ApiConstants.baseUrl,
        connectTimeout: const Duration(seconds: 20),
        receiveTimeout: const Duration(seconds: 20),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $authToken',
        },
      ),
    );

    final deviceType = (!kIsWeb && defaultTargetPlatform == TargetPlatform.iOS)
        ? 'ios'
        : 'android';
    try {
      await dio.post(ApiConstants.fcmToken, data: {
        'token': token,
        'device_type': deviceType,
      });
    } catch (_) {}
  }
}
