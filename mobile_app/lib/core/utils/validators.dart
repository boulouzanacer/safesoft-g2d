class Validators {
  static String? requiredField(String? value) {
    if (value == null || value.trim().isEmpty) return 'Champ obligatoire';
    return null;
  }

  static String? email(String? value) {
    final v = value?.trim() ?? '';
    if (v.isEmpty) return 'Email obligatoire';
    final re = RegExp(r'^[^@]+@[^@]+\.[^@]+$');
    if (!re.hasMatch(v)) return 'Email invalide';
    return null;
  }

  static String? password(String? value, {int min = 8}) {
    final v = value ?? '';
    if (v.length < min) return 'Mot de passe min $min caractères';
    return null;
  }
}

