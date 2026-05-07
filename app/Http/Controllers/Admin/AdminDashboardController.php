<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cmd1;
use App\Models\Fournisseur;
use App\Models\Client;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $nbFournisseurs = Fournisseur::query()->count();
        $nbClients = Client::query()->count();

        $today = Carbon::today();
        $nbCommandesDuJour = Cmd1::query()
            ->where('date_cmd', '>=', $today)
            ->count();

        $caTotal = (float) (Cmd1::query()->sum('montant_total') ?? 0);

        $from = Carbon::today()->subDays(6)->startOfDay();
        $counts = Cmd1::query()
            ->selectRaw('DATE(date_cmd) as d, COUNT(*) as c')
            ->where('date_cmd', '>=', $from)
            ->groupBy(DB::raw('DATE(date_cmd)'))
            ->orderBy('d')
            ->pluck('c', 'd')
            ->all();

        $labels = [];
        $series = [];
        for ($i = 0; $i < 7; $i++) {
            $d = $from->copy()->addDays($i)->toDateString();
            $labels[] = Carbon::parse($d)->format('d/m');
            $series[] = (int) ($counts[$d] ?? 0);
        }

        $dernieresCommandes = Cmd1::query()
            ->leftJoin('client', 'client.id', '=', 'cmd1.id_client')
            ->leftJoin('frs', 'frs.id', '=', 'cmd1.id_frs')
            ->select([
                'cmd1.id',
                'cmd1.date_cmd',
                'cmd1.statut',
                'cmd1.montant_total',
                'client.nom as client_nom',
                'client.prenom as client_prenom',
                'frs.nom_frs as frs_nom',
            ])
            ->orderByDesc('cmd1.date_cmd')
            ->limit(5)
            ->get();

        $fournisseursRecents = Fournisseur::query()
            ->select(['id', 'nom_frs', 'email', 'actif', 'created_at'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'title' => 'Dashboard',
            'nb_fournisseurs' => $nbFournisseurs,
            'nb_clients' => $nbClients,
            'nb_commandes' => $nbCommandesDuJour,
            'ca_total' => $caTotal,
            'chart_labels' => $labels,
            'chart_series' => $series,
            'dernieres_commandes' => $dernieresCommandes,
            'fournisseurs_recents' => $fournisseursRecents,
        ]);
    }
}

