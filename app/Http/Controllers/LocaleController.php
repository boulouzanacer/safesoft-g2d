<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $supported = array_keys((array) config('locales.supported', []));

        $data = $request->validate([
            'locale' => ['required', 'string', 'in:'.implode(',', $supported)],
        ]);

        $request->session()->put('locale', $data['locale']);

        return redirect()->back();
    }
}
