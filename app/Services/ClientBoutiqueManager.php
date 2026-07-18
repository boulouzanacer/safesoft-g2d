<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientBoutique;

class ClientBoutiqueManager
{
    public function resolveAuthenticatedClient(?Client $client): ?Client
    {
        if (! $client) {
            return null;
        }

        if ($client->isGlobalAccount()) {
            return $client;
        }

        $email = trim((string) $client->email);
        if ($email === '') {
            return $client;
        }

        $global = Client::findSimpleByEmail($email);

        if (! $global) {
            $global = Client::create([
                'nom' => $client->display_name,
                'email' => $client->email,
                'password' => $client->password,
                'telephone' => $client->telephone,
                'adresse' => $client->adresse,
                'id_wilaya' => (int) $client->id_wilaya,
                'id_commune' => (int) $client->id_commune,
                'type_client' => 'simple',
                'tarif' => 1,
                'id_frs' => null,
                'synced_pme' => 0,
                'actif' => (int) ($client->actif ?? 1),
                'email_verified_at' => $client->email_verified_at ?? now(),
                'email_verification_code_hash' => null,
                'email_verification_expires_at' => null,
            ]);
        } elseif (empty($global->email_verified_at) && ! empty($client->email_verified_at)) {
            $global->forceFill([
                'email_verified_at' => $client->email_verified_at,
            ])->save();
        }

        if ((int) ($client->id_frs ?? 0) > 0) {
            $this->upsertRelation($global, (int) $client->id_frs, $client);
        }

        return $global->fresh();
    }

    public function fournisseurClientMap(?Client $client): array
    {
        $global = $this->resolveAuthenticatedClient($client);
        if (! $global) {
            return [];
        }

        $map = [];

        $relations = $global->boutiqueRelations()
            ->with('fournisseurClient')
            ->get();

        foreach ($relations as $relation) {
            $supplierClient = $relation->fournisseurClient;
            if ($supplierClient && (int) ($supplierClient->id_frs ?? 0) === (int) $relation->fournisseur_id && (int) ($supplierClient->actif ?? 1) === 1) {
                $map[(int) $relation->fournisseur_id] = $supplierClient;
            }
        }

        $email = trim((string) $global->email);
        if ($email !== '') {
            $fallbackClients = Client::query()
                ->where('email', $email)
                ->whereNotNull('id_frs')
                ->where('actif', 1)
                ->get();

            foreach ($fallbackClients as $supplierClient) {
                $frsId = (int) ($supplierClient->id_frs ?? 0);
                if ($frsId <= 0) {
                    continue;
                }

                $this->upsertRelation($global, $frsId, $supplierClient);

                if (! array_key_exists($frsId, $map)) {
                    $map[$frsId] = $supplierClient;
                }
            }
        }

        return $map;
    }

    public function fournisseurClientFor(?Client $client, int $frsId): ?Client
    {
        if ($frsId <= 0) {
            return null;
        }

        $map = $this->fournisseurClientMap($client);

        return $map[$frsId] ?? null;
    }

    public function abonneFournisseurIds(?Client $client): array
    {
        return collect($this->fournisseurClientMap($client))
            ->filter(fn (Client $supplierClient) => (string) $supplierClient->type_client === 'abonne')
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function relatedClientIds(?Client $client): array
    {
        $global = $this->resolveAuthenticatedClient($client);
        if (! $global) {
            return [];
        }

        $ids = [$global->id];

        foreach ($this->fournisseurClientMap($global) as $supplierClient) {
            $ids[] = (int) $supplierClient->id;
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    public function resolveOrderClient(Client $client, int $frsId): Client
    {
        $global = $this->resolveAuthenticatedClient($client);
        if (! $global) {
            throw new \RuntimeException('Compte client introuvable.');
        }

        $supplierClient = $this->fournisseurClientFor($global, $frsId);
        if (! $supplierClient) {
            $supplierClient = Client::findForFournisseurByEmail($frsId, (string) $global->email);
        }

        $payload = [
            'nom' => $global->display_name,
            'email' => $global->email,
            'password' => $global->password,
            'telephone' => $global->telephone,
            'adresse' => $global->adresse,
            'id_wilaya' => (int) $global->id_wilaya,
            'id_commune' => (int) $global->id_commune,
            'id_frs' => $frsId,
            'actif' => 1,
        ];

        if ($supplierClient) {
            $supplierClient->update($payload);
        } else {
            $supplierClient = Client::create($payload + [
                'type_client' => 'simple',
                'tarif' => 1,
                'synced_pme' => 0,
                'email_verified_at' => $global->email_verified_at ?? now(),
            ]);
        }

        $this->upsertRelation($global, $frsId, $supplierClient);

        return $supplierClient->fresh();
    }

    public function syncLinkedClientsFromGlobal(Client $client, bool $withPassword = false): void
    {
        $global = $this->resolveAuthenticatedClient($client);
        if (! $global || ! $global->isGlobalAccount()) {
            return;
        }

        foreach ($this->fournisseurClientMap($global) as $supplierClient) {
            $payload = [
                'nom' => $global->display_name,
                'email' => $global->email,
                'telephone' => $global->telephone,
                'adresse' => $global->adresse,
                'id_wilaya' => (int) $global->id_wilaya,
                'id_commune' => (int) $global->id_commune,
            ];

            if ($withPassword) {
                $payload['password'] = $global->password;
            }

            $supplierClient->update($payload);
        }
    }

    public function upsertRelation(Client $globalClient, int $frsId, ?Client $supplierClient = null): ClientBoutique
    {
        return ClientBoutique::query()->updateOrCreate(
            [
                'global_client_id' => (int) $globalClient->id,
                'fournisseur_id' => $frsId,
            ],
            [
                'fournisseur_client_id' => $supplierClient?->id,
            ]
        );
    }
}
