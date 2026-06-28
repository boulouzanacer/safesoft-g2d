<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Prevendeur;
use App\Models\VisitDaily;
use App\Models\VisitPlan;
use App\Services\VisitPlanningService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VisitPlanningController extends Controller
{
    public function index(Request $request): View
    {
        $frsId = (int) session('frs_id');
        $today = Carbon::today();

        $editingPlan = null;
        if ($request->filled('edit')) {
            $editingPlan = VisitPlan::query()
                ->with(['days', 'client.prevendeur', 'prevendeur'])
                ->where('id_frs', $frsId)
                ->findOrFail((int) $request->query('edit'));
        }

        $clients = Client::query()
            ->where('id_frs', $frsId)
            ->with('prevendeur:id,nom')
            ->orderBy('prenom')
            ->orderBy('nom')
            ->get(['id', 'code_client', 'nom', 'prenom', 'prevendeur_id']);

        $prevendeurs = Prevendeur::query()
            ->where('id_frs', $frsId)
            ->where('actif', 1)
            ->orderBy('nom')
            ->get(['id', 'nom']);

        $plans = VisitPlan::query()
            ->with(['client:id,code_client,nom,prenom,prevendeur_id', 'client.prevendeur:id,nom', 'days', 'prevendeur:id,nom'])
            ->where('id_frs', $frsId)
            ->orderByDesc('is_active')
            ->orderBy('client_id')
            ->paginate(15)
            ->withQueryString();

        $todayVisits = VisitDaily::query()
            ->with(['client:id,code_client,nom,prenom', 'prevendeur:id,nom'])
            ->where('id_frs', $frsId)
            ->whereDate('visit_date', $today)
            ->orderBy('prevendeur_id')
            ->orderBy('client_id')
            ->get();

        $upcomingVisits = VisitDaily::query()
            ->selectRaw('visit_date, COUNT(*) as total')
            ->where('id_frs', $frsId)
            ->whereBetween('visit_date', [$today->toDateString(), $today->copy()->addDays(14)->toDateString()])
            ->groupBy('visit_date')
            ->orderBy('visit_date')
            ->get();

        $clientsWithoutPlan = Client::query()
            ->where('id_frs', $frsId)
            ->whereNotIn('id', VisitPlan::query()
                ->select('client_id')
                ->where('id_frs', $frsId)
                ->where('is_active', 1))
            ->count();

        return view('fournisseur.visites.index', [
            'title' => 'Planning de visite',
            'clients' => $clients,
            'prevendeurs' => $prevendeurs,
            'plans' => $plans,
            'editing_plan' => $editingPlan,
            'today_visits' => $todayVisits,
            'upcoming_visits' => $upcomingVisits,
            'active_plans_count' => VisitPlan::query()->where('id_frs', $frsId)->where('is_active', 1)->count(),
            'today_visits_count' => $todayVisits->count(),
            'clients_without_plan' => $clientsWithoutPlan,
            'clients_without_prevendeur' => Client::query()->where('id_frs', $frsId)->whereNull('prevendeur_id')->count(),
        ]);
    }

    public function store(Request $request, VisitPlanningService $service): RedirectResponse
    {
        $frsId = (int) session('frs_id');
        $data = $this->validatedData($request, $frsId);

        $plan = DB::transaction(function () use ($data, $frsId) {
            return $this->persistPlan(new VisitPlan(), $data, $frsId);
        });

        $service->regenerateForPlan($plan);

        return redirect('/fournisseur/visites/planning')
            ->with('success', 'Planning de visite cree et genere sur les 60 prochains jours.');
    }

    public function update(Request $request, int $id, VisitPlanningService $service): RedirectResponse
    {
        $frsId = (int) session('frs_id');
        $plan = VisitPlan::query()
            ->with('days')
            ->where('id_frs', $frsId)
            ->findOrFail($id);

        $data = $this->validatedData($request, $frsId);

        $plan = DB::transaction(function () use ($plan, $data, $frsId) {
            return $this->persistPlan($plan, $data, $frsId);
        });

        $service->regenerateForPlan($plan);

        return redirect('/fournisseur/visites/planning')
            ->with('success', 'Planning de visite mis a jour et recalcule.');
    }

    public function toggle(int $id, VisitPlanningService $service): RedirectResponse
    {
        $frsId = (int) session('frs_id');

        $plan = VisitPlan::query()
            ->with('days')
            ->where('id_frs', $frsId)
            ->findOrFail($id);

        $plan->update([
            'is_active' => ! $plan->is_active,
        ]);

        $service->regenerateForPlan($plan->fresh('days'));

        return back()->with('success', 'Statut du planning mis a jour.');
    }

    public function regenerate(VisitPlanningService $service): RedirectResponse
    {
        $frsId = (int) session('frs_id');

        $service->regenerateForFournisseur($frsId);

        return back()->with('success', 'Projection des visites regeneree pour les 60 prochains jours.');
    }

    protected function persistPlan(VisitPlan $plan, array $data, int $frsId): VisitPlan
    {
        $otherPlanIds = collect();
        $client = Client::query()
            ->where('id_frs', $frsId)
            ->findOrFail((int) $data['client_id']);

        $plan->fill([
            'client_id' => (int) $data['client_id'],
            'id_frs' => $frsId,
            'prevendeur_id' => (int) $client->prevendeur_id,
            'frequency_type' => $data['frequency_type'],
            'interval_value' => (int) $data['interval_value'],
            'month_occurrence' => $data['frequency_type'] === 'monthly' ? $data['month_occurrence'] : null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'label' => $data['label'] ?? null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ]);

        $plan->save();

        if ($plan->is_active) {
            $otherPlanIds = VisitPlan::query()
                ->where('id_frs', $frsId)
                ->where('client_id', $plan->client_id)
                ->where('id', '!=', $plan->id)
                ->pluck('id');

            VisitPlan::query()
                ->where('id_frs', $frsId)
                ->where('client_id', $plan->client_id)
                ->where('id', '!=', $plan->id)
                ->update(['is_active' => 0]);

            if ($otherPlanIds->isNotEmpty()) {
                VisitDaily::query()
                    ->whereIn('visit_plan_id', $otherPlanIds->all())
                    ->where('source', 'generated')
                    ->delete();
            }
        }

        $plan->days()->delete();

        foreach ($data['weekdays'] as $dayOfWeek) {
            $plan->days()->create([
                'day_of_week' => (int) $dayOfWeek,
            ]);
        }

        return $plan->fresh('days');
    }

    protected function validatedData(Request $request, int $frsId): array
    {
        $data = $request->validate([
            'client_id' => [
                'required',
                'integer',
                Rule::exists('client', 'id')->where(fn ($query) => $query->where('id_frs', $frsId)),
            ],
            'label' => ['nullable', 'string', 'max:255'],
            'frequency_type' => ['required', Rule::in(['daily', 'weekly', 'monthly'])],
            'interval_value' => ['required', 'integer', 'min:1', 'max:90'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'month_occurrence' => ['nullable', Rule::in(['first', 'second', 'third', 'fourth', 'last'])],
            'weekdays' => ['nullable', 'array'],
            'weekdays.*' => ['integer', 'between:0,6'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['weekdays'] = collect($data['weekdays'] ?? [])
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();

        if ($data['frequency_type'] === 'daily') {
            $data['weekdays'] = [];

            $client = Client::query()
                ->where('id_frs', $frsId)
                ->find((int) $data['client_id']);

            if (! $client || ! $client->prevendeur_id) {
                throw ValidationException::withMessages([
                    'client_id' => 'Veuillez affecter ce client a un prevendeur avant de definir son planning.',
                ]);
            }

            return $data;
        }

        $client = Client::query()
            ->where('id_frs', $frsId)
            ->find((int) $data['client_id']);

        if (! $client || ! $client->prevendeur_id) {
            throw ValidationException::withMessages([
                'client_id' => 'Veuillez affecter ce client a un prevendeur avant de definir son planning.',
            ]);
        }

        if (count($data['weekdays']) === 0) {
            throw ValidationException::withMessages([
                'weekdays' => 'Veuillez selectionner au moins un jour.',
            ]);
        }

        if ($data['frequency_type'] === 'monthly') {
            if (($data['month_occurrence'] ?? null) === null) {
                throw ValidationException::withMessages([
                    'month_occurrence' => 'Veuillez choisir la position dans le mois.',
                ]);
            }

            if (count($data['weekdays']) !== 1) {
                throw ValidationException::withMessages([
                    'weekdays' => 'Le mode mensuel accepte un seul jour de semaine.',
                ]);
            }
        } else {
            $data['month_occurrence'] = null;
        }

        return $data;
    }
}
