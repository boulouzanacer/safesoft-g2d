<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Models\Cmd1;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\VisitDaily;
use App\Models\VisitPlan;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(): View
    {
        $frsId = (int) session('frs_id');
        $frs = Fournisseur::query()
            ->with(['customDomains' => fn ($query) => $query->where('is_primary', 1)->orderByDesc('is_primary')])
            ->findOrFail($frsId);

        $primaryCustomDomain = $frs->customDomains->first();
        $storefrontTheme = $frs->storefrontThemeConfig();

        $cmdEnAttente = Cmd1::query()
            ->where('id_frs', $frsId)
            ->where('statut', 'en_attente')
            ->count();

        $cmdDuJour = Cmd1::query()
            ->where('id_frs', $frsId)
            ->where('date_cmd', '>=', Carbon::today())
            ->count();

        $clientsAbonnes = Client::query()
            ->where('id_frs', $frsId)
            ->where('type_client', 'abonne')
            ->count();

        $produitsActifs = Produit::query()
            ->where('id_frs', $frsId)
            ->where('actif', 1)
            ->count();

        $dernieresCommandes = Cmd1::query()
            ->leftJoin('client', 'client.id', '=', 'cmd1.id_client')
            ->select([
                'cmd1.id',
                'cmd1.date_cmd',
                'cmd1.statut',
                'cmd1.montant_total',
                'client.nom as client_nom',
                'client.prenom as client_prenom',
            ])
            ->where('cmd1.id_frs', $frsId)
            ->orderByDesc('cmd1.date_cmd')
            ->limit(5)
            ->get();

        $ruptureStock = Produit::query()
            ->where('id_frs', $frsId)
            ->where('stock', '<', 5)
            ->orderBy('stock')
            ->limit(5)
            ->get(['id', 'reference', 'designation', 'stock', 'pv_1', 'actif', 'image_principale']);

        $visitesDuJour = VisitDaily::query()
            ->where('id_frs', $frsId)
            ->whereDate('visit_date', Carbon::today())
            ->count();

        $plansActifs = VisitPlan::query()
            ->where('id_frs', $frsId)
            ->where('is_active', 1)
            ->count();

        $clientsSansPlanning = Client::query()
            ->where('id_frs', $frsId)
            ->whereNotIn('id', VisitPlan::query()
                ->select('client_id')
                ->where('id_frs', $frsId)
                ->where('is_active', 1))
            ->count();

        $clientsAVisiter = VisitDaily::query()
            ->leftJoin('client', 'client.id', '=', 'visit_daily.client_id')
            ->select(['visit_daily.visit_date', 'client.id', 'client.nom', 'client.prenom', 'client.code_client'])
            ->where('visit_daily.id_frs', $frsId)
            ->whereDate('visit_daily.visit_date', Carbon::today())
            ->orderBy('client.nom')
            ->limit(8)
            ->get();

        $prochainesVisites = VisitDaily::query()
            ->selectRaw('visit_date, COUNT(*) as total')
            ->where('id_frs', $frsId)
            ->whereBetween('visit_date', [Carbon::today()->toDateString(), Carbon::today()->copy()->addDays(6)->toDateString()])
            ->groupBy('visit_date')
            ->orderBy('visit_date')
            ->get();

        return view('fournisseur.dashboard', [
            'title' => 'Mon Dashboard',
            'storefront_url' => $frs->storefront_url,
            'primary_custom_domain' => $primaryCustomDomain?->domain,
            'storefront_theme_key' => $frs->storefrontThemeKey(),
            'storefront_theme_name' => $storefrontTheme['name'] ?? 'Theme',
            'storefront_theme_tagline' => $storefrontTheme['tagline'] ?? '',
            'storefront_theme_preview' => $storefrontTheme['preview'] ?? [],
            'cmd_en_attente' => $cmdEnAttente,
            'cmd_du_jour' => $cmdDuJour,
            'clients_abonnes' => $clientsAbonnes,
            'produits_actifs' => $produitsActifs,
            'dernieres_commandes' => $dernieresCommandes,
            'rupture_stock' => $ruptureStock,
            'visites_du_jour' => $visitesDuJour,
            'plans_actifs' => $plansActifs,
            'clients_sans_planning' => $clientsSansPlanning,
            'clients_a_visiter' => $clientsAVisiter,
            'prochaines_visites' => $prochainesVisites,
        ]);
    }
}
