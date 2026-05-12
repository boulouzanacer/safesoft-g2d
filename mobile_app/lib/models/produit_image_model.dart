class ProduitImageModel {
  final int id;
  final String filename;
  final String urlPrincipale;
  final String urlThumbnail;
  final int ordre;

  ProduitImageModel({
    required this.id,
    required this.filename,
    required this.urlPrincipale,
    required this.urlThumbnail,
    required this.ordre,
  });

  factory ProduitImageModel.fromJson(Map<String, dynamic> json) {
    return ProduitImageModel(
      id: (json['id'] ?? 0) as int,
      filename: (json['filename'] ?? '').toString(),
      urlPrincipale: (json['url_principale'] ?? '').toString(),
      urlThumbnail: (json['url_thumbnail'] ?? '').toString(),
      ordre: (json['ordre'] ?? 0) as int,
    );
  }
}

