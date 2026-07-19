import 'dart:ui';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../core/constants/app_constants.dart';
import '../l10n/app_i18n.dart';

class LocaleNotifier extends StateNotifier<Locale> {
  LocaleNotifier() : super(const Locale('fr')) {
    loadLocale();
  }

  Future<void> loadLocale() async {
    final prefs = await SharedPreferences.getInstance();
    final code = prefs.getString(AppConstants.storageLocaleKey) ?? 'fr';
    state = _safeLocale(code);
  }

  Future<void> setLocale(String code) async {
    state = _safeLocale(code);
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(AppConstants.storageLocaleKey, state.languageCode);
  }

  Locale _safeLocale(String code) {
    for (final locale in AppI18n.supportedLocales) {
      if (locale.languageCode == code) {
        return locale;
      }
    }
    return const Locale('fr');
  }
}

final localeProvider =
    StateNotifierProvider<LocaleNotifier, Locale>((ref) => LocaleNotifier());
