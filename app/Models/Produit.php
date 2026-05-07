<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produit extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'produit';

    protected $fillable = [
        'id_frs',
        'reference',
        'designation',
        'description',
        'pv_1',
        'pv_2',
        'pv_3',
        'stock',
        'image_principale',
        'categorie',
        'abonne_only',
        'actif',
    ];

    protected $casts = [
        'pv_1' => 'float',
        'pv_2' => 'float',
        'pv_3' => 'float',
        'abonne_only' => 'integer',
        'actif' => 'integer',
    ];

    public function prixPourTarif(int $tarif): float
    {
        $t = $tarif;
        if ($t < 1 || $t > 3) {
            $t = 1;
        }

        return match ($t) {
            2 => (float) $this->pv_2,
            3 => (float) $this->pv_3,
            default => (float) $this->pv_1,
        };
    }

    public function prixPourClient(?Client $client): float
    {
        if (! $client || (string) $client->type_client !== 'abonne') {
            return (float) $this->pv_1;
        }

        return $this->prixPourTarif((int) ($client->tarif ?? 1));
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, 'id_frs', 'id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProduitImage::class, 'id_produit', 'id');
    }
}
