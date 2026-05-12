<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuthChangePasswordRequest;
use App\Http\Requests\Api\V1\AuthLoginRequest;
use App\Http\Requests\Api\V1\AuthRegisterRequest;
use App\Http\Requests\Api\V1\AuthUpdateProfileRequest;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function register(AuthRegisterRequest $request)
    {
        $data = $request->validated();

        $idWilaya = isset($data['id_wilaya'])
            ? (int) $data['id_wilaya']
            : (int) (DB::table('wilaya')->min('ID_WILAYA') ?? 1);

        $idCommune = isset($data['id_commune'])
            ? (int) $data['id_commune']
            : (int) (DB::table('commune')->where('ID_WILAYA', $idWilaya)->min('ID_COMMUNE') ?? 1);

        $client = Client::create([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'telephone' => $data['telephone'] ?? null,
            'adresse' => $data['adresse'] ?? '',
            'id_wilaya' => $idWilaya,
            'id_commune' => $idCommune,
            'type_client' => 'simple',
            'id_frs' => null,
            'actif' => 1,
            'email_verified_at' => now(),
        ]);

        $token = $client->createToken('client')->plainTextToken;

        return $this->success([
            'token' => $token,
            'client' => [
                'id' => $client->id,
                'nom' => $client->nom,
                'prenom' => $client->prenom,
                'email' => $client->email,
                'telephone' => $client->telephone,
                'type_client' => $client->type_client,
                'tarif' => (int) ($client->tarif ?? 1),
                'id_frs' => $client->id_frs,
            ],
        ], 'Inscription réussie');
    }

    public function login(AuthLoginRequest $request)
    {
        $data = $request->validated();

        $client = Client::query()->where('email', $data['email'])->first();

        if (! $client || ! Hash::check($data['password'], $client->password)) {
            return $this->error('Identifiants invalides', null, 401);
        }

        if ((int) $client->actif !== 1) {
            return $this->error('Compte désactivé', null, 403);
        }

        $token = $client->createToken('client')->plainTextToken;

        $fournisseur = null;
        if ($client->id_frs) {
            $frs = Fournisseur::query()->find($client->id_frs);
            if ($frs) {
                $fournisseur = [
                    'id' => $frs->id,
                    'nom_frs' => $frs->nom_frs,
                ];
            }
        }

        return $this->success([
            'token' => $token,
            'client' => [
                'id' => $client->id,
                'nom' => $client->nom,
                'prenom' => $client->prenom,
                'email' => $client->email,
                'telephone' => $client->telephone,
                'adresse' => $client->adresse,
                'id_wilaya' => $client->id_wilaya,
                'id_commune' => $client->id_commune,
                'type_client' => $client->type_client,
                'tarif' => (int) ($client->tarif ?? 1),
                'id_frs' => $client->id_frs,
                'fournisseur' => $fournisseur,
            ],
        ], 'Connexion réussie');
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return $this->success(null, 'Déconnexion réussie');
    }

    public function me(Request $request)
    {
        /** @var Client $client */
        $client = $request->user();

        $fournisseur = null;
        if ($client->id_frs) {
            $frs = Fournisseur::query()->find($client->id_frs);
            if ($frs) {
                $fournisseur = [
                    'id' => $frs->id,
                    'nom_frs' => $frs->nom_frs,
                    'email' => $frs->email,
                    'telephone' => $frs->telephone,
                ];
            }
        }

        return $this->success([
            'id' => $client->id,
            'nom' => $client->nom,
            'prenom' => $client->prenom,
            'email' => $client->email,
            'telephone' => $client->telephone,
            'adresse' => $client->adresse,
            'id_wilaya' => $client->id_wilaya,
            'id_commune' => $client->id_commune,
            'type_client' => $client->type_client,
            'tarif' => (int) ($client->tarif ?? 1),
            'id_frs' => $client->id_frs,
            'fournisseur' => $fournisseur,
        ], 'Profil');
    }

    public function updateProfil(AuthUpdateProfileRequest $request)
    {
        /** @var Client $client */
        $client = $request->user();
        $client->update($request->validated());

        return $this->success([
            'id' => $client->id,
            'nom' => $client->nom,
            'prenom' => $client->prenom,
            'telephone' => $client->telephone,
            'adresse' => $client->adresse,
            'id_wilaya' => $client->id_wilaya,
            'id_commune' => $client->id_commune,
        ], 'Profil mis à jour');
    }

    public function changePassword(AuthChangePasswordRequest $request)
    {
        /** @var Client $client */
        $client = $request->user();
        $data = $request->validated();

        if (! Hash::check($data['current_password'], $client->password)) {
            return $this->error('Ancien mot de passe incorrect', null, 422);
        }

        $client->update([
            'password' => Hash::make($data['password']),
        ]);

        return $this->success(null, 'Mot de passe mis à jour');
    }
}
