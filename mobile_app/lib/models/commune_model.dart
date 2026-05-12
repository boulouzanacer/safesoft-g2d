class CommuneModel {
  final int idCommune;
  final String commune;
  final int idWilaya;

  CommuneModel({
    required this.idCommune,
    required this.commune,
    required this.idWilaya,
  });

  factory CommuneModel.fromJson(Map<String, dynamic> json) {
    return CommuneModel(
      idCommune: (json['ID_COMMUNE'] ?? 0) as int,
      commune: (json['COMMUNE'] ?? '').toString(),
      idWilaya: (json['ID_WILAYA'] ?? 0) as int,
    );
  }
}

