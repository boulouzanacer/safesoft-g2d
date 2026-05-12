class CommandeLigneModel {
  final int id;
  final int idProduit;
  final int quantite;
  final double prixUnitaire;
  final double sousTotal;
  final String? produitDesignation;
  final String? produitReference;
  final String? produitImage;

  CommandeLigneModel({
    required this.id,
    required this.idProduit,
    required this.quantite,
    required this.prixUnitaire,
    required this.sousTotal,
    this.produitDesignation,
    this.produitReference,
    this.produitImage,
  });

  factory CommandeLigneModel.fromJson(Map<String, dynamic> json) {
    return CommandeLigneModel(
      id: (json['id'] ?? 0) as int,
      idProduit: (json['id_produit'] ?? 0) as int,
      quantite: (json['quantite'] ?? 0) as int,
      prixUnitaire: (json['prix_unitaire'] is num) ? (json['prix_unitaire'] as num).toDouble() : double.tryParse('${json['prix_unitaire']}') ?? 0,
      sousTotal: (json['sous_total'] is num) ? (json['sous_total'] as num).toDouble() : double.tryParse('${json['sous_total']}') ?? 0,
      produitDesignation: json['produit_designation']?.toString(),
      produitReference: json['produit_reference']?.toString(),
      produitImage: json['produit_image']?.toString(),
    );
  }
}

