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
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            ->latest('is_active')
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
            VisitPlan::query()
                ->where('id_frs', $frsId)
                ->where('client_id', $client->id)
                ->update(['is_active' => 0]);

            $service->clearClientProgram($frsId, $client->id);

            return back()->with('success', 'Prevendeur retire. Les visites generees de ce client ont ete nettoyees.');
        }

        $plans = VisitPlan::query()
            ->with(['days', 'client'])
            ->where('id_frs', $frsId)
            ->where('client_id', $client->id)
            ->get();

        foreach ($plans as $plan) {
            $service->syncClientProgram($plan);
        }

        return back()->with('success', 'Prevendeur affecte au client et planning regenere selon ses details.');
    }

    public function updatePlanning(Request $request, int $id, VisitPlanningService $service): RedirectResponse
    {
        $frsId = (int) session('frs_id');

        $client = Client::query()
            ->where('id_frs', $frsId)
            ->with('visitPlans.days')
            ->findOrFail($id);

        $data = $this->validatedPlanningData($request, $client);

        $plan = VisitPlan::query()
            ->where('id_frs', $frsId)
            ->where('client_id', $client->id)
            ->latest('updated_at')
            ->first();

        if (! $data['programme_tournee_actif']) {
            VisitPlan::query()
                ->where('id_frs', $frsId)
                ->where('client_id', $client->id)
                ->update(['is_active' => 0]);

            $service->clearClientProgram($frsId, $client->id);

            return back()->with('success', 'Programme tournee desactive pour ce client.');
        }

        if (! $plan) {
            $plan = new VisitPlan();
        }

        $plan->fill([
            'client_id' => $client->id,
            'id_frs' => $frsId,
            'prevendeur_id' => (int) $client->prevendeur_id,
            'frequency_type' => $data['frequency_type'],
            'interval_value' => (int) $data['interval_value'],
            'month_occurrence' => $data['frequency_type'] === 'monthly' ? $data['month_occurrence'] : null,
            'start_date' => $plan->start_date?->format('Y-m-d') ?? now()->toDateString(),
            'end_date' => null,
            'label' => 'Programme client '.$client->id,
            'is_active' => 1,
        ]);
        $plan->save();

        VisitPlan::query()
            ->where('id_frs', $frsId)
            ->where('client_id', $client->id)
            ->where('id', '!=', $plan->id)
            ->update(['is_active' => 0]);

        $plan->days()->delete();
        foreach ($data['weekdays'] as $dayOfWeek) {
            $plan->days()->create([
                'day_of_week' => (int) $dayOfWeek,
            ]);
        }

        $service->syncClientProgram($plan->fresh(['days', 'client']));

        return back()->with('success', 'Programme tournee enregistre et regenere pour ce client.');
    }

    protected function validatedPlanningData(Request $request, Client $client): array
    {
        $data = $request->validate([
            'programme_tournee_actif' => ['nullable', 'boolean'],
            'frequency_type' => ['required', Rule::in(['daily', 'weekly', 'monthly'])],
            'interval_value' => ['required', 'integer', 'min:1', 'max:90'],
            'month_occurrence' => ['nullable', Rule::in(['first', 'second', 'third', 'fourth', 'last'])],
            'weekdays' => ['nullable', 'array'],
            'weekdays.*' => ['integer', 'between:0,6'],
        ]);

        $data['programme_tournee_actif'] = (int) ($data['programme_tournee_actif'] ?? 0) === 1;
        $data['weekdays'] = collect($data['weekdays'] ?? [])
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        if (! $data['programme_tournee_actif']) {
            return $data;
        }

        if (! $client->prevendeur_id) {
            throw ValidationException::withMessages([
                'programme_tournee_actif' => 'Veuillez d abord affecter ce client a un prevendeur.',
            ]);
        }

        if ($data['frequency_type'] === 'daily') {
            $data['weekdays'] = [];
            $data['month_occurrence'] = null;

            return $data;
        }

        if (count($data['weekdays']) === 0) {
            throw ValidationException::withMessages([
                'weekdays' => 'Veuillez selectionner au moins un jour de visite.',
            ]);
        }

        if ($data['frequency_type'] === 'monthly') {
            if (($data['month_occurrence'] ?? null) === null) {
                throw ValidationException::withMessages([
                    'month_occurrence' => 'Veuillez choisir l occurrence mensuelle.',
                ]);
            }

            if (count($data['weekdays']) !== 1) {
                throw ValidationException::withMessages([
                    'weekdays' => 'Le mode mensuel accepte un seul jour de visite.',
                ]);
            }
        } else {
            $data['month_occurrence'] = null;
        }

        return $data;
    }
}
