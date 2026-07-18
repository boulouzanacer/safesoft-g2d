import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

class NotificationService {
  final FlutterLocalNotificationsPlugin _localNotifications = FlutterLocalNotificationsPlugin();

  Future<void> init() async {
    const android = AndroidInitializationSettings('@mipmap/ic_launcher');
    const settings = InitializationSettings(android: android);
    await _localNotifications.initialize(settings);

    await FirebaseMessaging.instance.requestPermission();
    FirebaseMessaging.onMessage.listen(_showForegroundNotification);
  }

  Future<void> _showForegroundNotification(RemoteMessage message) async {
    final title = message.notification?.title ?? 'Notification';
    final body = message.notification?.body ?? '';

    const androidDetails = AndroidNotificationDetails(
      'safesoft_g2d_channel',
      'GrosLink',
      importance: Importance.max,
      priority: Priority.high,
    );

    await _localNotifications.show(
      title.hashCode,
      title,
      body,
      const NotificationDetails(android: androidDetails),
    );
  }
}
