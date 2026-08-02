<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Commune;
use App\Models\Fournisseur;
use App\Models\Wilaya;
use App\Services\ClientBoutiqueManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class ClientAuthController extends Controller
{
    public function __construct(private readonly ClientBoutiqueManager $clientBoutiqueManager)
    {
    }

    private function requestStorefrontDomainBoutique(Request $request): ?Fournisseur
    {
        $boutique = $request->attributes->get('custom_storefront_boutique');

        return $boutique instanceof Fournisseur ? $boutique : null;
    }

    private function storefrontBoutiqueFromSession(Request $request): ?Fournisseur
    {
        $frsId = (int) $request->session()->get('storefront_frs_id', 0);
        if ($frsId <= 0) {
            return null;
        }

        return Fournisseur::query()
            ->with('boutiqueCategory:id,name')
            ->where('id', $frsId)
            ->where('actif', 1)
            ->whereNull('deleted_at')
            ->first([
                'id',
                'nom_frs',
                'storefront_slug',
                'storefront_theme',
                'boutique_category_id',
                'logo_path',
                'telephone',
                'adresse',
                'id_wilaya',
                'id_commune',
                'latitude',
                'longitude',
            ]);
    }

    private function boutiqueStorefrontHomeUrl(Fournisseur $boutique, string $mode): string
    {
        if ($mode === 'domain') {
            return url('/');
        }

        return trim((string) ($boutique->storefront_url ?? '')) !== ''
            ? $boutique->storefront_url
            : url('/boutiques/'.$boutique->id);
    }

    private function storefrontState(Request $request): array
    {
        $domainBoutique = $this->requestStorefrontDomainBoutique($request);
        if ($domainBoutique) {
            return [
                'boutique' => $domainBoutique,
                'mode' => 'domain',
                'home_url' => $this->boutiqueStorefrontHomeUrl($domainBoutique, 'domain'),
            ];
        }

        $sessionBoutique = $this->storefrontBoutiqueFromSession($request);
        if ($sessionBoutique) {
            return [
                'boutique' => $sessionBoutique,
                'mode' => 'slug',
                'home_url' => $this->boutiqueStorefrontHomeUrl($sessionBoutique, 'slug'),
            ];
        }

        return [
            'boutique' => null,
            'mode' => null,
            'home_url' => '',
        ];
    }

    private function storefrontViewData(Request $request, string $title): array
    {
        $state = $this->storefrontState($request);
        $boutique = $state['boutique'];
        $mode = $state['mode'];

        return [
            'title' => $title,
            'storefront_mode' => $boutique !== null,
            'storefront_mode_type' => $mode,
            'custom_domain_mode' => $mode === 'domain',
            'storefront_boutique' => $boutique,
            'storefront_home_url' => (string) $state['home_url'],
            'store_theme_boutique' => $boutique,
            'theme_boutique' => $boutique,
            'header_brand_boutique' => $boutique,
            'header_brand_url' => $boutique ? $this->boutiqueStorefrontHomeUrl($boutique, (string) $mode) : '',
            'storefront_theme_key' => $boutique?->storefrontThemeKey() ?? Fournisseur::DEFAULT_STOREFRONT_THEME,
            'storefront_theme_config' => $boutique?->storefrontThemeConfig() ?? Fournisseur::storefrontThemeOptions()[Fournisseur::DEFAULT_STOREFRONT_THEME],
        ];
    }

    private function storefrontRedirectUrl(Request $request): string
    {
        $state = $this->storefrontState($request);

        return trim((string) ($state['home_url'] ?? '')) !== ''
            ? (string) $state['home_url']
            : '/';
    }

    public function showLogin(Request $request): View
    {
        return view('auth.client-login', $this->storefrontViewData($request, 'Connexion'));
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $client = Client::query()
            ->where('email', $credentials['email'])
            ->orderByRaw("CASE WHEN type_client = 'simple' AND id_frs IS NULL THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->get()
            ->first(fn (Client $candidate) => Hash::check($credentials['password'], $candidate->password));

        $client = $this->clientBoutiqueManager->resolveAuthenticatedClient($client);

        if (! $client) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Identifiants invalides.']);
        }

        if ((int) $client->actif !== 1) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Compte désactivé.']);
        }

        if ($client->type_client === 'simple' && empty($client->email_verified_at)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email non vérifié. Veuillez vérifier votre boîte mail.']);
        }

        $request->session()->regenerate();
        $request->session()->put([
            'role' => 'client',
            'client_id' => $client->id,
        ]);

        return redirect()->intended($this->storefrontRedirectUrl($request));
    }

    public function showRegister(Request $request): View
    {
        $wilayas = Wilaya::query()->orderBy('ID_WILAYA')->get(['ID_WILAYA', 'WILAYA']);
        $defaultWilaya = (int) ($wilayas->first()?->ID_WILAYA ?? 1);
        $communes = Commune::query()
            ->where('ID_WILAYA', $defaultWilaya)
            ->orderBy('COMMUNE')
            ->get(['ID_COMMUNE', 'COMMUNE', 'ID_WILAYA']);

        return view('auth.client-register', $this->storefrontViewData($request, 'Créer un compte') + [
            'wilayas' => $wilayas,
            'communes' => $communes,
            'default_wilaya' => $defaultWilaya,
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'telephone' => ['required', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'id_wilaya' => ['nullable', 'integer', 'exists:wilaya,ID_WILAYA'],
            'id_commune' => ['nullable', 'integer', 'exists:commune,ID_COMMUNE'],
        ]);

        $idWilaya = isset($data['id_wilaya'])
            ? (int) $data['id_wilaya']
            : (int) (DB::table('wilaya')->min('ID_WILAYA') ?? 1);

        $idCommune = isset($data['id_commune'])
            ? (int) $data['id_commune']
            : (int) (DB::table('commune')->where('ID_WILAYA', $idWilaya)->min('ID_COMMUNE') ?? 1);

        $existing = Client::findSimpleByEmail($data['email']);

        if ($existing && ! empty($existing->email_verified_at)) {
            return back()
                ->withInput($request->only('nom', 'email', 'telephone', 'adresse', 'id_wilaya', 'id_commune'))
                ->withErrors(['email' => 'Email déjà utilisé.']);
        }

        $payload = [
            'nom' => Client::normalizeFullName($data['nom']),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'telephone' => $data['telephone'],
            'adresse' => $data['adresse'] ?? '',
            'id_wilaya' => $idWilaya,
            'id_commune' => $idCommune,
            'type_client' => 'simple',
            'id_frs' => null,
            'actif' => 1,
            'email_verified_at' => null,
        ];

        $client = $existing
            ? tap($existing)->update($payload)
            : Client::create($payload);

        if (! $this->sendEmailVerificationCode($client)) {
            return back()
                ->withInput($request->only('nom', 'email', 'telephone', 'adresse', 'id_wilaya', 'id_commune'))
                ->withErrors(['email' => 'Impossible d’envoyer le code. Vérifiez la configuration Resend/Mail.']);
        }

        $request->session()->forget(['role', 'client_id']);
        $request->session()->put([
            'pending_client_id' => $client->id,
            'pending_client_email' => $client->email,
        ]);

        return redirect()->to('/register')->with('success', 'Un code de vérification a été envoyé par email.');
    }

    public function verifyEmail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $pendingId = (int) $request->session()->get('pending_client_id');
        if ($pendingId <= 0) {
            return redirect()->to('/register')->withErrors(['code' => 'Session expirée. Veuillez refaire l’inscription.']);
        }

        $client = Client::query()->find($pendingId);
        if (! $client) {
            return redirect()->to('/register')->withErrors(['code' => 'Compte introuvable.']);
        }

        if ($client->type_client !== 'simple') {
            return redirect()->to('/register')->withErrors(['code' => 'Compte invalide.']);
        }

        if (! empty($client->email_verified_at)) {
            $request->session()->forget(['pending_client_id', 'pending_client_email']);
            return redirect()->to('/login')->with('success', 'Email déjà vérifié. Vous pouvez vous connecter.');
        }

        if (empty($client->email_verification_code_hash) || empty($client->email_verification_expires_at)) {
            return redirect()->to('/register')->withErrors(['code' => 'Code invalide.']);
        }

        $expiresAt = Carbon::parse($client->email_verification_expires_at);
        if (Carbon::now()->greaterThan($expiresAt)) {
            return redirect()->to('/register')->withErrors(['code' => 'Code expiré. Cliquez sur "Renvoyer le code".']);
        }

        if (! Hash::check($data['code'], $client->email_verification_code_hash)) {
            return redirect()->to('/register')->withErrors(['code' => 'Code incorrect.']);
        }

        $client->forceFill([
            'email_verified_at' => Carbon::now(),
            'email_verification_code_hash' => null,
            'email_verification_expires_at' => null,
        ])->save();

        $request->session()->forget(['pending_client_id', 'pending_client_email']);
        $request->session()->regenerate();
        $request->session()->put([
            'role' => 'client',
            'client_id' => $client->id,
        ]);

        return redirect()->to($this->storefrontRedirectUrl($request))->with('success', 'Email vérifié. Bienvenue.');
    }

    public function resendEmailCode(Request $request): RedirectResponse
    {
        $pendingId = (int) $request->session()->get('pending_client_id');
        if ($pendingId <= 0) {
            return redirect()->to('/register')->withErrors(['code' => 'Session expirée. Veuillez refaire l’inscription.']);
        }

        $client = Client::query()->find($pendingId);
        if (! $client) {
            return redirect()->to('/register')->withErrors(['code' => 'Compte introuvable.']);
        }

        if ($client->type_client !== 'simple') {
            return redirect()->to('/register')->withErrors(['code' => 'Compte invalide.']);
        }

        if (! empty($client->email_verified_at)) {
            $request->session()->forget(['pending_client_id', 'pending_client_email']);
            return redirect()->to('/login')->with('success', 'Email déjà vérifié. Vous pouvez vous connecter.');
        }

        if (! $this->sendEmailVerificationCode($client)) {
            return redirect()->to('/register')->withErrors(['code' => 'Impossible d’envoyer le code. Vérifiez la configuration Resend/Mail.']);
        }

        return redirect()->to('/register')->with('success', 'Code renvoyé.');
    }

    public function restartRegister(Request $request): RedirectResponse
    {
        $pendingId = (int) $request->session()->get('pending_client_id');
        $input = [];

        if ($pendingId > 0) {
            $client = Client::query()->find($pendingId);

            if ($client && $client->type_client === 'simple' && empty($client->email_verified_at)) {
                $input = [
                    'nom' => $client->nom,
                    'email' => $client->email,
                    'telephone' => $client->telephone,
                    'adresse' => $client->adresse,
                    'id_wilaya' => $client->id_wilaya,
                    'id_commune' => $client->id_commune,
                ];

                $client->delete();
            }
        }

        $request->session()->forget(['pending_client_id', 'pending_client_email']);

        return redirect()
            ->to('/register')
            ->withInput($input)
            ->with('info', 'Vous pouvez corriger votre adresse email et recréer le compte.');
    }

    private function sendEmailVerificationCode(Client $client): bool
    {
        $code = (string) random_int(100000, 999999);

        $client->forceFill([
            'email_verification_code_hash' => Hash::make($code),
            'email_verification_expires_at' => Carbon::now()->addMinutes(10),
            'email_verified_at' => null,
        ])->save();

        $subject = 'Code de vérification email';
        $body = "Votre code de vérification est : {$code}\n\nCe code expire dans 10 minutes.\n\n" . config('app.name');

        try {
            $resendKey = (string) (config('services.resend.key') ?? '');
            $from = (string) (config('services.resend.from') ?? '');

            if ($resendKey !== '' && $from !== '') {
                $res = Http::timeout(10)
                    ->acceptJson()
                    ->withToken($resendKey)
                    ->post('https://api.resend.com/emails', [
                        'from' => $from,
                        'to' => [$client->email],
                        'subject' => $subject,
                        'text' => $body,
                    ]);

                if ($res->successful()) {
                    return true;
                }

                $client->forceFill([
                    'email_verification_code_hash' => null,
                    'email_verification_expires_at' => null,
                ])->save();

                return false;
            }

            Mail::raw($body, function ($message) use ($client, $subject) {
                $message->to($client->email)->subject($subject);
            });

            return true;
        } catch (\Throwable) {
            $client->forceFill([
                'email_verification_code_hash' => null,
                'email_verification_expires_at' => null,
            ])->save();

            return false;
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        $redirectUrl = $this->storefrontRedirectUrl($request);

        $request->session()->forget(['role', 'client_id']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to($redirectUrl);
    }
}
