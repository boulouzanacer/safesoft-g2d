<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fournisseur;
use App\Models\Produit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    public function index(Request $request): View
    {
        $fournisseurId = $request->query('fournisseur');
        $q = trim((string) $request->query('q', ''));

        $produits = Produit::query()
            ->leftJoin('frs', 'frs.id', '=', 'produit.id_frs')
            ->select([
                'produit.*',
                'frs.nom_frs as frs_nom',
            ])
            ->when($fournisseurId, fn ($query) => $query->where('produit.id_frs', $fournisseurId))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('produit.reference', 'like', "%{$q}%")
                        ->orWhere('produit.designation', 'like', "%{$q}%")
                        ->orWhere('produit.categorie', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('produit.created_at')
            ->paginate(15)
            ->withQueryString();

        $fournisseurs = Fournisseur::query()->orderBy('nom_frs')->get(['id', 'nom_frs']);

        return view('admin.produits.index', [
            'title' => 'Produits',
            'produits' => $produits,
            'fournisseurs' => $fournisseurs,
            'selected_fournisseur' => $fournisseurId,
            'q' => $q,
        ]);
    }
}

