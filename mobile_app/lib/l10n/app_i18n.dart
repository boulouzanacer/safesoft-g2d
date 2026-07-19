import 'package:flutter/material.dart';

class AppI18n {
  static const supportedLocales = <Locale>[
    Locale('fr'),
    Locale('ar'),
  ];

  static const localeOptions = <Map<String, String>>[
    {'code': 'fr', 'label': 'Français', 'flag': '🇫🇷'},
    {'code': 'ar', 'label': 'العربية', 'flag': '🇩🇿'},
  ];

  static const Map<String, Map<String, String>> _translations = {
    'ar': {
      'Accueil': 'الرئيسية',
      'Produits': 'المنتجات',
      'Panier': 'السلة',
      'Profil': 'الملف الشخصي',
      'Mode hors ligne': 'وضع عدم الاتصال',
      'Tous': 'الكل',
      'Non connecté': 'غير متصل',
      'Se connecter': 'تسجيل الدخول',
      'Mes informations': 'معلوماتي',
      'Mes commandes': 'طلباتي',
      'Notifications': 'الإشعارات',
      'Mode sombre': 'الوضع الداكن',
      'Changer mot de passe': 'تغيير كلمة المرور',
      'Support': 'الدعم',
      'Contactez-nous : support@safesoft.dz': 'اتصل بنا: support@safesoft.dz',
      'Déconnexion': 'تسجيل الخروج',
      'Abonné': 'مشترك',
      'Client Simple': 'عميل عادي',
      'OK': 'حسناً',
      'Langue': 'اللغة',
      'Choisir la langue': 'اختر اللغة',
      'Connexion Abonné': 'دخول المشترك',
      'Email': 'البريد الإلكتروني',
      'Mot de passe': 'كلمة المرور',
      'Pas encore inscrit ? Créer un compte': 'لست مسجلاً بعد؟ أنشئ حساباً',
      'Inscription': 'التسجيل',
      'Veuillez sélectionner wilaya et commune': 'يرجى اختيار الولاية والبلدية',
      'Veuillez accepter les CGU': 'يرجى قبول الشروط العامة',
      'Nom client': 'اسم العميل',
      'Téléphone': 'الهاتف',
      'Confirmer mot de passe': 'تأكيد كلمة المرور',
      'Les mots de passe ne correspondent pas': 'كلمتا المرور غير متطابقتين',
      'Wilaya': 'الولاية',
      'Commune': 'البلدية',
      'Adresse (optionnel)': 'العنوان (اختياري)',
      'J\'accepte les CGU': 'أوافق على الشروط العامة',
      'S\'inscrire': 'إنشاء حساب',
      'J’ai déjà un compte': 'لدي حساب بالفعل',
      'Rechercher un produit': 'ابحث عن منتج',
      'Voir tout': 'عرض الكل',
      'produits': 'منتجات',
      'Bienvenue, ': 'مرحباً، ',
      'Nos Boutiques': 'متاجرنا',
      'Tous les produits': 'كل المنتجات',
      'Catégories': 'الفئات',
      'Réessayer': 'إعادة المحاولة',
      'Erreur': 'خطأ',
      'Aucun produit': 'لا توجد منتجات',
      'Essayez de modifier votre recherche.': 'جرّب تعديل البحث.',
      'Ajouté au panier': 'تمت الإضافة إلى السلة',
      'Ref:': 'المرجع:',
      'Votre panier est vide': 'سلتك فارغة',
      'Parcourir les produits': 'تصفح المنتجات',
      'unité': 'وحدة',
      'Sous-total:': 'المجموع الفرعي:',
      'Sous-total :': 'المجموع الفرعي :',
      'Livraison :': 'التوصيل :',
      'À calculer': 'سيتم الحساب لاحقاً',
      'TOTAL :': 'الإجمالي :',
      'Passer la commande': 'تأكيد الطلب',
      'Se connecter pour commander': 'سجل الدخول لإتمام الطلب',
      'Stock insuffisant': 'المخزون غير كاف',
      'Veuillez saisir une adresse complète': 'يرجى إدخال عنوان كامل',
      'Veuillez saisir le téléphone de livraison': 'يرجى إدخال هاتف التوصيل',
      'Checkout': 'إتمام الطلب',
      'Panier vide': 'السلة فارغة',
      'Livraison': 'التوصيل',
      'Adresse complète': 'العنوان الكامل',
      'Téléphone de livraison': 'هاتف التوصيل',
      'Notes pour le fournisseur (optionnel)': 'ملاحظات للمورد (اختياري)',
      'Résumé commande': 'ملخص الطلب',
      'Confirmer la commande': 'تأكيد الطلب',
      'Connectez-vous pour voir vos commandes': 'سجل الدخول لرؤية طلباتك',
      'Aucune commande': 'لا توجد طلبات',
      'Vos commandes apparaîtront ici après validation.': 'ستظهر طلباتك هنا بعد التأكيد.',
      'Commande': 'طلب',
      'Fournisseur': 'المتجر',
      'Commande introuvable': 'الطلب غير موجود',
      'Date': 'التاريخ',
      'Adresse de livraison': 'عنوان التوصيل',
      'Notes': 'ملاحظات',
      'Détail produit': 'تفاصيل المنتج',
      'Impossible de charger le produit': 'تعذر تحميل المنتج',
      'Référence': 'المرجع',
      'Total:': 'الإجمالي:',
      'Tarifs par quantité': 'أسعار حسب الكمية',
      'pièces': 'قطع',
      'Champ obligatoire': 'حقل إجباري',
      'Email obligatoire': 'البريد الإلكتروني إجباري',
      'Email invalide': 'البريد الإلكتروني غير صالح',
      'Mot de passe min {min} caractères': 'كلمة المرور يجب أن تحتوي على {min} أحرف على الأقل'
    },
  };

  static String translate(Locale locale, String key) {
    final code = locale.languageCode;
    return _translations[code]?[key] ?? key;
  }

  static String translateWithArgs(
    Locale locale,
    String key, {
    Map<String, String> replacements = const {},
  }) {
    var result = translate(locale, key);
    replacements.forEach((token, value) {
      result = result.replaceAll('{$token}', value);
    });
    return result;
  }
}

extension AppI18nBuildContext on BuildContext {
  String tr(String key) => AppI18n.translate(Localizations.localeOf(this), key);

  String trArgs(String key, {Map<String, String> replacements = const {}}) =>
      AppI18n.translateWithArgs(
        Localizations.localeOf(this),
        key,
        replacements: replacements,
      );

  bool get isRtlLanguage => Directionality.of(this) == TextDirection.rtl;
}
