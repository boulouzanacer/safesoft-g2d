<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFournisseurRequest;
use App\Http\Requests\UpdateFournisseurRequest;
use App\Models\BoutiqueCategory;
use App\Models\Commune;
use App\Models\Fournisseur;
use App\Models\Wilaya;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FournisseurController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $editId = (int) $request->query('edit', 0);
        $createOpen = (int) $request->query('create', 0) === 1;

        Fournisseur::query()
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<', Carbon::today()->toDateString())
            ->where('actif', 1)
            ->update(['actif' => 0]);

        $editingFournisseur = null;
        $editCommunes = collect();

        if ($editId > 0) {
            $editingFournisseur = Fournisseur::query()->findOrFail($editId);
            $editCommunes = Commune::query()
                ->where('ID_WILAYA', $editingFournisseur->id_wilaya)
                ->orderBy('COMMUNE')
                ->get();
        }

        $fournisseurs = Fournisseur::query()
            ->leftJoin('boutique_categories', 'boutique_categories.id', '=', 'frs.boutique_category_id')
            ->leftJoin('wilaya', 'wilaya.ID_WILAYA', '=', 'frs.id_wilaya')
            ->select([
                'frs.*',
                'boutique_categories.name as boutique_category_name',
                'wilaya.WILAYA as wilaya_nom',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('frs.nom_frs', 'like', "%{$q}%")
                        ->orWhere('frs.email', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('frs.created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.fournisseurs.index', [
            'title' => 'Fournisseurs',
            'q' => $q,
            'fournisseurs' => $fournisseurs,
            'boutique_categories' => BoutiqueCategory::query()->orderBy('name')->get(),
            'wilayas' => Wilaya::query()->orderBy('ID_WILAYA')->get(),
            'create_open' => $createOpen,
            'editing_fournisseur' => $editingFournisseur,
            'edit_communes' => $editCommunes,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->to('/admin/fournisseurs?create=1');
    }

    public function store(StoreFournisseurRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $token = (string) Str::uuid();

        $frs = Fournisseur::create([
            'nom_frs' => $data['nom_frs'],
            'boutique_category_id' => (int) $data['boutique_category_id'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'telephone' => $data['telephone'] ?? null,
            'adresse' => $data['adresse'],
            'id_wilaya' => (int) $data['id_wilaya'],
            'id_commune' => (int) $data['id_commune'],
            'latitude' => isset($data['latitude']) ? (float) $data['latitude'] : null,
            'longitude' => isset($data['longitude']) ? (float) $data['longitude'] : null,
            'token' => $token,
            'actif' => $this->resolveActifFromExpiration($data),
            'expires_at' => $data['expires_at'],
        ]);

        if ($request->hasFile('logo')) {
            $ext = strtolower((string) $request->file('logo')->getClientOriginalExtension());
            if ($ext === '') {
                $ext = 'jpg';
            }
            $path = $request->file('logo')->storeAs(
                "frs/{$frs->id}",
                'logo_'.now()->timestamp.'.'.$ext,
                'public'
            );
            $frs->update(['logo_path' => $path]);
        }

        return redirect()
            ->to("/admin/fournisseurs?edit={$frs->id}")
            ->with('created_token', $token);
    }

    public function edit(int $id): RedirectResponse
    {
        Fournisseur::query()->findOrFail($id);

        return redirect()->to("/admin/fournisseurs?edit={$id}");
    }

    public function update(UpdateFournisseurRequest $request, int $id): RedirectResponse
    {
        $frs = Fournisseur::query()->findOrFail($id);
        $data = $request->validated();

        $payload = [
            'nom_frs' => $data['nom_frs'],
            'boutique_category_id' => (int) $data['boutique_category_id'],
            'email' => $data['email'],
            'telephone' => $data['telephone'] ?? null,
            'adresse' => $data['adresse'],
            'id_wilaya' => (int) $data['id_wilaya'],
            'id_commune' => (int) $data['id_commune'],
            'latitude' => isset($data['latitude']) ? (float) $data['latitude'] : null,
            'longitude' => isset($data['longitude']) ? (float) $data['longitude'] : null,
            'actif' => $this->resolveActifFromExpiration($data),
            'expires_at' => $data['expires_at'],
        ];

        if (! empty($data['password'] ?? null)) {
            $payload['password'] = Hash::make($data['password']);
        }

        if ((int) ($data['remove_logo'] ?? 0) === 1) {
            if (! empty($frs->logo_path)) {
                Storage::disk('public')->delete($frs->logo_path);
            }
            $payload['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if (! empty($frs->logo_path)) {
                Storage::disk('public')->delete($frs->logo_path);
            }
            $ext = strtolower((string) $request->file('logo')->getClientOriginalExtension());
            if ($ext === '') {
                $ext = 'jpg';
            }
            $path = $request->file('logo')->storeAs(
                "frs/{$frs->id}",
                'logo_'.now()->timestamp.'.'.$ext,
                'public'
            );
            $payload['logo_path'] = $path;
        }

        $frs->update($payload);

        return back()->with('success', 'Fournisseur mis à jour.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $frs = Fournisseur::query()->findOrFail($id);
        $frs->delete();

        return back()->with('success', 'Fournisseur supprimé.');
    }

    public function toggleActif(int $id): RedirectResponse
    {
        $frs = Fournisseur::query()->findOrFail($id);
        $nextStatus = (int) $frs->actif === 1 ? 0 : 1;

        if ($nextStatus === 1 && $frs->isExpired()) {
            return back()->with('success', 'Impossible d activer ce fournisseur tant que sa date d expiration n a pas ete prolongee.');
        }

        $frs->actif = $nextStatus;
        $frs->save();

        return back()->with('success', 'Statut mis à jour.');
    }

    public function regenererToken(int $id): RedirectResponse
    {
        $frs = Fournisseur::query()->findOrFail($id);

        $frs->token = (string) Str::uuid();
        $frs->save();

        return back()->with('regenerated_token', $frs->token);
    }

    public function communes(int $idWilaya): JsonResponse
    {
        $rows = DB::table('commune')
            ->where('ID_WILAYA', $idWilaya)
            ->orderBy('COMMUNE')
            ->get(['ID_COMMUNE', 'COMMUNE']);

        return response()->json($rows);
    }

    protected function resolveActifFromExpiration(array $data): int
    {
        $isActive = (int) ($data['actif'] ?? 0) === 1 ? 1 : 0;
        $expiresAt = Carbon::parse($data['expires_at'])->startOfDay();

        if ($expiresAt->lt(Carbon::today()->startOfDay())) {
            return 0;
        }

        return $isActive;
    }
}
