<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Models\Prevendeur;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PrevendeurController extends Controller
{
    public function index(Request $request): View
    {
        $frsId = (int) session('frs_id');
        $q = trim((string) $request->query('q', ''));

        $editingPrevendeur = null;
        if ($request->filled('edit')) {
            $editingPrevendeur = Prevendeur::query()
                ->where('id_frs', $frsId)
                ->findOrFail((int) $request->query('edit'));
        }

        $prevendeurs = Prevendeur::query()
            ->withCount('clients')
            ->where('id_frs', $frsId)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nom', 'like', "%{$q}%")
                        ->orWhere('telephone', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('actif')
            ->orderBy('nom')
            ->paginate(12)
            ->withQueryString();

        return view('fournisseur.prevendeurs.index', [
            'title' => 'Mes Prevendeurs',
            'q' => $q,
            'prevendeurs' => $prevendeurs,
            'editing_prevendeur' => $editingPrevendeur,
            'prevendeurs_count' => Prevendeur::query()->where('id_frs', $frsId)->count(),
            'prevendeurs_actifs_count' => Prevendeur::query()->where('id_frs', $frsId)->where('actif', 1)->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $frsId = (int) session('frs_id');
        $data = $this->validatedData($request, $frsId);

        Prevendeur::query()->create([
            'id_frs' => $frsId,
            'nom' => $data['nom'],
            'telephone' => $data['telephone'] ?? null,
            'email' => $data['email'] ?? null,
            'notes' => $data['notes'] ?? null,
            'actif' => isset($data['actif']) ? 1 : 0,
        ]);

        return redirect('/fournisseur/prevendeurs')->with('success', 'Prevendeur cree avec succes.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $frsId = (int) session('frs_id');
        $prevendeur = Prevendeur::query()
            ->where('id_frs', $frsId)
            ->findOrFail($id);

        $data = $this->validatedData($request, $frsId, $prevendeur->id);

        $prevendeur->update([
            'nom' => $data['nom'],
            'telephone' => $data['telephone'] ?? null,
            'email' => $data['email'] ?? null,
            'notes' => $data['notes'] ?? null,
            'actif' => isset($data['actif']) ? 1 : 0,
        ]);

        return redirect('/fournisseur/prevendeurs')->with('success', 'Prevendeur mis a jour.');
    }

    public function toggle(int $id): RedirectResponse
    {
        $frsId = (int) session('frs_id');
        $prevendeur = Prevendeur::query()
            ->where('id_frs', $frsId)
            ->findOrFail($id);

        $prevendeur->update([
            'actif' => ! $prevendeur->actif,
        ]);

        return back()->with('success', 'Statut du prevendeur mis a jour.');
    }

    protected function validatedData(Request $request, int $frsId, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('prevendeurs', 'email')
                    ->where(fn ($query) => $query->where('id_frs', $frsId))
                    ->ignore($ignoreId),
            ],
            'notes' => ['nullable', 'string'],
            'actif' => ['nullable', 'boolean'],
        ]);
    }
}
