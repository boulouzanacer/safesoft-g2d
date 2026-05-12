class ApiConstants {
  static const String baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://g2d-dz.com/api/v1',
  );

  static const String authRegister = '/auth/register';
  static const String authLogin = '/auth/login';
  static const String authLogout = '/auth/logout';
  static const String authMe = '/auth/me';
  static const String authProfil = '/auth/profil';
  static const String authPassword = '/auth/password';

  static const String fcmToken = '/fcm/token';

  static const String boutiques = '/boutiques';
  static String boutiqueDetail(int id) => '/boutiques/$id';

  static const String produits = '/produits';
  static String produitDetail(int id) => '/produits/$id';
  static const String produitCategories = '/produits/categories';

  static const String commandes = '/commandes';
  static String commandeDetail(int id) => '/commandes/$id';

  static const String wilayas = '/wilayas';
  static String communesByWilaya(int wilayaId) => '/communes/$wilayaId';

  static const String notifications = '/notifications';
  static String notificationMarkRead(String id) => '/notifications/$id/lu';
  static const String notificationsMarkAllRead = '/notifications/tout-lire';
  static String notificationDelete(String id) => '/notifications/$id';
}
