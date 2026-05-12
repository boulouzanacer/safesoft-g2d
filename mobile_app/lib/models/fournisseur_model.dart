class FournisseurModel {
  final int id;
  final String nomFrs;
  final String? email;
  final String? telephone;

  FournisseurModel({
    required this.id,
    required this.nomFrs,
    this.email,
    this.telephone,
  });

  factory FournisseurModel.fromJson(Map<String, dynamic> json) {
    return FournisseurModel(
      id: (json['id'] ?? 0) as int,
      nomFrs: (json['nom_frs'] ?? '').toString(),
      email: json['email']?.toString(),
      telephone: json['telephone']?.toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nom_frs': nomFrs,
      'email': email,
      'telephone': telephone,
    };
  }
}
