import 'package:flutter/widgets.dart';

import '../../l10n/app_i18n.dart';

class Validators {
  static String? requiredField(BuildContext context, String? value) {
    if (value == null || value.trim().isEmpty) {
      return context.tr('Champ obligatoire');
    }
    return null;
  }

  static String? email(BuildContext context, String? value) {
    final v = value?.trim() ?? '';
    if (v.isEmpty) return context.tr('Email obligatoire');
    final re = RegExp(r'^[^@]+@[^@]+\.[^@]+$');
    if (!re.hasMatch(v)) return context.tr('Email invalide');
    return null;
  }

  static String? password(BuildContext context, String? value, {int min = 8}) {
    final v = value ?? '';
    if (v.length < min) {
      return context.trArgs(
        'Mot de passe min {min} caractères',
        replacements: {'min': '$min'},
      );
    }
    return null;
  }
}
