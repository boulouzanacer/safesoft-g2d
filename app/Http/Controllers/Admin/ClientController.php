<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Fournisseur;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $fournisseurId = $request->query('fournisseur');
        $q = trim((string) $request->query('q', ''));

        $clients = Client::query()
            ->leftJoin('frs', 'frs.id', '=', 'client.id_frs')
            ->select([
                'client.*',
                'frs.nom_frs as frs_nom',
            ])
            ->when($fournisseurId, fn ($query) => $query->where('client.id_frs', $fournisseurId))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('client.nom', 'like', "%{$q}%")
                        ->orWhere('client.prenom', 'like', "%{$q}%")
                        ->orWhere('client.email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('client.created_at')
            ->paginate(15)
            ->withQueryString();

        $fournisseurs = Fournisseur::query()->orderBy('nom_frs')->get(['id', 'nom_frs']);

        return view('admin.clients.index', [
            'title' => 'Clients',
            'clients' => $clients,
            'fournisseurs' => $fournisseurs,
            'selected_fournisseur' => $fournisseurId,
            'q' => $q,
        ]);
    }
}

