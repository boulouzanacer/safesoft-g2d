<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;

class BoutiqueController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $client = request()->user();
        $isAbonne = $client instanceof Client && (string) $client->type_client === 'abonne';

        $nbProduits = DB::table('produit')
            ->selectRaw('id_frs, COUNT(*) as nb')
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->when(! $isAbonne, fn ($q) => $q->where('abonne_only', 0))
            ->groupBy('id_frs');

        $rows = Fournisseur::query()
            ->where('actif', 1)
            ->where('is_visible', 1)
            ->whereNull('deleted_at')
            ->when($isAbonne && $client->id_frs, fn ($q) => $q->where('frs.id', (int) $client->id_frs))
            ->leftJoin('wilaya', 'wilaya.ID_WILAYA', '=', 'frs.id_wilaya')
            ->leftJoin('commune', 'commune.ID_COMMUNE', '=', 'frs.id_commune')
            ->leftJoinSub($nbProduits, 'p', fn ($join) => $join->on('p.id_frs', '=', 'frs.id'))
            ->select([
                'frs.id',
                'frs.nom_frs',
                'frs.telephone',
                'frs.logo_path',
                'frs.adresse',
                'frs.id_wilaya',
                'frs.id_commune',
                'frs.latitude',
                'frs.longitude',
                'wilaya.WILAYA as wilaya',
                'commune.COMMUNE as commune',
                DB::raw('COALESCE(p.nb, 0) as nb_produits'),
            ])
            ->orderBy('frs.nom_frs')
            ->get();

        return $this->success($rows, 'Liste des boutiques');
    }

    public function show(int $id)
    {
        $client = request()->user();
        if ($client instanceof Client && (string) $client->type_client === 'abonne' && $client->id_frs && (int) $client->id_frs !== $id) {
            return $this->error('Non autorisé', null, 403);
        }

        $frs = Fournisseur::query()
            ->where('actif', 1)
            ->where('is_visible', 1)
            ->whereNull('deleted_at')
            ->leftJoin('wilaya', 'wilaya.ID_WILAYA', '=', 'frs.id_wilaya')
            ->leftJoin('commune', 'commune.ID_COMMUNE', '=', 'frs.id_commune')
            ->select([
                'frs.id',
                'frs.nom_frs',
                'frs.email',
                'frs.telephone',
                'frs.logo_path',
                'frs.adresse',
                'frs.id_wilaya',
                'frs.id_commune',
                'frs.latitude',
                'frs.longitude',
                'wilaya.WILAYA as wilaya',
                'commune.COMMUNE as commune',
            ])
            ->where('frs.id', $id)
            ->first();

        if (! $frs) {
            return $this->notFound();
        }

        $stats = DB::table('produit')
            ->where('id_frs', $id)
            ->whereNull('deleted_at')
            ->when(! ($client instanceof Client && (string) $client->type_client === 'abonne'), fn ($q) => $q->where('abonne_only', 0))
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN actif=1 THEN 1 ELSE 0 END) as actifs, SUM(CASE WHEN stock<5 THEN 1 ELSE 0 END) as stock_faible')
            ->first();

        $frs->stats = [
            'total_produits' => (int) ($stats->total ?? 0),
            'produits_actifs' => (int) ($stats->actifs ?? 0),
            'produits_stock_faible' => (int) ($stats->stock_faible ?? 0),
        ];

        return $this->success($frs, 'Détail boutique');
    }
}
