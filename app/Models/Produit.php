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
        'prix',
        'stock',
        'image_principale',
        'categorie',
        'actif',
    ];

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, 'id_frs', 'id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProduitImage::class, 'id_produit', 'id');
    }
}
