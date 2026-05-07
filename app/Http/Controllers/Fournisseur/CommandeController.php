<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Cmd1;
use App\Models\Cmd2;
use App\Notifications\StatutCommandeChange;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CommandeController extends Controller
{
    public function index(Request $request): View
    {
        $frsId = (int) session('frs_id');
        $statut = $request->query('statut');
        $from = $request->query('from');
        $to = $request->query('to');
        $clientId = $request->query('client');

        $query = Cmd1::query()
            ->leftJoin('client', 'client.id', '=', 'cmd1.id_client')
            ->select([
                'cmd1.*',
                'client.nom as client_nom',
                'client.prenom as client_prenom',
            ])
            ->where('cmd1.id_frs', $frsId);

        if ($statut) {
            $query->where('cmd1.statut', $statut);
        }

        if ($clientId) {
            $query->where('cmd1.id_client', $clientId);
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

        $clients = Client::query()
            ->where('id_frs', $frsId)
            ->orderBy('prenom')
            ->get(['id', 'nom', 'prenom']);

        return view('fournisseur.commandes.index', [
            'title' => 'Mes Commandes',
            'commandes' => $commandes,
            'clients' => $clients,
            'selected_statut' => $statut,
            'from' => $from,
            'to' => $to,
            'selected_client' => $clientId,
            'statuts' => ['en_attente', 'confirmee', 'expediee', 'livree', 'annulee'],
        ]);
    }

    public function show(int $id): View
    {
        $frsId = (int) session('frs_id');

        $commande = Cmd1::query()
            ->where('id_frs', $frsId)
            ->findOrFail($id);

        $client = Client::query()->find($commande->id_client);

        $lignes = Cmd2::query()
            ->leftJoin('produit', 'produit.id', '=', 'cmd2.id_produit')
            ->select([
                'cmd2.*',
                'produit.designation as produit_designation',
                'produit.reference as produit_reference',
            ])
            ->where('cmd2.id_cmd', $commande->id)
            ->orderBy('cmd2.id')
            ->get();

        return view('fournisseur.commandes.show', [
            'title' => 'Détail Commande',
            'commande' => $commande,
            'client' => $client,
            'lignes' => $lignes,
            'statuts' => ['en_attente', 'confirmee', 'expediee', 'livree', 'annulee'],
        ]);
    }

    public function updateStatut(Request $request, int $id): RedirectResponse
    {
        $frsId = (int) session('frs_id');

        $data = $request->validate([
            'statut' => ['required', 'in:en_attente,confirmee,expediee,livree,annulee'],
        ]);

        $commande = Cmd1::query()
            ->where('id_frs', $frsId)
            ->findOrFail($id);

        $old = (string) $commande->statut;
        $new = (string) $data['statut'];

        if ($old !== $new) {
            $commande->statut = $new;
            $commande->save();

            $client = Client::query()->find($commande->id_client);
            if ($client) {
                $client->notify(new StatutCommandeChange($commande, $new));
            }

            return back()->with('success', 'Statut mis à jour.');
        }

        return back()->with('success', 'Aucun changement.');
    }
}

