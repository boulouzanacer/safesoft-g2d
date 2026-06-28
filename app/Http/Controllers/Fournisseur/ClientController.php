<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Cmd1;
use App\Models\Prevendeur;
use App\Models\VisitDaily;
use App\Models\VisitPlan;
use App\Services\VisitPlanningService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $frsId = (int) session('frs_id');
        $q = trim((string) $request->query('q', ''));

        $cmdCounts = DB::table('cmd1')
            ->selectRaw('id_client, COUNT(*) as nb')
            ->where('id_frs', $frsId)
            ->groupBy('id_client');

        $clients = Client::query()
            ->leftJoin('commune', 'commune.ID_COMMUNE', '=', 'client.id_commune')
            ->leftJoin('prevendeurs', 'prevendeurs.id', '=', 'client.prevendeur_id')
            ->leftJoinSub($cmdCounts, 'cc', function ($join) {
                $join->on('cc.id_client', '=', 'client.id');
            })
            ->select([
                'client.*',
                'commune.COMMUNE as commune_nom',
                'prevendeurs.nom as prevendeur_nom',
                DB::raw('COALESCE(cc.nb, 0) as nb_commandes'),
            ])
            ->where('client.id_frs', $frsId)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('client.nom', 'like', "%{$q}%")
                        ->orWhere('client.prenom', 'like', "%{$q}%")
                        ->orWhere('client.code_client', 'like', "%{$q}%")
                        ->orWhere('client.email', 'like', "%{$q}%")
                        ->orWhere('client.telephone', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('client.created_at')
            ->paginate(15)
            ->withQueryString();

        return view('fournisseur.clients.index', [
            'title' => 'Mes Clients',
            'q' => $q,
            'clients' => $clients,
        ]);
    }

    public function show(int $id): View
    {
        $frsId = (int) session('frs_id');

        $client = Client::query()
            ->where('id_frs', $frsId)
            ->with('prevendeur:id,nom')
            ->findOrFail($id);

        $commandes = Cmd1::query()
            ->where('id_frs', $frsId)
            ->where('id_client', $client->id)
            ->orderByDesc('date_cmd')
            ->paginate(10);

        $prevendeurs = Prevendeur::query()
            ->where('id_frs', $frsId)
            ->where('actif', 1)
            ->orderBy('nom')
            ->get(['id', 'nom']);

        $activePlan = VisitPlan::query()
            ->with(['days', 'prevendeur:id,nom'])
            ->where('id_frs', $frsId)
            ->where('client_id', $client->id)
            ->where('is_active', 1)
            ->latest('updated_at')
            ->first();

        return view('fournisseur.clients.show', [
            'title' => 'Détail Client',
            'client' => $client,
            'commandes' => $commandes,
            'prevendeurs' => $prevendeurs,
            'active_plan' => $activePlan,
        ]);
    }

    public function updatePrevendeur(Request $request, int $id, VisitPlanningService $service): RedirectResponse
    {
        $frsId = (int) session('frs_id');

        $data = $request->validate([
            'prevendeur_id' => ['nullable', 'integer'],
        ]);

        $client = Client::query()
            ->where('id_frs', $frsId)
            ->findOrFail($id);

        $prevendeurId = isset($data['prevendeur_id']) && (int) $data['prevendeur_id'] > 0
            ? (int) $data['prevendeur_id']
            : null;

        if ($prevendeurId !== null) {
            Prevendeur::query()
                ->where('id_frs', $frsId)
                ->where('actif', 1)
                ->findOrFail($prevendeurId);
        }

        $client->update([
            'prevendeur_id' => $prevendeurId,
        ]);

        VisitPlan::query()
            ->where('id_frs', $frsId)
            ->where('client_id', $client->id)
            ->update([
                'prevendeur_id' => $prevendeurId,
            ]);

        if ($prevendeurId === null) {
            VisitDaily::query()
                ->where('id_frs', $frsId)
                ->where('client_id', $client->id)
                ->where('source', 'generated')
                ->delete();

            return back()->with('success', 'Prevendeur retire. Les visites generees de ce client ont ete nettoyees.');
        }

        $plans = VisitPlan::query()
            ->with(['days', 'client'])
            ->where('id_frs', $frsId)
            ->where('client_id', $client->id)
            ->get();

        foreach ($plans as $plan) {
            $service->regenerateForPlan($plan);
        }

        return back()->with('success', 'Prevendeur affecte au client et planning regenere selon ses details.');
    }
}
