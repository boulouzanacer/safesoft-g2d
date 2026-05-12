import 'produit_image_model.dart';

class ProduitModel {
  final int id;
  final int idFrs;
  final String? nomFrs;
  final String reference;
  final String designation;
  final String description;
  final double prix;
  final int stock;
  final String? imagePrincipale;
  final String categorie;
  final int actif;
  final List<ProduitImageModel> images;

  ProduitModel({
    required this.id,
    required this.idFrs,
    required this.reference,
    required this.designation,
    required this.description,
    required this.prix,
    required this.stock,
    required this.categorie,
    required this.actif,
    this.nomFrs,
    this.imagePrincipale,
    this.images = const [],
  });

  factory ProduitModel.fromJson(Map<String, dynamic> json) {
    final imgs = (json['images'] is List) ? (json['images'] as List).whereType<Map<String, dynamic>>().map(ProduitImageModel.fromJson).toList() : <ProduitImageModel>[];
    return ProduitModel(
      id: (json['id'] ?? 0) as int,
      idFrs: (json['id_frs'] ?? 0) as int,
      nomFrs: json['nom_frs']?.toString(),
      reference: (json['reference'] ?? '').toString(),
      designation: (json['designation'] ?? '').toString(),
      description: (json['description'] ?? '').toString(),
      prix: (json['prix'] is num) ? (json['prix'] as num).toDouble() : double.tryParse('${json['prix']}') ?? 0,
      stock: (json['stock'] ?? 0) as int,
      imagePrincipale: json['image_principale']?.toString(),
      categorie: (json['categorie'] ?? '').toString(),
      actif: (json['actif'] ?? 0) as int,
      images: imgs,
    );
  }
}

