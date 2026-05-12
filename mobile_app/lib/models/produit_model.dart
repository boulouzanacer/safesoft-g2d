import 'produit_image_model.dart';

class QuantityPriceTier {
  final int quantityMin;
  final int? quantityMax;
  final double price;

  const QuantityPriceTier({
    required this.quantityMin,
    required this.quantityMax,
    required this.price,
  });

  factory QuantityPriceTier.fromJson(Map<String, dynamic> json) {
    final maxRaw = json['quantity_max'];
    return QuantityPriceTier(
      quantityMin: (json['quantity_min'] is num)
          ? (json['quantity_min'] as num).toInt()
          : int.tryParse('${json['quantity_min']}') ?? 1,
      quantityMax: maxRaw == null
          ? null
          : (maxRaw is num ? maxRaw.toInt() : int.tryParse('$maxRaw')),
      price: (json['price'] is num)
          ? (json['price'] as num).toDouble()
          : double.tryParse('${json['price']}') ?? 0,
    );
  }
}

class ProduitModel {
  final int id;
  final int idFrs;
  final String? nomFrs;
  final String reference;
  final String designation;
  final String description;
  final double prix;
  final bool enableTierPricing;
  final List<QuantityPriceTier> quantityPrices;
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
    this.enableTierPricing = false,
    this.quantityPrices = const [],
  });

  bool get hasTierPricing => enableTierPricing && quantityPrices.isNotEmpty;

  double unitPriceForQty(int qty) {
    final q = qty < 1 ? 1 : qty;
    if (!hasTierPricing) return prix;

    final sorted = [...quantityPrices]
      ..sort((a, b) => a.quantityMin.compareTo(b.quantityMin));

    for (int i = sorted.length - 1; i >= 0; i--) {
      final t = sorted[i];
      if (q < t.quantityMin) continue;
      if (t.quantityMax == null || q <= t.quantityMax!) {
        return t.price;
      }
    }

    return prix;
  }

  factory ProduitModel.fromJson(Map<String, dynamic> json) {
    final imgs = (json['images'] is List) ? (json['images'] as List).whereType<Map<String, dynamic>>().map(ProduitImageModel.fromJson).toList() : <ProduitImageModel>[];
    final tiers = (json['quantity_prices'] is List)
        ? (json['quantity_prices'] as List)
            .whereType<Map<String, dynamic>>()
            .map(QuantityPriceTier.fromJson)
            .toList()
        : <QuantityPriceTier>[];
    return ProduitModel(
      id: (json['id'] ?? 0) as int,
      idFrs: (json['id_frs'] ?? 0) as int,
      nomFrs: json['nom_frs']?.toString(),
      reference: (json['reference'] ?? '').toString(),
      designation: (json['designation'] ?? '').toString(),
      description: (json['description'] ?? '').toString(),
      prix: (json['prix'] is num) ? (json['prix'] as num).toDouble() : double.tryParse('${json['prix']}') ?? 0,
      enableTierPricing: json['enable_tier_pricing'] == true ||
          json['enable_tier_pricing'] == 1 ||
          json['enable_tier_pricing'] == '1',
      quantityPrices: tiers,
      stock: (json['stock'] ?? 0) as int,
      imagePrincipale: json['image_principale']?.toString(),
      categorie: (json['categorie'] ?? '').toString(),
      actif: (json['actif'] ?? 0) as int,
      images: imgs,
    );
  }
}
