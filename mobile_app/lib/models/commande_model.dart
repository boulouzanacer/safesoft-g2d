import 'commande_ligne_model.dart';

class CommandeModel {
  final int id;
  final int idFrs;
  final String? nomFrs;
  final String dateCmd;
  final String statut;
  final double montantTotal;
  final int syncedPme;
  final String? adresseLivraison;
  final int? idWilaya;
  final int? idCommune;
  final String? notes;
  final List<CommandeLigneModel> lignes;

  CommandeModel({
    required this.id,
    required this.idFrs,
    required this.dateCmd,
    required this.statut,
    required this.montantTotal,
    required this.syncedPme,
    this.nomFrs,
    this.adresseLivraison,
    this.idWilaya,
    this.idCommune,
    this.notes,
    this.lignes = const [],
  });

  factory CommandeModel.fromJson(Map<String, dynamic> json) {
    final lignes = (json['lignes'] is List)
        ? (json['lignes'] as List)
            .whereType<Map<String, dynamic>>()
            .map(CommandeLigneModel.fromJson)
            .toList()
        : <CommandeLigneModel>[];

    return CommandeModel(
      id: (json['id'] ?? 0) as int,
      idFrs: (json['id_frs'] ?? 0) as int,
      nomFrs: json['nom_frs']?.toString(),
      dateCmd: (json['date_cmd'] ?? '').toString(),
      statut: (json['statut'] ?? '').toString(),
      montantTotal: (json['montant_total'] is num)
          ? (json['montant_total'] as num).toDouble()
          : double.tryParse('${json['montant_total']}') ?? 0,
      syncedPme: (json['synced_pme'] ?? 0) as int,
      adresseLivraison: json['adresse_livraison']?.toString(),
      idWilaya: (json['id_wilaya'] is num)
          ? (json['id_wilaya'] as num).toInt()
          : int.tryParse('${json['id_wilaya']}'),
      idCommune: (json['id_commune'] is num)
          ? (json['id_commune'] as num).toInt()
          : int.tryParse('${json['id_commune']}'),
      notes: json['notes']?.toString(),
      lignes: lignes,
    );
  }
}
