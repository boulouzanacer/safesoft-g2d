class WilayaModel {
  final int idWilaya;
  final String wilaya;
  final String wilaya2;

  WilayaModel({
    required this.idWilaya,
    required this.wilaya,
    required this.wilaya2,
  });

  factory WilayaModel.fromJson(Map<String, dynamic> json) {
    return WilayaModel(
      idWilaya: (json['ID_WILAYA'] ?? 0) as int,
      wilaya: (json['WILAYA'] ?? '').toString(),
      wilaya2: (json['WILAYA2'] ?? '').toString(),
    );
  }
}

