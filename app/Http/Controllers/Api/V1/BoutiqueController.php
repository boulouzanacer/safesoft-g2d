<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BoutiqueCategory;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Services\ClientBoutiqueManager;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;

class BoutiqueController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly ClientBoutiqueManager $clientBoutiqueManager)
    {
    }

    public function categories()
    {
        $rows = BoutiqueCategory::query()
            ->withCount([
                'fournisseurs as nb_boutiques' => function ($query) {
                    $query->where('actif', 1)
                        ->whereNull('deleted_at');
                },
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'image_path']);

        return $this->success($rows, 'Liste des categories boutiques');
    }

    public function index()
    {
        $client = $this->clientBoutiqueManager->resolveAuthenticatedClient(request()->user());
        $abonneFournisseurIds = $this->clientBoutiqueManager->abonneFournisseurIds($client);

        $nbProduits = DB::table('produit')
            ->selectRaw('id_frs, COUNT(*) as nb')
            ->whereNull('deleted_at')
            ->where('actif', 1)
            ->where(function ($q) use ($abonneFournisseurIds) {
                $q->where('abonne_only', 0);

                if (count($abonneFournisseurIds) > 0) {
                    $q->orWhereIn('id_frs', $abonneFournisseurIds);
                }
            })
            ->groupBy('id_frs');

        $rows = Fournisseur::query()
            ->where('actif', 1)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($abonneFournisseurIds) {
                $q->where('is_visible', 1);

                if (count($abonneFournisseurIds) > 0) {
                    $q->orWhereIn('frs.id', $abonneFournisseurIds);
                }
            })
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
        $client = $this->clientBoutiqueManager->resolveAuthenticatedClient(request()->user());
        $abonneFournisseurIds = $this->clientBoutiqueManager->abonneFournisseurIds($client);
        $isAbonneForBoutique = in_array($id, $abonneFournisseurIds, true);

        $frs = Fournisseur::query()
            ->where('actif', 1)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($id, $isAbonneForBoutique) {
                $q->where('is_visible', 1);

                if ($isAbonneForBoutique) {
                    $q->orWhere('frs.id', $id);
                }
            })
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
            ->when(! $isAbonneForBoutique, fn ($q) => $q->where('abonne_only', 0))
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
