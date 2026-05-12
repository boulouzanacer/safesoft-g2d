import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../constants/app_constants.dart';

class StorageService {
  static const FlutterSecureStorage _storage = FlutterSecureStorage();

  Future<void> setToken(String token) =>
      _storage.write(key: AppConstants.storageTokenKey, value: token);

  Future<String?> getToken() =>
      _storage.read(key: AppConstants.storageTokenKey);

  Future<void> clearToken() =>
      _storage.delete(key: AppConstants.storageTokenKey);

  Future<void> setClientJson(String json) =>
      _storage.write(key: AppConstants.storageClientKey, value: json);

  Future<String?> getClientJson() =>
      _storage.read(key: AppConstants.storageClientKey);

  Future<void> clearClient() =>
      _storage.delete(key: AppConstants.storageClientKey);

  Future<void> setOnboardingSeen(bool seen) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(AppConstants.onboardingSeenKey, seen);
  }

  Future<bool> getOnboardingSeen() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getBool(AppConstants.onboardingSeenKey) ?? false;
  }
}
