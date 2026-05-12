class BoutiqueModel {
  final int id;
  final String nomFrs;
  final String? wilaya;
  final String? commune;
  final int nbProduits;

  final String? telephone;
  final String? adresse;
  final int? idWilaya;
  final int? idCommune;

  BoutiqueModel({
    required this.id,
    required this.nomFrs,
    required this.nbProduits,
    this.wilaya,
    this.commune,
    this.telephone,
    this.adresse,
    this.idWilaya,
    this.idCommune,
  });

  factory BoutiqueModel.fromJson(Map<String, dynamic> json) {
    return BoutiqueModel(
      id: (json['id'] ?? 0) as int,
      nomFrs: (json['nom_frs'] ?? '').toString(),
      wilaya: json['wilaya']?.toString(),
      commune: json['commune']?.toString(),
      nbProduits: (json['nb_produits'] is num)
          ? (json['nb_produits'] as num).toInt()
          : int.tryParse('${json['nb_produits']}') ?? 0,
      telephone: json['telephone']?.toString(),
      adresse: json['adresse']?.toString(),
      idWilaya: (json['id_wilaya'] is num) ? (json['id_wilaya'] as num).toInt() : null,
      idCommune:
          (json['id_commune'] is num) ? (json['id_commune'] as num).toInt() : null,
    );
  }
}
