<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PlatformBranding;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlatformSettingsController extends Controller
{
    public function __construct(private readonly PlatformBranding $platformBranding)
    {
    }

    public function edit(): View
    {
        return view('admin.parametres', [
            'title' => 'Paramètres',
            'platform_branding' => $this->platformBranding->viewData(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $currentLogoPath = $this->platformBranding->logoPath();

        if ((int) ($data['remove_logo'] ?? 0) === 1 && $currentLogoPath !== '') {
            Storage::disk('public')->delete($currentLogoPath);
            $this->platformBranding->setLogoPath(null);
            $currentLogoPath = '';
        }

        if ($request->hasFile('logo')) {
            if ($currentLogoPath !== '') {
                Storage::disk('public')->delete($currentLogoPath);
            }

            $ext = strtolower((string) $request->file('logo')->getClientOriginalExtension());
            if ($ext === '') {
                $ext = 'png';
            }

            $path = $request->file('logo')->storeAs(
                'branding',
                'platform_logo_'.now()->timestamp.'.'.$ext,
                'public'
            );

            $this->platformBranding->setLogoPath($path);
        }

        return back()->with('success', 'Logo de la plateforme mis à jour.');
    }
}
