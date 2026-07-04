<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApiKeyController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $type = trim((string) $request->query('type', ''));
        $createOpen = (int) $request->query('create', 0) === 1;

        $apiKeys = ApiKey::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('api_key', 'like', "%{$q}%");
            })
            ->when(array_key_exists($type, ApiKey::typeOptions()), function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.api-keys.index', [
            'title' => 'Api Keys',
            'q' => $q,
            'type_filter' => $type,
            'create_open' => $createOpen,
            'api_keys' => $apiKeys,
            'type_options' => ApiKey::typeOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(ApiKey::typeOptions()))],
            'api_key' => ['required', 'string', 'min:16', 'max:255', Rule::unique('api_keys', 'api_key')],
        ]);

        $apiKey = ApiKey::query()->create([
            'type' => $validated['type'],
            'api_key' => trim($validated['api_key']),
            'actif' => true,
        ]);

        return redirect()
            ->to('/admin/api-keys')
            ->with('success', 'Api key créée.')
            ->with('created_api_key', $apiKey->api_key);
    }

    public function toggle(int $id): RedirectResponse
    {
        $apiKey = ApiKey::query()->findOrFail($id);

        $apiKey->forceFill([
            'actif' => ! $apiKey->actif,
        ])->save();

        return back()->with('success', 'Statut de la clé mis à jour.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $apiKey = ApiKey::query()->findOrFail($id);
        $apiKey->delete();

        return back()->with('success', 'Api key supprimée.');
    }
}
