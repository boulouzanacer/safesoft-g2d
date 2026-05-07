<?php

namespace Database\Seeders;

use App\Models\Fournisseur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FournisseurSeeder extends Seeder
{
    public function run(): void
    {
        $idWilaya = 16;
        $idCommune = (int) (DB::table('commune')->where('ID_WILAYA', $idWilaya)->value('ID_COMMUNE') ?? 1);

        $this->upsertFournisseur(
            nomFrs: 'Fournisseur Test 1',
            email: 'frs1@safesoft.dz',
            telephone: '0550000001',
            idWilaya: $idWilaya,
            idCommune: $idCommune,
        );

        $this->upsertFournisseur(
            nomFrs: 'Fournisseur Test 2',
            email: 'frs2@safesoft.dz',
            telephone: '0550000002',
            idWilaya: $idWilaya,
            idCommune: $idCommune,
        );
    }

    private function upsertFournisseur(
        string $nomFrs,
        string $email,
        string $telephone,
        int $idWilaya,
        int $idCommune,
    ): void {
        $frs = Fournisseur::firstOrNew(['email' => $email]);
        $frs->nom_frs = $nomFrs;
        $frs->password = Hash::make('Password@123');
        $frs->telephone = $telephone;
        $frs->adresse = 'Alger';
        $frs->id_wilaya = $idWilaya;
        $frs->id_commune = $idCommune;
        $frs->actif = 1;

        if (! is_string($frs->token) || trim($frs->token) === '') {
            $frs->token = (string) Str::uuid();
        }

        $frs->save();
    }
}
