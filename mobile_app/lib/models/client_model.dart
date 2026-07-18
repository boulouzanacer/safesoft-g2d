import 'fournisseur_model.dart';

class ClientModel {
  final int id;
  final String nom;
  final String email;
  final String? telephone;
  final String? adresse;
  final int? idWilaya;
  final int? idCommune;
  final String typeClient;
  final int? idFrs;
  final FournisseurModel? fournisseur;

  ClientModel({
    required this.id,
    required this.nom,
    required this.email,
    required this.typeClient,
    this.telephone,
    this.adresse,
    this.idWilaya,
    this.idCommune,
    this.idFrs,
    this.fournisseur,
  });

  factory ClientModel.fromJson(Map<String, dynamic> json) {
    final nom = (json['nom'] ?? '').toString().trim();
    final prenom = (json['prenom'] ?? '').toString().trim();
    final fullName = [prenom, nom].where((value) => value.isNotEmpty).join(' ');

    return ClientModel(
      id: (json['id'] ?? 0) as int,
      nom: fullName.isEmpty ? nom : fullName,
      email: (json['email'] ?? '').toString(),
      telephone: json['telephone']?.toString(),
      adresse: json['adresse']?.toString(),
      idWilaya: json['id_wilaya'] is int
          ? json['id_wilaya'] as int
          : int.tryParse('${json['id_wilaya']}'),
      idCommune: json['id_commune'] is int
          ? json['id_commune'] as int
          : int.tryParse('${json['id_commune']}'),
      typeClient: (json['type_client'] ?? 'simple').toString(),
      idFrs: json['id_frs'] is int
          ? json['id_frs'] as int
          : int.tryParse('${json['id_frs']}'),
      fournisseur: json['fournisseur'] is Map<String, dynamic>
          ? FournisseurModel.fromJson(
              json['fournisseur'] as Map<String, dynamic>)
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nom': nom,
      'email': email,
      'telephone': telephone,
      'adresse': adresse,
      'id_wilaya': idWilaya,
      'id_commune': idCommune,
      'type_client': typeClient,
      'id_frs': idFrs,
      'fournisseur': fournisseur?.toJson(),
    };
  }

  String get firstName {
    final value = nom.trim();
    if (value.isEmpty) return '';
    return value.split(RegExp(r'\s+')).first;
  }
}
