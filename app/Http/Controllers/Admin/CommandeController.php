<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cmd1;
use App\Models\Fournisseur;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CommandeController extends Controller
{
    public function index(Request $request): View
    {
        $fournisseurId = $request->query('fournisseur');
        $statut = $request->query('statut');
        $from = $request->query('from');
        $to = $request->query('to');

        $query = Cmd1::query()
            ->leftJoin('client', 'client.id', '=', 'cmd1.id_client')
            ->leftJoin('frs', 'frs.id', '=', 'cmd1.id_frs')
            ->select([
                'cmd1.*',
                'client.nom as client_nom',
                'client.prenom as client_prenom',
                'frs.nom_frs as frs_nom',
            ]);

        if ($fournisseurId) {
            $query->where('cmd1.id_frs', $fournisseurId);
        }

        if ($statut) {
            $query->where('cmd1.statut', $statut);
        }

        if ($from) {
            $query->where('cmd1.date_cmd', '>=', Carbon::parse($from)->startOfDay());
        }

        if ($to) {
            $query->where('cmd1.date_cmd', '<=', Carbon::parse($to)->endOfDay());
        }

        $commandes = $query
            ->orderByDesc('cmd1.date_cmd')
            ->paginate(15)
            ->withQueryString();

        $fournisseurs = Fournisseur::query()->orderBy('nom_frs')->get(['id', 'nom_frs']);

        return view('admin.commandes.index', [
            'title' => 'Commandes',
            'commandes' => $commandes,
            'fournisseurs' => $fournisseurs,
            'selected_fournisseur' => $fournisseurId,
            'selected_statut' => $statut,
            'from' => $from,
            'to' => $to,
            'statuts' => ['en_attente', 'confirmee', 'expediee', 'livree', 'annulee'],
        ]);
    }
}

